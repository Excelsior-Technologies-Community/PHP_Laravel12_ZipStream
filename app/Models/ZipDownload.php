<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipDownload extends Model
{
    protected $table = 'zip_downloads';
    
    protected $fillable = [
        'zip_name',
        'total_files',
        'is_password_protected'
    ];
    
    protected $casts = [
        'is_password_protected' => 'boolean'
    ];
}