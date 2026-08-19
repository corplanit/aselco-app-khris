<?php

namespace App\Http\Controllers;

use App\Models\FileManager;
use App\Models\Lead;
use App\Models\User;
use App\Models\ProjectBidding;
use App\Models\Invite;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use League\Flysystem\StorageAttributes;

use App\Jobs\UploadProjectFilesToDrive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectInvitationMail;

class GoogleDriveController extends Controller
{
    protected $googleDriveService;


    public function activated(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id', // Optional parent folder (ensure it exists in DB)
        ]);

        $id = $request->id;
        $user = User::where('id', $id)->first();

        if ($user) {
            $default_folder_id = '1b0GSQOIR6-dstC4gX1X5nXYRPPXlFZ2r';
            $googleDriveFolderId = $this->googleDriveService->createFolder($user->name, $default_folder_id);

            User::where('email', $user->email)->update(['google_drive_id' => $googleDriveFolderId]);
        }
    }

    public function upload_project(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'proj_name' => 'required|string',
            'proj_due_date' => 'required|date_format:d/m/Y',
            'proj_walkthrough_date' => 'nullable|date_format:d/m/Y',
            'proj_documents.*' => 'file|max:5120000|mimes:pdf,zip,jpg,jpeg,png,gif,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,csv,svg,webp,rar,7z',
            'proj_stages' => 'nullable|string',
            'proj_address' => 'nullable|string',
            'proj_city' => 'nullable|string',
            'proj_state' => 'nullable|string',
            'proj_zip' => 'nullable|string',
            'proj_bidders' => 'array',
            'proj_status' => 'nullable|string',
            'stage_subject' => 'array',
            'stage_descriptions' => 'array',
            'stage_proj_documents' => 'array',
            'invite_clients' => 'array',
        ]);

        // Format dates
        $validated['proj_due_date'] = Carbon::createFromFormat('d/m/Y', $request->proj_due_date)->format('Y-m-d');
        $validated['proj_walkthrough_date'] = $request->proj_walkthrough_date ? Carbon::createFromFormat('d/m/Y', $request->proj_walkthrough_date)->format('Y-m-d') : null;

        // Determine client ID

        if (session('manage_portal_id')) {
            $email = session()->get('manage_portal_email');
            $client_info = Lead::where('email', $email)->first();
        } else {
            $client_info = Lead::where('email', Auth::user()->email)->first();
        }

        $client_id = $client_info->id ?? Auth::id();

        // Generate new project code
        $base_code = substr(date('Y'), 2);
        $lastProject = ProjectBidding::where('client_id', $client_id)
            ->where('proj_code', 'like', $base_code . '%')
            ->where('isDeleted', 0)
            ->orderBy('proj_code', 'desc')
            ->first();

        $new_code = $lastProject ? $base_code . str_pad(((int) substr($lastProject->proj_code, 2)) + 1, 4, '0', STR_PAD_LEFT) : $base_code . '0001';

        $validated['proj_code'] = $new_code;

        // Create project record
        $validated['proj_documents'] = $validated['proj_documents'] ?? [];
        $project = ProjectBidding::create($validated);

        $savedPaths = [];
        $structuredStageFiles = [];

        $tempPathBase = storage_path('app/temp_project_uploads');
        if (!file_exists($tempPathBase)) {
            mkdir($tempPathBase, 0775, true);
        }

        // Handle general project document uploads
        if ($request->hasFile('proj_documents') && !empty(Auth::user()->google_drive_id)) {
            foreach ($request->file('proj_documents') as $file) {
                if ($file->isValid()) {
                    $filename = $file->getClientOriginalName();
                    $fullPath = $tempPathBase . '/' . $filename;

                    $file->move($tempPathBase, $filename);

                    if (file_exists($fullPath)) {
                        $savedPaths[] = $fullPath;
                        Log::info("Stored for queue: $fullPath");
                    } else {
                        Log::error("Failed to store file: $fullPath");
                    }
                } else {
                    Log::warning('Skipped invalid project file.');
                }
            }
        }

        $targetDir = storage_path('app/temp_scope_uploads');
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $stageFiles = $request->file('stage_proj_documents');

        if (is_array($stageFiles)) {
            foreach ($stageFiles as $stage => $files) {
                if (!is_array($files)) {
                    $files = [$files]; // normalize
                }

                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $filename = uniqid() . '_' . $file->getClientOriginalName();
                        $fullPath = $targetDir . '/' . $filename;

                        try {
                            $file->move($targetDir, $filename);

                            if (file_exists($fullPath)) {
                                $structuredStageFiles[$stage][] = [
                                    'path' => $fullPath,
                                    'original_name' => $file->getClientOriginalName(),
                                    'size' => filesize($fullPath),
                                    'mime_type' => mime_content_type($fullPath),
                                    'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
                                ];
                            } else {
                                Log::error("Manual move failed for stage file: $fullPath");
                            }
                        } catch (\Exception $e) {
                            Log::error("Exception while moving stage file: {$e->getMessage()}");
                        }
                    } else {
                        Log::warning('Invalid stage file skipped.');
                    }
                }
            }
        }

        if (session('impersonator_email')) {
            $impersonating_id = User::where('email', session('impersonator_email'))->value('id');
        } else {
            $impersonating_id = Auth::user()->id;
        }

        // Dispatch background upload job only once
        if ((!empty($savedPaths) || !empty($structuredStageFiles)) && !empty(Auth::user()->google_drive_id)) {
            UploadProjectFilesToDrive::dispatch(
                $savedPaths,
                $project->id,
                Auth::user()->google_drive_id, // folderId
                Auth::id(),
                $structuredStageFiles,
                Auth::user()->google_drive_id, // pass again explicitly
                $project->proj_documents,
                '',
                $impersonating_id
            );
        }

        // Step 7: Invite clients
        if (!empty($validated['invite_clients']) && is_array($validated['invite_clients'])) {
            foreach ($validated['invite_clients'] as $category => $clients) {
                if (is_array($clients)) {
                    foreach ($clients as $client) {
                        $normalizedCategory = str_replace(' ', '_', $category);
                        Invite::create([
                            'project_id' => $project->id,
                            'email' => $client,
                            'category' => $category,
                            'subject' => $validated['stage_subject'][$normalizedCategory] ?? '',
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Project created. File uploads are processing in background.',
            'structuredStageFiles' => $structuredStageFiles,
            'proj_documents' => $project->proj_documents,
            'data' =>  $validated
        ]);
    }

    
    public function update_project(Request $request, $id)
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'proj_name' => 'required|string',
            'proj_due_date' => 'required|date_format:d/m/Y',
            'proj_walkthrough_date' => 'nullable|date_format:d/m/Y',
            'proj_documents.*' => 'file|max:5120000|mimes:pdf,zip,jpg,jpeg,png,gif,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,csv,svg,webp,rar,7z',
            'proj_stages' => 'nullable|string',
            'proj_address' => 'nullable|string',
            'proj_city' => 'nullable|string',
            'proj_state' => 'nullable|string',
            'proj_zip' => 'nullable|string',
            'proj_bidders' => 'array',
            'proj_status' => 'nullable|string',
            'stage_subject' => 'array',
            'stage_descriptions' => 'array',
            'stage_proj_documents' => 'array',
            'invite_clients' => 'array',
        ]);

        $validated['proj_due_date'] = Carbon::createFromFormat('d/m/Y', $request->proj_due_date)->format('Y-m-d');
        $validated['proj_walkthrough_date'] = $request->proj_walkthrough_date ? Carbon::createFromFormat('d/m/Y', $request->proj_walkthrough_date)->format('Y-m-d') : null;

        // Find existing project
        $project = ProjectBidding::findOrFail($id);
        $exist_proj_documents = $project->proj_documents;
        $exist_stage_proj_documents = $project->stage_proj_documents;

        $project->update($validated);

        $savedPaths = [];
        $structuredStageFiles = [];

        // Handle project-level documents
        $tempPathBase = storage_path('app/temp_project_uploads');
        if (!file_exists($tempPathBase)) {
            mkdir($tempPathBase, 0775, true);
        }

        if ($request->hasFile('proj_documents') && !empty(Auth::user()->google_drive_id)) {
            foreach ($request->file('proj_documents') as $file) {
                if ($file->isValid()) {
                    $filename = $file->getClientOriginalName();
                    $fullPath = $tempPathBase . '/' . $filename;

                    $file->move($tempPathBase, $filename);

                    if (file_exists($fullPath)) {
                        $savedPaths[] = $fullPath;
                        Log::info("Stored for queue: $fullPath");
                    } else {
                        Log::error("Failed to store file: $fullPath");
                    }
                } else {
                    Log::warning('Skipped invalid project file.');
                }
            }
        }

        // Handle stage-level documents
        $targetDir = storage_path('app/temp_scope_uploads');
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $stageFiles = $request->file('stage_proj_documents');

        if (is_array($stageFiles)) {
            foreach ($stageFiles as $stage => $files) {
                if (!is_array($files)) {
                    $files = [$files]; // normalize
                }

                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $filename = uniqid() . '_' . $file->getClientOriginalName();
                        $fullPath = $targetDir . '/' . $filename;

                        try {
                            $file->move($targetDir, $filename);

                            if (file_exists($fullPath)) {
                                $structuredStageFiles[$stage][] = [
                                    'path' => $fullPath,
                                    'original_name' => $file->getClientOriginalName(),
                                    'size' => filesize($fullPath),
                                    'mime_type' => mime_content_type($fullPath),
                                    'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
                                ];
                            } else {
                                Log::error("Manual move failed for stage file: $fullPath");
                            }
                        } catch (\Exception $e) {
                            Log::error("Exception while moving stage file: {$e->getMessage()}");
                        }
                    } else {
                        Log::warning('Invalid stage file skipped.');
                    }
                }
            }
        }

        // Dispatch background upload job if needed
        if ((!empty($savedPaths) || !empty($structuredStageFiles)) && !empty(Auth::user()->google_drive_id)) {
            UploadProjectFilesToDrive::dispatch($savedPaths, $project->id, Auth::user()->google_drive_id, Auth::id(), $structuredStageFiles, Auth::user()->google_drive_id, $exist_proj_documents, $exist_stage_proj_documents);
        }

        // Handle invites

        $projectLink = route('login');
        $partner = 'Plan Panther';

        if (!empty($validated['invite_clients']) && is_array($validated['invite_clients'])) {
            foreach ($validated['invite_clients'] as $category => $clients) {
                if (is_array($clients)) {
                    foreach ($clients as $client) {
                        if ($validated['proj_stages'] == 'Ready') {

                            $user = User::where('email', $client)->first();

                            if ($user) {
                                Mail::to($client)->send(new ProjectInvitationMail(
                                    $user->name,
                                    $partner,
                                    $validated['proj_name'],
                                    $validated['proj_due_date'],
                                    $validated['proj_walkthrough_date'] ?? null,
                                    $validated['proj_address'] ?? '',
                                    $validated['proj_city'] ?? '',
                                    $validated['proj_state'] ?? '',
                                    $validated['proj_zip'] ?? '',
                                    $validated['proj_stages'] ?? null,
                                    $validated['stage_subject'] ?? [],
                                    $validated['stage_descriptions'] ?? [],
                                    $projectLink
                                ));
                            }


                            Invite::where('status', null)
                                ->where('email', $client)
                                ->where('project_id', $project->id)
                                ->where('category', $category)
                                ->update(['status' => 'Ready']);
                        }

                        $normalizedCategory = str_replace(' ', '_', trim($category));
                        $subject = $validated['stage_subject'][$normalizedCategory] ?? '';

                        $check = Invite::where([
                            'project_id' => $project->id,
                            'email' => $client,
                            'category' => $category,
                            'subject' => $subject,
                        ])->exists();

                        if ($subject && !$check) {
                            Invite::create([
                                'project_id' => $project->id,
                                'email' => $client,
                                'category' => $category,
                                'subject' => $subject,
                            ]);
                        }
                    }
                }
            }
        }

        if ($validated['proj_stages'] == 'Ready') {
            Invite::where('status', null)
                ->where('project_id', $project->id)
                ->update(['status' => 'Ready']);
        }

        // return response()->json($client, 200, [], JSON_PRETTY_PRINT);
        return response()->json([
            'status' => 'success',
            'message' => 'Project updated. File uploads are processing in background.',
            'exist_stage_proj_documents' => $exist_stage_proj_documents,
            'exist_proj_documents' => $exist_proj_documents,
            'structuredStageFiles' => $structuredStageFiles,
        ]);
    }

    // public function upload_project(Request $request)
    // {
    //     // Step 1: Validate the request input
    //     $validated = $request->validate([
    //         'client_id' => 'required|string',
    //         'proj_code' => 'required|digits:6',
    //         'proj_name' => 'required|string',
    //         'proj_stages' => 'nullable|string',
    //         'proj_address' => 'nullable|string',
    //         'proj_city' => 'nullable|string',
    //         'proj_state' => 'nullable|string',
    //         'proj_zip' => 'nullable|string',
    //         'proj_bidders' => 'array',
    //         'proj_documents.*' => 'file|max:5120000|mimes:pdf,zip,jpg,jpeg,png,gif,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,csv,svg,webp,rar,7z',
    //         'proj_status' => 'nullable|string',
    //         'stage_subject' => 'array',
    //         'stage_descriptions' => 'array',
    //         'stage_proj_documents' => 'array',
    //         'invite_clients' => 'array',
    //     ]);

    //     // Step 2: Handle general project documents
    //     $validated['proj_documents'] = [];
    //     if ($request->hasFile('proj_documents')) {
    //         $folder_id = $request->parent_id ?? Auth::user()->google_drive_id;

    //         foreach ($request->file('proj_documents', []) as $file) {
    //             if ($file->isValid()) {
    //                 $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //                 $extension = $file->getClientOriginalExtension();
    //                 $fileName = $originalName . '_' . now()->format('Ymd_His') . '.' . $extension;

    //                 $googleDriveFileId = $this->googleDriveService->uploadFile($file->getRealPath(), $originalName, $folder_id);

    //                 do {
    //                     $new_link = Str::random(7);
    //                 } while (FileManager::where('link', $new_link)->exists());

    //                 $validated['proj_documents'][] = FileManager::create([
    //                     'link' => $new_link,
    //                     'name' => $file->getClientOriginalName(),
    //                     'google_drive_id' => $googleDriveFileId,
    //                     'path' => 'documents/' . $fileName,
    //                     'size' => $file->getSize(),
    //                     'format' => $extension,
    //                     'mime_type' => $file->getMimeType(),
    //                     'user_id' => Auth::id(),
    //                     'parent_id' => $folder_id,
    //                     'is_folder' => false,
    //                 ]);
    //             } else {
    //                 return response()->json(
    //                     [
    //                         'status' => 'error',
    //                         'message' => 'One or more project documents failed to upload.',
    //                     ],
    //                     400,
    //                 );
    //             }
    //         }
    //     }

    //     // Step 3: Format dates
    //     $validated['proj_due_date'] = Carbon::createFromFormat('d/m/Y', $request->proj_due_date)->format('Y-m-d');
    //     $validated['proj_walkthrough_date'] = $request->proj_walkthrough_date ? Carbon::createFromFormat('d/m/Y', $request->proj_walkthrough_date)->format('Y-m-d') : null;

    //     $client_info = Lead::where('email', Auth::user()->email)->first();
    //     $client_id = $client_info->id ?? Auth::user()->id;

    //     // Get last project for this client for the current year
    //     $base_code = substr(date('Y'), 2); // "25" for 2025

    //     $lastProject = ProjectBidding::where('client_id', $client_id)
    //         ->where('proj_code', 'like', $base_code . '%')
    //         ->where('isDeleted', 0)
    //         ->orderBy('proj_code', 'desc')
    //         ->first();

    //     if ($lastProject) {
    //         // Increment last code
    //         $last_code_number = (int) substr($lastProject->proj_code, 2); // get last 4 digits
    //         $new_code = $base_code . str_pad($last_code_number + 1, 4, '0', STR_PAD_LEFT);
    //     } else {
    //         // Start at 0001
    //         $new_code = $base_code . '0001';
    //     }

    //     // Now inject it into your validated data
    //     $validated['proj_code'] = $new_code;

    //     // Step 4: Create ProjectBidding record
    //     $project_bid = ProjectBidding::create($validated);

    //     // Step 5: Create folder for stage documents
    //     $parent_id = $request->parent_id ?? Auth::user()->google_drive_id;
    //     $googleDriveFolderId = $this->googleDriveService->createFolder($request->proj_name, $parent_id);

    //     // Step 6: Handle stage-specific file uploads
    //     $stageFiles = [];
    //     $allStageFiles = $request->file('stage_proj_documents') ?? [];

    //     foreach ($allStageFiles as $stage => $stageFileArray) {
    //         $stageFiles[$stage] = [];

    //         foreach ($stageFileArray as $file) {
    //             if ($file instanceof UploadedFile && $file->isValid()) {
    //                 $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //                 $extension = $file->getClientOriginalExtension();
    //                 $fileName = $originalName . '_' . now()->format('Ymd_His') . '.' . $extension;

    //                 $googleDriveFileId = $this->googleDriveService->uploadFile($file->getRealPath(), $originalName, $googleDriveFolderId);

    //                 do {
    //                     $new_link = Str::random(7);
    //                 } while (FileManager::where('link', $new_link)->exists());

    //                 $data_files = [
    //                     'link' => $new_link,
    //                     'name' => $file->getClientOriginalName(),
    //                     'google_drive_id' => $googleDriveFileId,
    //                     'path' => 'documents/' . $fileName,
    //                     'size' => $file->getSize(),
    //                     'format' => $extension,
    //                     'mime_type' => $file->getMimeType(),
    //                     'user_id' => Auth::id(),
    //                     'parent_id' => $parent_id,
    //                     'is_folder' => false,
    //                 ];

    //                 $fileRecord = FileManager::create($data_files);

    //                 $stageFiles[$stage][] = $fileRecord->only(['id', 'name', 'link', 'path', 'format', 'size', 'mime_type', 'google_drive_id']);
    //             } else {
    //                 return response()->json(
    //                     [
    //                         'status' => 'error',
    //                         'message' => "Invalid or null file in stage: $stage",
    //                     ],
    //                     400,
    //                 );
    //             }
    //         }
    //     }

    //     // Save stage files JSON
    //     $validated['stage_proj_documents'] = $stageFiles;
    //     $project_bid->stage_proj_documents = $stageFiles;
    //     $project_bid->save();

    //     // Step 7: Invite clients
    //     if (!empty($validated['invite_clients']) && is_array($validated['invite_clients'])) {
    //         foreach ($validated['invite_clients'] as $category => $clients) {
    //             if (is_array($clients)) {
    //                 foreach ($clients as $client) {
    //                     $normalizedCategory = str_replace(' ', '_', $category);
    //                     Invite::create([
    //                         'project_id' => $project_bid->id,
    //                         'email' => $client,
    //                         'category' => $category,
    //                         'subject' => $validated['stage_subject'][$normalizedCategory] ?? '',
    //                     ]);
    //                 }
    //             }
    //         }
    //     }

    //     // Step 8: Final response
    //     return response()->json(
    //         [
    //             'status' => 'success',
    //             'message' => 'Project Bidding Uploaded Successfully!',
    //             'project_id' => $project_bid->id,
    //             'stageFiles' => $stageFiles,
    //         ],
    //         200,
    //     );
    // }

    // public function update_project(Request $request, $id)
    // {
    //     // Step 1: Validate the request input
    //     $validated = $request->validate([
    //         'client_id' => 'required|string',
    //         'proj_code' => 'required|string',
    //         'proj_name' => 'required|string',
    //         'proj_stages' => 'required|string',
    //         'proj_status' => 'required|string',
    //         'proj_address' => 'required|string',
    //         'proj_city' => 'required|string',
    //         'proj_state' => 'required|string',
    //         'proj_zip' => 'required|string',
    //         'proj_bidders' => 'array',
    //         'proj_documents.*' => 'file|max:5120000|mimes:pdf,zip,jpg,jpeg,png,gif,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,csv,svg,webp,rar,7z',
    //         'stage_subject' => 'array',
    //         'stage_descriptions' => 'array',
    //         'stage_proj_documents' => 'array',
    //         'invite_clients' => 'array',
    //     ]);

    //     // // Step 2: Format the dates
    //     $validated['proj_due_date'] = Carbon::createFromFormat('d/m/Y', $request->proj_due_date)->format('Y-m-d');
    //     $validated['proj_walkthrough_date'] = $request->proj_walkthrough_date ? Carbon::createFromFormat('d/m/Y', $request->proj_walkthrough_date)->format('Y-m-d') : null;

    //     // // Step 3: Find the existing project
    //     $project_bid = ProjectBidding::findOrFail($id);
    //     $folder_id = $request->parent_id ?? Auth::user()->google_drive_id;

    //     // // Step 4: Handle new document uploads
    //     if ($request->hasFile('proj_documents')) {
    //         // Ensure proj_documents is an array (if it is a JSON string, decode it)
    //         $existingProjDocuments = is_string($project_bid->proj_documents) ? json_decode($project_bid->proj_documents, true) : $project_bid->proj_documents ?? [];

    //         foreach ($request->file('proj_documents') as $file) {
    //             if ($file->isValid()) {
    //                 $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //                 $extension = $file->getClientOriginalExtension();
    //                 $fileName = $originalName . '_' . now()->format('Ymd_His') . '.' . $extension;

    //                 // Upload to Google Drive
    //                 $googleDriveFileId = $this->googleDriveService->uploadFile($file->getRealPath(), $originalName, $folder_id);

    //                 do {
    //                     $new_link = Str::random(7);
    //                 } while (FileManager::where('link', $new_link)->exists());

    //                 // Store the new document record
    //                 $newDocument = FileManager::create([
    //                     'link' => $new_link,
    //                     'name' => $file->getClientOriginalName(),
    //                     'google_drive_id' => $googleDriveFileId,
    //                     'path' => 'documents/' . $fileName,
    //                     'size' => $file->getSize(),
    //                     'format' => $extension,
    //                     'mime_type' => $file->getMimeType(),
    //                     'user_id' => Auth::id(),
    //                     'parent_id' => $request->parent_id,
    //                     'is_folder' => false,
    //                 ]);

    //                 // Add the new document to the existing documents array
    //                 $existingProjDocuments[] = $newDocument->only(['id', 'name', 'link', 'path', 'format', 'size', 'mime_type', 'google_drive_id']);
    //             }
    //         }

    //         // Update the project's project documents with the new documents
    //         $validated['proj_documents'] = $existingProjDocuments;
    //     }

    //     // Step 5: Handle stage document uploads
    //     $stageFiles = [];
    //     $allStageFiles = $request->file('stage_proj_documents') ?? [];

    //     // Proceed only if files are provided in the 'stage_proj_documents' array
    //     if (!empty($allStageFiles)) {
    //         // Retrieve existing stage files
    //         $existingStageFiles = $project_bid->stage_proj_documents ?? [];

    //         foreach ($allStageFiles as $stage => $stageFileArray) {
    //             // Initialize the stage if it doesn't exist
    //             if (!isset($existingStageFiles[$stage])) {
    //                 $existingStageFiles[$stage] = [];
    //             }

    //             foreach ($stageFileArray as $file) {
    //                 if ($file instanceof UploadedFile && $file->isValid()) {
    //                     $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //                     $extension = $file->getClientOriginalExtension();
    //                     $fileName = $originalName . '_' . now()->format('Ymd_His') . '.' . $extension;

    //                     // Upload to Google Drive
    //                     $googleDriveFileId = $this->googleDriveService->uploadFile($file->getRealPath(), $originalName, $folder_id);

    //                     do {
    //                         $new_link = Str::random(7);
    //                     } while (FileManager::where('link', $new_link)->exists());

    //                     // Store the new document record
    //                     $data_files = [
    //                         'link' => $new_link,
    //                         'name' => $file->getClientOriginalName(),
    //                         'google_drive_id' => $googleDriveFileId,
    //                         'path' => 'documents/' . $fileName,
    //                         'size' => $file->getSize(),
    //                         'format' => $extension,
    //                         'mime_type' => $file->getMimeType(),
    //                         'user_id' => Auth::id(),
    //                         'parent_id' => $folder_id,
    //                         'is_folder' => false,
    //                     ];

    //                     $fileRecord = FileManager::create($data_files);

    //                     // Append the new file record to the appropriate stage
    //                     $existingStageFiles[$stage][] = $fileRecord->only(['id', 'name', 'link', 'path', 'format', 'size', 'mime_type', 'google_drive_id']);
    //                 } else {
    //                     return response()->json(
    //                         [
    //                             'status' => 'error',
    //                             'message' => "Invalid or null file in stage: $stage",
    //                         ],
    //                         400,
    //                     );
    //                 }
    //             }
    //         }

    //         $validated['stage_proj_documents'] = $existingStageFiles;
    //     }

    //     // Step 6: Update project details
    //     $project_bid->update($validated);

    //     //dd($existingStageFiles);

    //     // // Step 7: Handle invites
    //     if (!empty($validated['invite_clients']) && is_array($validated['invite_clients'])) {
    //         //Invite::where('project_id', $project_bid->id)->delete(); // Clear old invites

    //         foreach ($validated['invite_clients'] as $category => $clients) {
    //             if (is_array($clients)) {
    //                 foreach ($clients as $client) {
    //                     if ($validated['proj_stages'] == 'Ready') {
    //                         Invite::where('status', null)
    //                             ->where('email', $client)
    //                             ->where('project_id', $project_bid->id)
    //                             ->where('category', $category)
    //                             ->where('status', '', null)
    //                             ->update(['status' => 'Ready']);
    //                     }

    //                     $normalizedCategory = str_replace(' ', '_', trim($category));

    //                     $subject = $validated['stage_subject'][$normalizedCategory] ?? '';

    //                     $check = Invite::where([
    //                         'project_id' => $project_bid->id,
    //                         'email' => $client,
    //                         'category' => $category,
    //                         'subject' => $subject,
    //                     ])->exists();

    //                     if ($subject && !$check) {
    //                         Invite::create([
    //                             'project_id' => $project_bid->id,
    //                             'email' => $client,
    //                             'category' => $category,
    //                             'subject' => $subject,
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     if ($validated['proj_stages'] == 'Ready') {
    //         Invite::where('status', null)
    //             ->where('project_id', $project_bid->id)
    //             ->update(['status' => 'Ready']);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Project Bidding Updated Successfully!',
    //         'project_id' => $project_bid->id,
    //     ]);
    // }


    // public function upload_project(Request $request)
    // {
    //     $validated = $request->validate([
    //         'client_id' => 'required|string',
    //         'proj_name' => 'required|string',
    //         'proj_due_date' => 'required|date_format:d/m/Y',
    //         'proj_walkthrough_date' => 'nullable|date_format:d/m/Y',
    //         'proj_documents.*' => 'file|max:5120000|mimes:pdf,zip,jpg,jpeg,png,gif,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,csv,svg,webp,rar,7z',
    //         'proj_stages' => 'nullable|string',
    //         'proj_address' => 'nullable|string',
    //         'proj_city' => 'nullable|string',
    //         'proj_state' => 'nullable|string',
    //         'proj_zip' => 'nullable|string',
    //         'proj_bidders' => 'array',
    //         'proj_status' => 'nullable|string',
    //         'stage_subject' => 'array',
    //         'stage_descriptions' => 'array',
    //         'stage_proj_documents' => 'array',
    //         'invite_clients' => 'array',
    //     ]);

    //     // Format dates
    //     $validated['proj_due_date'] = Carbon::createFromFormat('d/m/Y', $request->proj_due_date)->format('Y-m-d');
    //     $validated['proj_walkthrough_date'] = $request->proj_walkthrough_date
    //         ? Carbon::createFromFormat('d/m/Y', $request->proj_walkthrough_date)->format('Y-m-d')
    //         : null;

    //     // Determine client ID
    //     if (session('manage_portal_id')) {
    //         $email = session()->get('manage_portal_email');
    //         $client_info = Lead::where('email', $email)->first();
    //     } else {
    //         $client_info = Lead::where('email', Auth::user()->email)->first();
    //     }
    //     $client_id = $client_info->id ?? Auth::id();

    //     // Generate new project code YYNNNN
    //     $base_code = substr(date('Y'), 2);
    //     $lastProject = ProjectBidding::where('client_id', $client_id)
    //         ->where('proj_code', 'like', $base_code . '%')
    //         ->where('isDeleted', 0)
    //         ->orderBy('proj_code', 'desc')
    //         ->first();
    //     $new_code = $lastProject
    //         ? $base_code . str_pad(((int) substr($lastProject->proj_code, 2)) + 1, 4, '0', STR_PAD_LEFT)
    //         : $base_code . '0001';

    //     $validated['proj_code'] = $new_code;

    //     // Create project record
    //     $validated['proj_documents'] = $validated['proj_documents'] ?? [];
    //     $project = ProjectBidding::create($validated);

    //     // Base directories (public disk => storage/app/public)
    //     $baseDir = "projects/{$client_id}/{$new_code}";
    //     $docsDir = "{$baseDir}/docs";
    //     $stagesDir = "{$baseDir}/stages";

    //     // Ensure directories exist
    //     Storage::disk('public')->makeDirectory($docsDir);
    //     Storage::disk('public')->makeDirectory($stagesDir);

    //     $savedDocs = [];
    //     if ($request->hasFile('proj_documents')) {
    //         foreach ($request->file('proj_documents') as $file) {
    //             if (!$file->isValid()) {
    //                 continue;
    //             }
    //             $name = Str::uuid()->toString() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
    //             $path = Storage::disk('public')->putFileAs($docsDir, $file, $name);
    //             $savedDocs[] = [
    //                 'path' => $path,                                   // relative to public disk
    //                 'url' => Storage::disk('public')->url($path),      // /storage/...
    //                 'original_name' => $file->getClientOriginalName(),
    //                 'size' => $file->getSize(),
    //                 'mime_type' => $file->getMimeType(),
    //                 'extension' => $file->getClientOriginalExtension(),
    //             ];
    //         }
    //     }

    //     // Save stage files per stage index/key
    //     $structuredStageFiles = [];
    //     $stageFiles = $request->file('stage_proj_documents');
    //     if (is_array($stageFiles)) {
    //         foreach ($stageFiles as $stage => $files) {
    //             $stageKey = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$stage);
    //             $stageDir = "{$stagesDir}/{$stageKey}";
    //             Storage::disk('public')->makeDirectory($stageDir);

    //             $files = is_array($files) ? $files : [$files];
    //             foreach ($files as $file) {
    //                 if (!$file || !$file->isValid()) {
    //                     continue;
    //                 }
    //                 $name = Str::uuid()->toString() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
    //                 $path = Storage::disk('public')->putFileAs($stageDir, $file, $name);
    //                 $structuredStageFiles[$stage][] = [
    //                     'path' => $path,
    //                     'url' => Storage::disk('public')->url($path),
    //                     'original_name' => $file->getClientOriginalName(),
    //                     'size' => $file->getSize(),
    //                     'mime_type' => $file->getMimeType(),
    //                     'extension' => $file->getClientOriginalExtension(),
    //                 ];
    //             }
    //         }
    //     }

    //     // Persist saved doc list back to project (assuming JSON cast on proj_documents)
    //     $project->update([
    //         'proj_documents' => $savedDocs,
    //     ]);

    //     // Invite clients
    //     if (!empty($validated['invite_clients']) && is_array($validated['invite_clients'])) {
    //         foreach ($validated['invite_clients'] as $category => $clients) {
    //             if (!is_array($clients)) {
    //                 continue;
    //             }
    //             foreach ($clients as $clientEmail) {
    //                 $normalizedCategory = str_replace(' ', '_', $category);
    //                 Invite::create([
    //                     'project_id' => $project->id,
    //                     'email' => $clientEmail,
    //                     'category' => $category,
    //                     'subject' => $validated['stage_subject'][$normalizedCategory] ?? '',
    //                 ]);
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Project created. Files saved on server.',
    //         'proj_documents' => $savedDocs,
    //         'structuredStageFiles' => $structuredStageFiles,
    //         'data' =>  $validated
    //     ]);
    // }
    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array', // Ensure it's an array of files
            'files.*' => 'file|max:2097152', // Validate each file, max ~2GB
            'parent_id' => 'nullable|exists:t_file_manager,google_drive_id',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $tempPath = $file->getRealPath();

                // Upload to Google Drive
                $parentDriveId = null;
                if ($request->parent_id) {
                    $parentFolder = FileManager::find($request->parent_id);
                    $parentDriveId = $parentFolder->google_drive_id ?? null;
                }

                $folder_id = $request->parent_id ?? Auth::user()->google_drive_id; // Default if No Folder Exist
                $googleDriveFileId = $this->googleDriveService->uploadFile($tempPath, $originalName, $folder_id);

                // Generate unique short link
                do {
                    $new_link = Str::random(7);
                } while (FileManager::where('link', $new_link)->exists());

                if (session('manage_portal_id')) {
                    $uploader = session()->get('manage_orignal_id');
                    $user_id = session()->get('manage_portal_id');
                } else {
                    $uploader = Auth::user()->id;
                    $user_id = $uploader;
                }

                if (session('impersonator_email')) {
                    $impersonating_id = User::where('email', session('impersonator_email'))->value('id');
                } else {
                    $impersonating_id = Auth::user()->id;
                }

                // Save to database
                FileManager::create([
                    'link' => $new_link,
                    'name' => $originalName,
                    'google_drive_id' => $googleDriveFileId,
                    'size' => $file->getSize(),
                    'format' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                    'user_id' => $user_id,
                    'parent_id' => $request->parent_id,
                    'is_folder' => false,
                    'uploader_id' => $impersonating_id,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Files uploaded to Google Drive successfully!');
    }

    public function folder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Generate unique short link
        do {
            $new_link = Str::random(7);
        } while (FileManager::where('link', $new_link)->exists());

        $folder_id = $request->parent_id ?? Auth::user()->google_drive_id; // Default if No Folder Exist
        // Create folder on Google Drive
        $googleDriveFolderId = null; //$this->googleDriveService->createFolder($request->name, $folder_id);

        if (session('manage_portal_id')) {
            $uploader = session()->get('manage_orignal_id');
            $user_id = session()->get('manage_portal_id');
        } else {
            $uploader = Auth::user()->id;
            $user_id = $uploader;
        }

        // Save folder details in DB
        $store = FileManager::create([
            'link' => $new_link,
            'name' => $request->name,
            'user_id' => $user_id,
            'parent_id' => $request->parent_id,
            'is_folder' => true,
            'google_drive_id' => $googleDriveFolderId,
            'uploader_id' => $uploader,
        ]);

        return redirect()->back()->with('success', 'Folder created successfully!');
    }

    protected function humanizeStorageSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function getStorageInfo()
    {
        $storage = $this->googleDriveService->getStorageInfo();

        return response()->json(
            [
                'storage_used' => $this->humanizeStorageSize($storage['used']),
                'storage_limit' => $this->humanizeStorageSize($storage['limit']),
                'storage_remaining' => $this->humanizeStorageSize($storage['remaining']),
            ],
            200,
        );
    }

    public function preview($id)
    {
        set_time_limit(0);

        $accessToken = $this->getAccessToken();

        // Fetch the file metadata
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        // Fetch file metadata from Google Drive API
        $fileRes = Http::withHeaders($headers)->get("https://www.googleapis.com/drive/v3/files/{$id}?fields=id,name,mimeType,webContentLink,webViewLink");

        // Check if file is accessible
        if ($fileRes->status() !== 200) {
            // Provide a more detailed error message in case of failure
            return response()->view('pages.members.preview', [
                'message' => 'File not found or access denied. Please check if the file is shared correctly or if the access token is valid.',
                'download_link' => null,
                'mimeType' => null,
                'url' => null,
                'id' => $id, // Pass the ID for downloading
            ]);
        }

        $fileMetadata = $fileRes->json();
        $mimeType = $fileMetadata['mimeType'];
        $name = $fileMetadata['name'];
        $webContentLink = $fileMetadata['webContentLink'];

        // Ensure the file is accessible via webContentLink (for private files)
        if (!$webContentLink) {
            return response()->view('pages.members.preview', [
                'message' => 'The file is not accessible or not publicly available. Please check if the file has the correct permissions.',
                'download_link' => url('/download-file/' . $id),
                'mimeType' => $mimeType,
                'url' => null,
                'id' => $id, // Pass the ID for downloading
            ]);
        }

        // Handle Google Docs or Sheets preview
        if (in_array($mimeType, ['application/vnd.google-apps.document', 'application/vnd.google-apps.spreadsheet'])) {
            return $this->googleFilePreview($id, $mimeType);
        }

        // Handle ZIP files - show download prompt
        if ($mimeType === 'application/zip' || $mimeType === 'application/x-zip-compressed') {
            return response()->view('pages.members.preview', [
                'message' => 'This is a ZIP file. Please download it to view its contents.',
                'download_link' => url('/download-file/' . $id),
                'mimeType' => $mimeType,
                'url' => null,
                'id' => $id, // Pass the ID for downloading
            ]);
        }

        // Handle image or video preview
        if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return $this->imagePreview($fileMetadata);
        } elseif (in_array($mimeType, ['video/mp4', 'video/webm', 'video/ogg'])) {
            return $this->videoPreview($fileMetadata);
        }

        // For Office files (.docx, .xlsx, .pptx), handle with Office Online
        // if (
        //     in_array($mimeType, [
        //         'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        //         'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        //         'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
        //     ])
        // ) {
        //     return $this->officeFilePreview($id, $mimeType);
        // }

        // Default file preview (e.g., PDF, text files)
        return $this->defaultPreview($fileMetadata, $mimeType);
    }

    // Google Docs/Sheets preview (View or Edit)
    private function googleFilePreview($id, $mimeType)
    {
        $url = "https://drive.google.com/file/d/{$id}/view?usp=drivesdk";

        // If it's a PDF, use the webContentLink (Google Drive preview)
        if ($mimeType === 'application/pdf') {
            $url = "https://drive.google.com/uc?id={$id}&export=download";
        }

        return view('pages.apps.storage.preview', [
            'url' => $url,
            'mimeType' => $mimeType,
            'message' => null,
            'download_link' => url('/download-file/' . $id),
            'id' => $id, // Add the $id variable here
        ]);
    }

    // Office Online preview for Word, Excel, and PowerPoint files
    public function officeFilePreview($id)
    {
        $mimeType = 'application/pdf'; // Default to PDF or derive it dynamically

        $accessToken = $this->getAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        // Fetch the file from Google Drive and export it as a PDF
        $response = Http::withHeaders($headers)->get("https://www.googleapis.com/drive/v3/files/{$id}/export", [
            'mimeType' => $mimeType,
        ]);

        if (!$response->ok()) {
            return response()->view('pages.apps.storage.partials.preview', [
                'message' => 'Unable to preview this file. Please download it instead.',
                'download_link' => url('/download-file/' . $id),
                'mimeType' => $mimeType,
                'url' => null,
                'id' => $id,
            ]);
        }

        // Store the preview file temporarily
        $filename = "preview_{$id}.pdf";
        Storage::disk('local')->put("previews/{$filename}", $response->body());

        // Return the preview as a file response
        return response()->file(storage_path("app/previews/{$filename}"), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // Image preview handler
    private function imagePreview($fileMetadata)
    {
        $accessToken = $this->getAccessToken();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $fileRes = Http::withHeaders($headers)->get("https://www.googleapis.com/drive/v3/files/{$fileMetadata['id']}?alt=media");

        return response()->stream(
            function () use ($fileRes) {
                echo $fileRes->body();
            },
            200,
            [
                'Content-Type' => $fileRes->header('Content-Type'),
                'Content-Disposition' => 'inline',
            ],
        );
    }

    // Video preview handler
    private function videoPreview($fileMetadata)
    {
        $accessToken = $this->getAccessToken();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $fileRes = Http::withHeaders($headers)->get("https://www.googleapis.com/drive/v3/files/{$fileMetadata['id']}?alt=media");

        return response()->stream(
            function () use ($fileRes) {
                echo $fileRes->body();
            },
            200,
            [
                'Content-Type' => $fileRes->header('Content-Type'),
                'Content-Disposition' => 'inline',
            ],
        );
    }

    // Default file preview (e.g., PDF, text files)
    private function defaultPreview($fileMetadata, $mimeType)
    {
        $accessToken = $this->getAccessToken();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $fileRes = Http::withHeaders($headers)->get("https://www.googleapis.com/drive/v3/files/{$fileMetadata['id']}?alt=media");

        return response()->stream(
            function () use ($fileRes) {
                echo $fileRes->body();
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline',
            ],
        );
    }

    // Access token retrieval method
    private function getAccessToken()
    {
        // Check if we already have a refresh token
        $refreshToken = config('filesystems.disks.google.refreshToken');

        if ($refreshToken) {
            // We already have a refresh token, so fetch the access token
            return $this->getAccessTokenFromRefreshToken($refreshToken);
        }

        // No refresh token available, redirect to OAuth consent page
        $authUrl =
            'https://accounts.google.com/o/oauth2/v2/auth?' .
            http_build_query([
                'client_id' => config('filesystems.disks.google.clientId'),
                'redirect_uri' => config('filesystems.disks.google.redirectUri'),
                'response_type' => 'code',
                'scope' => 'https://www.googleapis.com/auth/drive',
                'access_type' => 'offline', // Request offline access for refresh token
            ]);

        return redirect($authUrl);
    }

    private function getAccessTokenFromRefreshToken($refreshToken)
    {
        // Exchange the refresh token for an access token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('filesystems.disks.google.clientId'),
            'client_secret' => config('filesystems.disks.google.clientSecret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->ok()) {
            abort(500, 'Failed to get access token.');
        }

        return $response->json()['access_token'];
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->code;

        // Exchange the authorization code for access and refresh tokens
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('filesystems.disks.google.clientId'),
            'client_secret' => config('filesystems.disks.google.clientSecret'),
            'redirect_uri' => config('filesystems.disks.google.redirectUri'),
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->ok()) {
            abort(500, 'Failed to exchange authorization code for tokens.');
        }

        $tokens = $response->json();

        // Store refresh token securely in your database
        $user = Auth::user();
        $user->google_refresh_token = $tokens['refresh_token']; // Save to DB
        $user->save();

        return redirect('/preview-file')->with('message', 'Google account linked successfully!');
    }

    // Download method for files
    public function download($id)
    {
        $accessToken = $this->getAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        // Get file metadata
        $meta = Http::withHeaders($headers)->get("https://www.googleapis.com/drive/v3/files/{$id}?fields=name,mimeType");

        if (!$meta->ok()) {
            abort(403, 'Unable to access file metadata.');
        }

        $fileName = $meta['name'];
        $mimeType = $meta['mimeType'];

        // Force .zip if mimeType indicates it
        if ($mimeType === 'application/zip' || str_ends_with($fileName, '.zip')) {
            if (!str_ends_with($fileName, '.zip')) {
                $fileName .= '.zip';
            }
            $contentType = 'application/zip';
        } else {
            $contentType = $mimeType;
        }

        // Download file content
        $fileRes = Http::withHeaders($headers)->get("https://www.googleapis.com/drive/v3/files/{$id}?alt=media");

        if (!$fileRes->ok()) {
            abort(403, 'Unable to download file content.');
        }

        return response($fileRes->body(), 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($fileRes->body()));
    }

    // public function show($id)
    // {
    //     // 1. Check MIME type
    //     $mimeType = $this->googleDriveService->getFileMimeType($id);

    //     // 2. Convert if needed
    //     if ($mimeType !== 'application/vnd.google-apps.spreadsheet') {
    //         $id = $this->googleDriveService->convertDriveFileToGoogleSheet($id, 'Converted Sheet');
    //     }

    //     // 3. Make public
    //     $this->googleDriveService->makeFilePublic($id);

    //     // 4. Get embed URL
    //     $embedUrl = $this->googleDriveService->getGoogleSheetEmbedUrl($id);

    //     // 5. Pass to view
    //     return view('pages.apps.storage.sheet', compact('embedUrl', 'id'));
    // }

    // public function show($id)
    // {
    //     $mimeType = $this->googleDriveService->getFileMimeType($id);
    //     $fileName = $this->googleDriveService->getFileName($id);

    //     // Remove edit URLs since you don't want writer/edit mode
    //     $previewUrl = null;
    //     $downloadUrl = "https://drive.google.com/uc?id={$id}&export=download";

    //     //$this->googleDriveService->makeFilePublic($id);
    //     $this->googleDriveService->grantWriteAccessToUser($id, 'marketing@hillbcs.com');

    //     // Previewable types (PDF, images, videos, Google docs preview etc)
    //     if ($this->googleDriveService->isPreviewableMime($mimeType)) {
    //         $previewUrl = $this->googleDriveService->getPreviewUrl($id);
    //     }

    //     return view('pages.apps.storage.sheet', compact('fileName', 'mimeType', 'previewUrl', 'downloadUrl'));
    // }

    // public function show($id)
    // {
    //     $mimeType = $this->googleDriveService->getFileMimeType($id);
    //     $fileName = $this->googleDriveService->getFileName($id);
    //     $fileSizeBytes = $this->googleDriveService->getFileSize($id);
    //     $fileSizeFormatted = $this->googleDriveService->formatBytes($fileSizeBytes);

    //     $this->googleDriveService->makeFilePublic($id);
    //     // if (Auth::user()->email == 'demo@hillbcs.com') {
    //     //     $this->googleDriveService->makeFilePublic($id);
    //     //     //$email = 'douglas.hill2012@gmail.com';
    //     // } else {
    //     //     $email = Auth::user()->email;
    //     //     $this->googleDriveService->grantWriteAccessToUser($id, $email);
    //     // }
    //     //$this->googleDriveService->grantWriteAccessToUser($id, $email);

    //     if ($this->googleDriveService->isPreviewableMime($mimeType)) {
    //         $previewUrl = $this->googleDriveService->getPreviewUrl($id);
    //     }
    //     $downloadUrl = "https://drive.google.com/uc?id={$id}&export=download";

    //     return view('pages.apps.storage.sheet', compact('fileName', 'mimeType', 'previewUrl', 'downloadUrl', 'fileSizeFormatted'));
    // }

    public function show($id)
    {
        $mimeType = $this->googleDriveService->getFileMimeType($id);
        $fileName = $this->googleDriveService->getFileName($id);
        $fileSizeBytes = $this->googleDriveService->getFileSize($id);
        $fileSizeFormatted = $this->googleDriveService->formatBytes($fileSizeBytes);

        $this->googleDriveService->makeFilePublic($id);

        // initialize variable
        $previewUrl = null;

        if ($this->googleDriveService->isPreviewableMime($mimeType)) {
            $previewUrl = $this->googleDriveService->getPreviewUrl($id);
        }

        $downloadUrl = "https://drive.google.com/uc?id={$id}&export=download";

        return view('pages.apps.storage.sheet', compact(
            'fileName',
            'mimeType',
            'previewUrl',
            'downloadUrl',
            'fileSizeFormatted'
        ));
    }

    public function remove_proj_file(Request $request)
    {
        $googleDriveId = $request->google_drive_id;

        try {
            // Find the project that has this file in proj_documents
            $project = ProjectBidding::whereRaw("JSON_SEARCH(proj_documents, 'one', ?) IS NOT NULL", [$googleDriveId])->firstOrFail();

            // Get already-cast array (no need to json_decode)
            $documents = $project->proj_documents;

            // Remove the file with matching google_drive_id
            $updatedDocs = array_values(
                array_filter($documents, function ($doc) use ($googleDriveId) {
                    return $doc['google_drive_id'] !== $googleDriveId;
                }),
            );

            // Save updated JSON
            $project->proj_documents = $updatedDocs;
            $project->save();

            // Optional: remove from FileManager
            FileManager::where('google_drive_id', $googleDriveId)->update(['isDeleted', 0]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function remove_scope_file(Request $request)
    {
        $request->validate([
            'stageKey' => 'required|string',
            'docId' => 'required|integer',
        ]);

        $stageKey = $request->input('stageKey');
        $docId = $request->input('docId');
        $id = $request->input('id');

        // Load the project or entity holding the documents JSON column
        $project = DB::table('t_project_bidding')->where('id', $id)->first(); // adjust for your use case

        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found']);
        }

        // Decode the JSON column, assuming it's called 'stage_proj_documents'
        $documents = json_decode($project->stage_proj_documents, true);

        if (!isset($documents[$stageKey])) {
            return response()->json(['success' => false, 'message' => 'Stage not found']);
        }

        // Filter out the document with the given docId
        $updatedDocs = array_filter($documents[$stageKey], fn($doc) => $doc['id'] !== $docId);

        // Re-index array to avoid gaps (optional)
        $documents[$stageKey] = array_values($updatedDocs);

        // Save back the updated JSON to DB
        DB::table('t_project_bidding')
            ->where('id', $project->id)
            ->update(['stage_proj_documents' => json_encode($documents)]);

        FileManager::where('id', $docId)->update(['isDeleted', 0]);

        return response()->json(['success' => true]);
    }
}
