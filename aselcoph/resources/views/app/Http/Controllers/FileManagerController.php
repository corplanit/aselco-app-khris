<?php

namespace App\Http\Controllers;

use App\Models\ContactPerson;
use App\Models\FileManager;
use App\Models\FileSubmitted;
use App\Models\ProjectBidding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Mail\SendCustomEmail;
use App\Models\Clients;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class FileManagerController extends Controller
{
    public function index()
    {
        return view('modules.file-manager.index');
        //return view('pages.apps.storage.index');
    }

    public function index_v2()
    {
        return view('modules.file-manager.v2.index');
    }

    public function api_files(Request $request)
    {
        $folderId = $request->id;
        $clientId = Auth::user()->id;

        try {
            $files = FileManager::where('parent_id', $folderId)
                ->where('user_id', $clientId)
                ->where('is_folder', 0)
                ->where('isDeleted', 0)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($file) {
                    $size = $file->size;
                    if ($size >= 1073741824) {
                        $readableSize = number_format($size / 1073741824, 2) . ' GB';
                    } elseif ($size >= 1048576) {
                        $readableSize = number_format($size / 1048576, 2) . ' MB';
                    } elseif ($size >= 1024) {
                        $readableSize = number_format($size / 1024, 2) . ' KB';
                    } elseif ($size > 1) {
                        $readableSize = $size . ' bytes';
                    } elseif ($size == 1) {
                        $readableSize = '1 byte';
                    } else {
                        $readableSize = '0 bytes';
                    }

                    $uploader = User::where('id', $file->uploader_id)->first();

                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'format' => strtoupper($file->format),
                        'type' => $file->format,
                        'ulink' => $file->link,
                        'size' => $readableSize,
                        'link' => $file->google_drive_id ? 'file-manager/preview/' . $file->google_drive_id : $file->path,
                        'created_at' => date_format($file->created_at, 'm/d/Y'),
                        'google_drive_id' => $file->google_drive_id,
                        'uploaded' => $uploader->name ?? '-',
                    ];
                });

            return response()->json(['data' => $files,
                        'folderId' => $folderId,
                        'clientId' => $clientId,], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function xindex($parent_id = null)
    {
        $files = FileManager::where('parent_id', $parent_id)
            ->where('user_id', Auth::user()->id)
            ->where('isDeleted', 0)
            ->get();
        $parent = $parent_id ? FileManager::findOrFail($parent_id) : null;
        return view('pages.apps.storage.index', compact('files', 'parent'));
    }

    public function rename(Request $request, $id)
    {
        FileManager::where('id', $id)->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Rename successfully!');
    }

    public function submitted(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:t_file_manager,id',
            'proj_id' => 'required|exists:t_project_bidding,id',
        ]);

        $userId = Auth::id(); // Get authenticated user ID

        $contact_ids = '';

        DB::transaction(function () use ($validatedData, $request, $userId) {
            FileSubmitted::where('user_id', $userId)->where('project_id', $request->proj_id)->delete();
            foreach ($validatedData['file_ids'] as $fileId) {
                FileSubmitted::create([
                    'file_id' => $fileId,
                    'project_id' => $request->proj_id,
                    'user_id' => $userId,
                ]);
            }

            // Fetch project
            $project = ProjectBidding::findOrFail($request->proj_id);
            $bidders = is_array($project->proj_bidders) ? $project->proj_bidders : [];

            if (empty($bidders)) {
                return response()->json(['message' => 'No bidders found.'], 404);
            }

            // Fetch all contact persons at once (avoid N+1 query issue)
            $contactPersons = ContactPerson::whereIn('id', $bidders)->get();

            if ($contactPersons->isEmpty()) {
                return response()->json(['message' => 'No contact persons found for bidders.'], 404);
            }

            $ext_email = User::where('assign_id', $userId)->first()->email ?? 'hillbcservices@gmail.com';

            foreach ($contactPersons as $person) {
                Mail::to($person->email)
                    ->cc([Auth::user()->email, $ext_email]) // 👈 add one or multiple CC recipients here // 'hillbcservices@gmail.com',
                    ->send(new SendCustomEmail($request->proj_id, $person->id, $person->company_id, null, $project->proj_name));
            }
        });

        return response()->json([
            'message' => 'Files submitted successfully and notifications sent.',
            'id' => $contact_ids,
        ]);
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'files' => 'required|array', // Ensure it's an array of files
    //         'files.*' => 'file|max:512000', // Validate each file, max 5MB
    //         'parent_id' => 'nullable|exists:file_managers,id',
    //     ]);

    //     if ($request->hasFile('files')) {
    //         foreach ($request->file('files') as $file) {
    //             $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
    //             $path = $file->storeAs('file-manager', $filename, 'public');

    //             do {
    //                 $new_link = Str::random(7);
    //             } while (FileManager::where('link', $new_link)->exists());

    //             FileManager::create([
    //                 'link' => $new_link,
    //                 'name' => $file->getClientOriginalName(),
    //                 'path' => $path,
    //                 'size' => $file->getSize(),
    //                 'format' => $file->getClientOriginalExtension(),
    //                 'mime_type' => $file->getMimeType(),
    //                 'user_id' => Auth::user()->id,
    //                 'parent_id' => $request->parent_id,
    //                 'is_folder' => false,
    //             ]);
    //         }
    //     } else {
    //         FileManager::create([
    //             'name' => $request->name,
    //             'user_id' => Auth::user()->id,
    //             'parent_id' => $request->parent_id,
    //             'is_folder' => true,
    //         ]);
    //     }

    //     return redirect()->back()->with('success', 'Files uploaded successfully!');
    // }

    public function folder(Request $request)
    {
        do {
            $new_link = Str::random(7);
        } while (FileManager::where('link', $new_link)->exists());

        FileManager::create([
            'link' => $new_link,
            'name' => $request->name,
            'user_id' => Auth::user()->id,
            'parent_id' => $request->parent_id,
            'is_folder' => true,
        ]);

        return redirect()->back()->with('success', 'Folder Created successfully!');
    }

    public function destroy(FileManager $file)
    {
        if (!$file->is_folder) {
            Storage::delete($file->path);
        }
        $file->delete();
        return redirect('/file-manager/list')->with('success', 'Deleted successfully!');
    }

    public function updatePrivacy(Request $request)
    {
        $folder = FileManager::findOrFail($request->id);
        $folder->privacy = $request->privacy;
        $folder->save();

        return back()->with('success', 'Privacy updated successfully!');
    }

    public function preview($id)
    {
        return view('modules.file-manager.tables', compact('id'));
    }
}
