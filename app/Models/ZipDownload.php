<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipDownload extends Model
{
    protected $fillable = [
        'zip_name',
        'total_files'
    ];
}