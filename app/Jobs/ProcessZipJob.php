<?php

namespace App\Jobs;

use App\Models\ZipJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ZipStream\ZipStream;
use ZipArchive;

class ProcessZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    public ZipJob $zipJob;

    public function __construct(ZipJob $zipJob)
    {
        $this->zipJob = $zipJob;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $this->zipJob->update(['status' => 'processing']);

        $outputDir  = storage_path('app/public/zips/');
        $outputFile = $outputDir . $this->zipJob->zip_name . '.zip';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $rawPaths = $this->zipJob->file_paths;

        $filePaths = is_array($rawPaths) ? $rawPaths : json_decode($rawPaths, true);

        if (empty($filePaths) || !is_array($filePaths)) {
            $this->zipJob->update(['status' => 'failed']);
            return;
        }

        $password = $this->zipJob->password ?? null;

        if (!empty($password)) {
            $this->buildEncrypted($filePaths, $outputFile, $password);
            return;
        }

        $this->buildPlain($filePaths, $outputFile);
    }

    private function buildEncrypted(array $filePaths, string $outputFile, string $password): void
    {
        try {
            $zip = new ZipArchive();
            $zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->setPassword($password);

            $addedAny = false;

            foreach ($filePaths as $path) {
                if (!file_exists($path)) {
                    continue;
                }

                $entryName = basename($path);
                $zip->addFile($path, $entryName);
                $zip->setEncryptionName($entryName, ZipArchive::EM_AES_256, $password);

                $addedAny = true;
            }

            $zip->close();

            if (!$addedAny) {
                @unlink($outputFile);
                $this->zipJob->update(['status' => 'failed']);
                return;
            }

            $this->zipJob->update([
                'status'       => 'completed',
                'download_url' => $outputFile,
            ]);

        } catch (\Throwable $e) {
            $this->zipJob->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function buildPlain(array $filePaths, string $outputFile): void
    {
        $outputStream = fopen($outputFile, 'w');

        if ($outputStream === false) {
            $this->zipJob->update(['status' => 'failed']);
            return;
        }

        try {
            $zip = new ZipStream(
                outputStream: $outputStream,
                sendHttpHeaders: false,
                enableZip64: true,
            );

            $addedAny = false;

            foreach ($filePaths as $path) {
                if (!file_exists($path)) {
                    continue;
                }

                $zip->addFileFromPath(
                    fileName: basename($path),
                    path: $path
                );

                $addedAny = true;
            }

            if (!$addedAny) {
                fclose($outputStream);
                @unlink($outputFile);
                $this->zipJob->update(['status' => 'failed']);
                return;
            }

            $zip->finish();
            fclose($outputStream);

            $this->zipJob->update([
                'status'       => 'completed',
                'download_url' => $outputFile,
            ]);

        } catch (\Throwable $e) {
            if (is_resource($outputStream)) {
                fclose($outputStream);
            }

            $this->zipJob->update(['status' => 'failed']);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->zipJob->update(['status' => 'failed']);
    }
}