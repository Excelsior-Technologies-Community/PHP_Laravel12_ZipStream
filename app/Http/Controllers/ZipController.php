<?php

namespace App\Http\Controllers;

use STS\ZipStream\Facades\Zip;

class ZipController extends Controller
{
    public function index()
    {
        return view('zip.index');
    }

    public function downloadZip()
    {
        $files = [
            'sample1.txt' => storage_path('app/public/sample1.txt'),
            'sample2.txt' => storage_path('app/public/sample2.txt'),
        ];

        return Zip::create('myfiles.zip', $files);
    }
}