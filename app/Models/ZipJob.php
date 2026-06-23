<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipJob extends Model
{
    protected $fillable = [
        'zip_name',
        'status',
        'file_paths',
        'download_url',
        'password'
    ];

    protected $casts = [
        'file_paths' => 'array'
    ];
}