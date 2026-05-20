<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        
        // Avoid duplicate names
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $counter = 1;
        
        while (Storage::disk('public')->exists($filename . '.' . $extension)) {
            $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $counter;
            $counter++;
        }
        
        $storedName = $filename . '.' . $extension;
        $path = $file->storeAs('', $storedName, 'public');
        
        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file' => $storedName,
            'size' => $this->formatBytes($file->getSize())
        ]);
    }

    public function delete($filename)
    {
        $deleted = Storage::disk('public')->delete($filename);
        
        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'File deleted successfully']);
        }
        
        return response()->json(['success' => false, 'message' => 'File not found'], 404);
    }

    public function preview($filename)
    {
        $path = storage_path('app/public/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $content = file_get_contents($path);
        
        // For images, return the file directly
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return response($content)->header('Content-Type', mime_content_type($path));
        }
        
        // For text files, return content as JSON
        if (in_array(strtolower($extension), ['txt', 'csv', 'md', 'json', 'xml', 'html', 'css', 'js', 'php'])) {
            return response()->json([
                'filename' => $filename,
                'content' => htmlspecialchars($content),
                'size' => $this->formatBytes(filesize($path))
            ]);
        }
        
        return response()->json([
            'filename' => $filename,
            'message' => 'Preview not available for this file type',
            'size' => $this->formatBytes(filesize($path))
        ]);
    }

    public function list()
    {
        $files = Storage::disk('public')->files();
        $fileDetails = [];
        
        foreach ($files as $file) {
            if (in_array($file, ['.gitignore'])) continue;
            
            $path = storage_path('app/public/' . $file);
            $fileDetails[] = [
                'name' => $file,
                'size' => file_exists($path) ? $this->formatBytes(filesize($path)) : '0 B',
                'modified' => file_exists($path) ? date('Y-m-d H:i:s', filemtime($path)) : 'Unknown'
            ];
        }
        
        return response()->json($fileDetails);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}