<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedZip extends Model
{
    protected $table = 'shared_zips';

    protected $fillable = [
        'token',
        'zip_name',
        'file_paths',
        'file_names',
        'email',
        'expires_at',
        'downloaded_at',
        'download_count',
        'is_prebuilt'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'downloaded_at' => 'datetime',
        'download_count' => 'integer',
        'is_prebuilt' => 'boolean'
    ];

    public function isExpired()
    {
        return $this->expires_at < now();
    }

    public function isValid()
    {
        return !$this->isExpired();
    }
}