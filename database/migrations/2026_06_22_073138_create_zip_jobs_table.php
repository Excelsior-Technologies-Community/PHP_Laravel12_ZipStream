<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zip_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('zip_name');
            $table->string('status')->default('pending');
            $table->longText('file_paths');
            $table->string('password')->nullable();
            $table->string('download_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zip_jobs');
    }
};