<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use STS\ZipStream\Facades\Zip;
use App\Models\ZipDownload;
use App\Models\SharedZip;
use App\Models\ZipJob;
use App\Jobs\ProcessZipJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ZipController extends Controller
{
    public function index()
    {
        $availableFiles = $this->getAvailableFiles();

        $availableFolders = $this->getAvailableFolders();

        $downloads = ZipDownload::latest()->take(10)->get();

        $zipJobs = ZipJob::latest()->take(5)->get();

        return view('zip.index', compact(
            'availableFiles',
            'availableFolders',
            'downloads',
            'zipJobs'
        ));
    }

    public function downloadZip(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1'
        ]);

        $selectedFiles = [];
        $files         = $request->input('files');
        $password      = $request->input('password', null);

        foreach ($files as $file) {
            $resolvedPath = $this->resolveFilePath($file);
            if ($resolvedPath) {
                $selectedFiles[$resolvedPath] = $file;
            }
        }

        if (empty($selectedFiles)) {
            return back()->with('error', 'No valid files selected');
        }

        $zipName = 'ZipStream_' . now()->format('Ymd_His') . '.zip';

        ZipDownload::create([
            'zip_name'              => $zipName,
            'total_files'           => count($selectedFiles),
            'is_password_protected' => !empty($password),
            'user_ip'               => $request->ip(),
        ]);

        if (!empty($password)) {
            $zipPath = $this->buildEncryptedZip($zipName, $selectedFiles, $password);
            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        }

        return Zip::create($zipName, array_keys($selectedFiles));
    }

    public function downloadZipAjax(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1'
        ]);

        $password = $request->input('password', null);

        $selectedFiles = [];

        foreach ($request->input('files') as $file) {
            $resolvedPath = $this->resolveFilePath($file);
            if ($resolvedPath) {
                $selectedFiles[$resolvedPath] = basename($file);
            }
        }

        if (empty($selectedFiles)) {
            return response()->json(['success' => false, 'message' => 'No valid files found'], 422);
        }

        $zipName = 'ZipStream_' . now()->format('Ymd_His') . '.zip';
        $token   = Str::random(64);

        if (!empty($password)) {
            $zipPath = $this->buildEncryptedZip($zipName, $selectedFiles, $password);

            SharedZip::create([
                'token'       => $token,
                'zip_name'    => $zipName,
                'file_paths'  => json_encode([$zipPath]),
                'file_names'  => json_encode([$zipName]),
                'expires_at'  => now()->addHours(24),
                'is_prebuilt' => true,
            ]);
        } else {
            SharedZip::create([
                'token'      => $token,
                'zip_name'   => $zipName,
                'file_paths' => json_encode(array_keys($selectedFiles)),
                'file_names' => json_encode($selectedFiles),
                'expires_at' => now()->addHours(24),
            ]);
        }

        ZipDownload::create([
            'zip_name'              => $zipName,
            'total_files'           => count($selectedFiles),
            'is_password_protected' => !empty($password),
            'user_ip'               => $request->ip(),
        ]);

        return response()->json([
            'success'      => true,
            'download_url' => route('zip.download.shared', $token),
            'zip_name'     => $zipName,
            'total_files'  => count($selectedFiles),
        ]);
    }

    public function downloadFolderZip(Request $request)
    {
        $request->validate([
            'folders' => 'required|array|min:1'
        ]);

        $password      = $request->input('password', null);
        $selectedFiles = [];
        $folders       = $request->input('folders');

        foreach ($folders as $folder) {
            $folderPath = storage_path('app/public/' . ltrim($folder, '/'));

            if (!is_dir($folderPath)) {
                continue;
            }

            $folderFiles = $this->getFilesRecursively($folderPath);

            foreach ($folderFiles as $filePath) {
                $relativePath = $folder . '/' . basename($filePath);
                $selectedFiles[$filePath] = $relativePath;
            }
        }

        if (empty($selectedFiles)) {
            return back()->with('error', 'No files found in selected folders');
        }

        $zipName = 'FolderZip_' . now()->format('Ymd_His') . '.zip';

        ZipDownload::create([
            'zip_name'              => $zipName,
            'total_files'           => count($selectedFiles),
            'is_password_protected' => !empty($password),
            'user_ip'               => $request->ip(),
        ]);

        if (!empty($password)) {
            $zipPath = $this->buildEncryptedZip($zipName, $selectedFiles, $password);
            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        }

        return Zip::create($zipName, array_keys($selectedFiles));
    }

    public function queueZipJob(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1'
        ]);

        $password  = $request->input('password', null);
        $filePaths = [];

        foreach ($request->input('files') as $file) {
            $resolvedPath = $this->resolveFilePath($file);
            if ($resolvedPath) {
                $filePaths[] = $resolvedPath;
            }
        }

        if (empty($filePaths)) {
            return response()->json(['success' => false, 'message' => 'No valid files found'], 422);
        }

        $zipName = 'QueueZip_' . now()->format('Ymd_His');

        $zipJob = ZipJob::create([
            'zip_name'   => $zipName,
            'status'     => 'pending',
            'file_paths' => json_encode($filePaths),
            'password'   => $password,
        ]);

        ProcessZipJob::dispatch($zipJob);

        return response()->json([
            'success'    => true,
            'job_id'     => $zipJob->id,
            'message'    => 'ZIP is being prepared in background. Check status below.',
            'status_url' => route('zip.job.status', $zipJob->id),
        ]);
    }

    public function checkJobStatus($jobId)
    {
        $zipJob = ZipJob::findOrFail($jobId);

        $response = [
            'job_id'   => $zipJob->id,
            'status'   => $zipJob->status,
            'zip_name' => $zipJob->zip_name,
        ];

        if ($zipJob->status === 'completed' && $zipJob->download_url) {
            $response['download_url'] = route('zip.job.download', $zipJob->id);
        }

        return response()->json($response);
    }

    public function downloadJobZip($jobId)
    {
        $zipJob = ZipJob::findOrFail($jobId);

        if ($zipJob->status !== 'completed') {
            return response()->json(['error' => 'ZIP not ready yet'], 400);
        }

        $filePath = $zipJob->download_url;

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'ZIP file not found'], 404);
        }

        return response()->download($filePath, $zipJob->zip_name . '.zip')->deleteFileAfterSend(false);
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

        $existingFiles = array_filter($filePaths, fn($path) => file_exists($path));

        if (empty($existingFiles)) {
            abort(404, 'No valid files remaining');
        }

        $sharedZip->update([
            'downloaded_at'  => now(),
            'download_count' => $sharedZip->download_count + 1,
        ]);

        if ($sharedZip->is_prebuilt) {
            return response()->download(array_values($existingFiles)[0], $sharedZip->zip_name);
        }

        return Zip::create($sharedZip->zip_name, array_values($existingFiles));
    }

    public function emailZipLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'files' => 'required|array|min:1',
        ]);

        $password      = $request->input('password', null);
        $selectedFiles = [];

        foreach ($request->input('files') as $file) {
            $resolvedPath = $this->resolveFilePath($file);
            if ($resolvedPath) {
                $selectedFiles[$resolvedPath] = basename($file);
            }
        }

        if (empty($selectedFiles)) {
            return response()->json(['success' => false, 'message' => 'No valid files found'], 422);
        }

        $zipName = 'ZipStream_' . now()->format('Ymd_His') . '.zip';
        $token   = Str::random(64);

        if (!empty($password)) {
            $zipPath = $this->buildEncryptedZip($zipName, $selectedFiles, $password);

            SharedZip::create([
                'token'       => $token,
                'zip_name'    => $zipName,
                'file_paths'  => json_encode([$zipPath]),
                'file_names'  => json_encode([$zipName]),
                'email'       => $request->email,
                'expires_at'  => now()->addHours(48),
                'is_prebuilt' => true,
            ]);
        } else {
            SharedZip::create([
                'token'      => $token,
                'zip_name'   => $zipName,
                'file_paths' => json_encode(array_keys($selectedFiles)),
                'file_names' => json_encode($selectedFiles),
                'email'      => $request->email,
                'expires_at' => now()->addHours(48),
            ]);
        }

        ZipDownload::create([
            'zip_name'              => $zipName,
            'total_files'           => count($selectedFiles),
            'is_password_protected' => !empty($password),
            'user_ip'               => $request->ip(),
        ]);

        $downloadUrl = route('zip.download.shared', $token);

        return response()->json([
            'success'      => true,
            'message'      => 'Download link ready for ' . $request->email,
            'download_url' => $downloadUrl,
        ]);
    }

    private function buildEncryptedZip(string $zipName, array $selectedFiles, string $password): string
    {
        $outputDir = storage_path('app/public/zips/');

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $zipPath = $outputDir . $zipName;

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->setPassword($password);

        foreach ($selectedFiles as $fullPath => $nameInZip) {
            if (!file_exists($fullPath)) {
                continue;
            }

            $zip->addFile($fullPath, $nameInZip);
            $zip->setEncryptionName($nameInZip, ZipArchive::EM_AES_256, $password);
        }

        $zip->close();

        return $zipPath;
    }

    private function resolveFilePath(string $file): ?string
    {
        $pathsToCheck = [
            storage_path('app/public/' . $file),
            storage_path('app/' . $file),
            public_path($file),
        ];

        foreach ($pathsToCheck as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function getAvailableFiles(): array
    {
        $files     = [];
        $skipFiles = ['.gitignore', '.gitkeep', 'index.html', '.htaccess'];

        $publicFiles = Storage::disk('public')->files();
        foreach ($publicFiles as $file) {
            if (!in_array(basename($file), $skipFiles)) {
                $files[] = $file;
            }
        }

        $sampleDir = storage_path('app/public/');
        if (is_dir($sampleDir)) {
            foreach (scandir($sampleDir) as $file) {
                if ($file !== '.' && $file !== '..' && !in_array($file, $files) && !in_array($file, $skipFiles)) {
                    if (is_file($sampleDir . $file)) {
                        $files[] = $file;
                    }
                }
            }
        }

        return array_values(array_unique($files));
    }

    private function getAvailableFolders(): array
    {
        $folders = [];
        $baseDir = storage_path('app/public/');

        if (!is_dir($baseDir)) {
            return $folders;
        }

        foreach (scandir($baseDir) as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($baseDir . $item)) {
                $folders[] = [
                    'name'       => $item,
                    'file_count' => count($this->getFilesRecursively($baseDir . $item)),
                ];
            }
        }

        return $folders;
    }

    private function getFilesRecursively(string $directory): array
    {
        $files = [];

        if (!is_dir($directory)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}