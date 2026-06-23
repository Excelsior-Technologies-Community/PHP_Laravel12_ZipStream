<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shared_zips', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('zip_name');
            $table->longText('file_paths');
            $table->longText('file_names')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('downloaded_at')->nullable();
            $table->integer('download_count')->default(0);
            $table->boolean('is_prebuilt')->default(false);
            $table->timestamps();

            $table->index('token');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shared_zips');
    }
};