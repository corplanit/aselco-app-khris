<?php
// app/Http/Controllers/ChunkUploadController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\FileManager;
use App\Models\User;

class ChunkUploadController extends Controller
{
    public function chunk(Request $req)
    {
        $req->headers->set('Accept', 'application/json');
        @set_time_limit(0);
        @ini_set('memory_limit', '-1');
        @ignore_user_abort(true);

        $x = request('f') ?? '111111';

        try {
            $validated = $req->validate([
                'chunk' => 'required|file',
                'file_id' => 'required|string|max:128',
                'index' => 'required|integer|min:0',
                'total' => 'required|integer|min:1|max:5120000',
                'filename' => 'required|string|max:255',
                'size' => 'required|integer|min:1',
                'parent_id' => 'nullable',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Validation failed: ' . $e->getMessage()], 422);
        }

        // Normalize parent_id to numeric or null
        $parentId = $req->input('parent_id');
        // if (!is_null($parentId)) {
        //     $parentId = (int)$parentId;
        //     if ($parentId <= 0 || !FileManager::whereKey($parentId)->exists()) {
        //         return response()->json(['error' => 'Parent folder not found'], 422);
        //     }
        // } else {
        //     $parentId = null;
        // }

        $fileId = preg_replace('/[^A-Za-z0-9\-]/', '', $validated['file_id']);
        $index = (int) $validated['index'];
        $total = (int) $validated['total'];
        $orig = $validated['filename'];
        $declaredSize = (int) $validated['size'];

        if ($index >= $total) {
            return response()->json(['error' => 'Chunk index out of range'], 400);
        }

        $tmpDir = storage_path("app/chunks/{$fileId}");
        $chunkDst = $tmpDir . "/chunk_{$index}.part";
        $lockPath = $tmpDir . '/stitch.lock';

        if (!is_dir($tmpDir) && !@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
            return response()->json(['error' => 'Cannot create temporary directory'], 500);
        }

        // ---- Write this chunk atomically (idempotent)
        try {
            $inPath = $req->file('chunk')->getRealPath();
            if (!$inPath || !is_readable($inPath)) {
                return response()->json(['error' => 'Uploaded chunk is not readable'], 400);
            }

            if (is_file($chunkDst) && filesize($chunkDst) === filesize($inPath)) {
                // already there
            } else {
                $tmpWrite = $chunkDst . '.tmp';
                $in = fopen($inPath, 'rb');
                $out = fopen($tmpWrite, 'wb');
                if (!$in || !$out) {
                    if ($in) {
                        fclose($in);
                    }
                    if ($out) {
                        fclose($out);
                    }
                    return response()->json(['error' => 'Failed to open streams for chunk write'], 500);
                }
                $buf = 8 * 1024 * 1024;
                while (!feof($in)) {
                    $data = fread($in, $buf);
                    if ($data === false) {
                        fclose($in);
                        fclose($out);
                        @unlink($tmpWrite);
                        return response()->json(['error' => 'Read error while writing chunk'], 500);
                    }
                    if (fwrite($out, $data) === false) {
                        fclose($in);
                        fclose($out);
                        @unlink($tmpWrite);
                        return response()->json(['error' => 'Write error while writing chunk'], 500);
                    }
                }
                fclose($in);
                fclose($out);
                if (!@rename($tmpWrite, $chunkDst)) {
                    @unlink($tmpWrite);
                    return response()->json(['error' => 'Failed to finalize chunk file'], 500);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Chunk write failed', ['ex' => $e, 'fileId' => $fileId, 'index' => $index]);
            return response()->json(['error' => 'Chunk write failed: ' . $e->getMessage()], 500);
        }

        // ---- Finalize when all chunks exist (with a lock)
        $lockFp = @fopen($lockPath, 'c');
        if ($lockFp && @flock($lockFp, LOCK_EX)) {
            try {
                for ($i = 0; $i < $total; $i++) {
                    if (!is_file($tmpDir . "/chunk_{$i}.part")) {
                        @flock($lockFp, LOCK_UN);
                        @fclose($lockFp);
                        return response()->json(['ok' => true, 'chunk' => $index]);
                    }
                }

                // Which user owns the file
                if (session('manage_portal_id')) {
                    $uploader = session()->get('manage_orignal_id');
                    $user_id = session()->get('manage_portal_id');
                } else {
                    $uploader = Auth::id();
                    $user_id = $uploader;
                }
                if (session('impersonator_email')) {
                    $impersonating_id = User::where('email', session('impersonator_email'))->value('id');
                } else {
                    $impersonating_id = Auth::id();
                }

                // Per-user directory
                $userDir = public_path('file-manager' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . 'files');
                if (!is_dir($userDir) && !@mkdir($userDir, 0775, true) && !is_dir($userDir)) {
                    return response()->json(['error' => 'Cannot create destination directory'], 500);
                }

                $free = @disk_free_space($userDir);
                if ($free !== false && $free < $declaredSize + 64 * 1024 * 1024) {
                    return response()->json(['error' => 'Insufficient disk space'], 507);
                }

                // Stitch into a TEMP file first (race-safe), then rename to unique name
                $tmpFinal = $userDir . DIRECTORY_SEPARATOR . '.~' . Str::uuid() . '.partmerge';
                $out = @fopen($tmpFinal, 'wb');
                if (!$out) {
                    return response()->json(['error' => 'Unable to open destination file for writing'], 500);
                }

                $buf = 8 * 1024 * 1024;
                for ($i = 0; $i < $total; $i++) {
                    $part = $tmpDir . "/chunk_{$i}.part";
                    $in = @fopen($part, 'rb');
                    if (!$in) {
                        fclose($out);
                        @unlink($tmpFinal);
                        return response()->json(['error' => "Cannot open chunk {$i} for stitching"], 500);
                    }
                    while (!feof($in)) {
                        $data = fread($in, $buf);
                        if ($data === false) {
                            fclose($in);
                            fclose($out);
                            @unlink($tmpFinal);
                            return response()->json(['error' => 'Read error during stitching'], 500);
                        }
                        if (fwrite($out, $data) === false) {
                            fclose($in);
                            fclose($out);
                            @unlink($tmpFinal);
                            return response()->json(['error' => 'Write error during stitching'], 500);
                        }
                    }
                    fclose($in);
                    @unlink($part);
                }
                fclose($out);
                @rmdir($tmpDir);

                // Verify size of stitched temp
                $actual = @filesize($tmpFinal);
                if ($actual !== false && $actual != $declaredSize) {
                    @unlink($tmpFinal);
                    return response()->json(['error' => 'Size mismatch after stitching'], 422);
                }

                // Build a UNIQUE final name using " (n)" semantics
                $origBaseRaw = pathinfo($orig, PATHINFO_FILENAME);
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                $safeBase = $this->sanitizeBase($origBaseRaw); // keeps letters, numbers, spaces, dots, dashes, underscores, parentheses
                $finalName = $this->uniqueName($safeBase, $ext, $user_id, $parentId, $userDir);
                $finalPath = $userDir . DIRECTORY_SEPARATOR . $finalName;
                $relative = 'file-manager/' . $user_id . '/files/' . $finalName;

                // Try to move temp to the unique final name; if it exists, recompute and retry (race-safe)
                $maxRetry = 20;
                $tries = 0;
                while (!@rename($tmpFinal, $finalPath)) {
                    if (!file_exists($finalPath)) {
                        // rename failed for another reason
                        @unlink($tmpFinal);
                        return response()->json(['error' => 'Failed to move stitched file'], 500);
                    }
                    if (++$tries > $maxRetry) {
                        @unlink($tmpFinal);
                        return response()->json(['error' => 'Too many rename retries for unique name'], 500);
                    }
                    $finalName = $this->uniqueName($safeBase, $ext, $user_id, $parentId, $userDir);
                    $finalPath = $userDir . DIRECTORY_SEPARATOR . $finalName;
                    $relative = 'file-manager/' . $user_id . '/files/' . $finalName;
                }

                // Unique short link
                do {
                    $new_link = Str::random(7);
                } while (FileManager::where('link', $new_link)->exists());

                // Detect mime (best effort)
                $mime = null;
                try {
                    if (function_exists('finfo_open')) {
                        $f = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($f, $finalPath);
                        finfo_close($f);
                    }
                } catch (\Throwable $e) {
                }

                // Save DB row (NO Google Drive) — 'name' will be the deduped display name
                $record = FileManager::create([
                    'link' => $new_link,
                    'name' => $finalName, // <— display name now includes (n) when needed
                    'google_drive_id' => null,
                    'path' => $relative, // relative path in public/
                    'size' => $actual ?: $declaredSize,
                    'format' => $ext,
                    'mime_type' => $mime,
                    'user_id' => $user_id,
                    'parent_id' => $parentId,
                    'is_folder' => false,
                    'uploader_id' => $impersonating_id,
                ]);

                @flock($lockFp, LOCK_UN);
                @fclose($lockFp);

                return response()->json([
                    'ok' => true,
                    'id' => $record->id,
                    'url' => url($relative),
                    'name' => $record->name, // already deduped
                    'size' => $record->size,
                    'mime' => $record->mime_type,
                    'path' => $record->path,
                ]);
            } catch (\Throwable $e) {
                Log::error('Finalize failed', ['ex' => $e, 'fileId' => $fileId]);
                @flock($lockFp, LOCK_UN);
                @fclose($lockFp);
                return response()->json(['error' => 'Finalize failed: ' . $e->getMessage()], 500);
            }
        } elseif ($lockFp) {
            @fclose($lockFp);
            return response()->json(['ok' => true, 'chunk' => $index]);
        }

        return response()->json(['ok' => true, 'chunk' => $index]);
    }

    /**
     * Sanitize a base name (no extension). Keep letters, numbers, space, dot, dash, underscore, parentheses.
     */
    private function sanitizeBase(string $base): string
    {
        $base = preg_replace('/[^A-Za-z0-9\.\-\_\ \(\)]/', '', $base) ?: 'file';
        // collapse excessive spaces
        $base = trim(preg_replace('/\s+/', ' ', $base));
        // guard length (reserve room for " (123)" and extension)
        return mb_substr($base, 0, 200);
    }

    /**
     * Return a unique filename with " (n)" if needed, checking both DB (same user/parent) and filesystem.
     * $ext should be the extension WITHOUT dot ('' if none).
     */
    private function uniqueName(string $base, string $ext, int $userId, ?string $parentId, string $dir): string
    {
        $dotExt = $ext !== '' ? ".{$ext}" : '';
        $n = 0;

        // Preload existing names from DB that start with "$base" (fast filter)
        $query = FileManager::where('user_id', $userId)
            ->where('is_folder', false)
            ->when($parentId !== null, fn($q) => $q->where('parent_id', $parentId))
            ->where(function ($q) use ($base) {
                $q->where('name', $base)->orWhere('name', 'like', $base . ' (%)%'); // handles "Base (n).ext"
            })
            ->pluck('name')
            ->toArray();

        // Try sequentially until neither DB nor FS has its
        while (true) {
            $name = $n === 0 ? "{$base}{$dotExt}" : "{$base} ({$n}){$dotExt}";

            $tooLong = mb_strlen($name) > 255;
            if ($tooLong) {
                // trim base to fit
                $suffix = $n === 0 ? $dotExt : " ({$n}){$dotExt}";
                $allowed = 255 - mb_strlen($suffix);
                $name = mb_substr($base, 0, max(1, $allowed)) . $suffix;
            }

            $existsInDb =
                in_array($name, $query, true) ||
                FileManager::where('user_id', $userId)
                    ->when($parentId !== null, fn($q) => $q->where('parent_id', $parentId))
                    ->where('name', $name)
                    ->exists();

            $existsOnFs = file_exists($dir . DIRECTORY_SEPARATOR . $name);

            if (!$existsInDb && !$existsOnFs) {
                return $name;
            }
            $n++;
            if ($n > 5000) {
                // safety
                return Str::uuid() . $dotExt;
            }
        }
    }
}
