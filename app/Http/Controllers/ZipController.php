<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use STS\ZipStream\Facades\Zip;
use App\Models\ZipDownload;

class ZipController extends Controller
{
    public function index()
    {
        $availableFiles = [
            'sample1.txt',
            'sample2.txt',
            'sample3.txt',
        ];

        $downloads = ZipDownload::latest()->take(5)->get();

        return view('zip.index', compact('availableFiles', 'downloads'));
    }

    public function downloadZip(Request $request)
{
    $request->validate([
        'files' => 'required|array|min:1'
    ]);

    $selectedFiles = [];

    $files = $request->input('files');

    foreach ($files as $file) {

        $path = storage_path('app/public/' . $file);

        if (file_exists($path)) {
            $selectedFiles[] = $path;
        }
    }

    $zipName = 'ZipStream_' . now()->format('Ymd_His') . '.zip';

    ZipDownload::create([
        'zip_name' => $zipName,
        'total_files' => count($selectedFiles),
    ]);

    return Zip::create($zipName, $selectedFiles);
}
}