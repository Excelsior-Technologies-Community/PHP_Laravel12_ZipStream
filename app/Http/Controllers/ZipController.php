<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use STS\ZipStream\Facades\Zip;
use App\Models\ZipDownload;
use App\Models\SharedZip;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ZipController extends Controller
{
    public function index()
    {
        $availableFiles = $this->getAvailableFiles();
        $downloads = ZipDownload::latest()->take(10)->get();
        
        return view('zip.index', compact('availableFiles', 'downloads'));
    }

    public function downloadZip(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1'
        ]);

        $selectedFiles = [];
        $files = $request->input('files');
        $password = $request->input('password', null);

        foreach ($files as $file) {
            // Check both public disk and direct storage path
            $pathsToCheck = [
                storage_path('app/public/' . $file),
                storage_path('app/' . $file),
                public_path($file)
            ];
            
            foreach ($pathsToCheck as $path) {
                if (file_exists($path)) {
                    $selectedFiles[$path] = $file;
                    break;
                }
            }
        }

        if (empty($selectedFiles)) {
            return back()->with('error', 'No valid files selected');
        }

        $zipName = 'ZipStream_' . now()->format('Ymd_His') . '.zip';

        ZipDownload::create([
            'zip_name' => $zipName,
            'total_files' => count($selectedFiles),
            'is_password_protected' => !empty($password),
        ]);

        $zip = Zip::create($zipName, array_keys($selectedFiles));
        
        if (!empty($password)) {
            $zip->setPassword($password);
        }
        
        return $zip;
    }

    public function downloadZipAjax(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1'
        ]);

        $selectedFiles = [];
        $files = $request->input('files');
        
        foreach ($files as $file) {
            $pathsToCheck = [
                storage_path('app/public/' . $file),
                storage_path('app/' . $file),
                public_path($file)
            ];
            
            foreach ($pathsToCheck as $path) {
                if (file_exists($path)) {
                    $selectedFiles[$path] = basename($file);
                    break;
                }
            }
        }

        $zipName = 'ZipStream_' . now()->format('Ymd_His') . '.zip';
        $token = Str::random(32);

        SharedZip::create([
            'token' => $token,
            'zip_name' => $zipName,
            'file_paths' => json_encode(array_keys($selectedFiles)),
            'file_names' => json_encode($selectedFiles),
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'success' => true,
            'download_url' => route('zip.download.shared', $token),
            'zip_name' => $zipName,
            'total_files' => count($selectedFiles)
        ]);
    }

    public function downloadSharedZip($token)
    {
        $sharedZip = SharedZip::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $filePaths = json_decode($sharedZip->file_paths, true);
        
        if (empty($filePaths)) {
            abort(404, 'Files not found');
        }

        $existingFiles = [];
        foreach ($filePaths as $path) {
            if (file_exists($path)) {
                $existingFiles[] = $path;
            }
        }

        if (empty($existingFiles)) {
            abort(404, 'No valid files remaining');
        }

        $sharedZip->update(['downloaded_at' => now(), 'download_count' => $sharedZip->download_count + 1]);

        return Zip::create($sharedZip->zip_name, $existingFiles);
    }

    public function emailZipLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'files' => 'required|array|min:1'
        ]);

        $selectedFiles = [];
        $files = $request->input('files');
        
        foreach ($files as $file) {
            $pathsToCheck = [
                storage_path('app/public/' . $file),
                storage_path('app/' . $file),
                public_path($file)
            ];
            
            foreach ($pathsToCheck as $path) {
                if (file_exists($path)) {
                    $selectedFiles[$path] = basename($file);
                    break;
                }
            }
        }

        $zipName = 'ZipStream_' . now()->format('Ymd_His') . '.zip';
        $token = Str::random(32);

        SharedZip::create([
            'token' => $token,
            'zip_name' => $zipName,
            'file_paths' => json_encode(array_keys($selectedFiles)),
            'file_names' => json_encode($selectedFiles),
            'email' => $request->email,
            'expires_at' => now()->addHours(48),
        ]);

        // Send email (you need to configure mail first)
        // Mail::to($request->email)->send(new ZipDownloadLink($token, $zipName));

        return response()->json([
            'success' => true,
            'message' => 'Download link sent to ' . $request->email,
            'download_url' => route('zip.download.shared', $token)
        ]);
    }

    private function getAvailableFiles()
    {
        $files = [];
        
        // Get files from storage/app/public
        $publicFiles = Storage::disk('public')->files();
        foreach ($publicFiles as $file) {
            if (!in_array(basename($file), ['.gitignore'])) {
                $files[] = $file;
            }
        }
        
        // Also check direct storage path for sample files
        $sampleDir = storage_path('app/public/');
        if (is_dir($sampleDir)) {
            foreach (scandir($sampleDir) as $file) {
                if ($file !== '.' && $file !== '..' && !in_array($file, $files)) {
                    $files[] = $file;
                }
            }
        }
        
        return array_values(array_unique($files));
    }
}