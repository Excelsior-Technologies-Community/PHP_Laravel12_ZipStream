<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('zip_downloads', function (Blueprint $table) {
            if (!Schema::hasColumn('zip_downloads', 'is_password_protected')) {
                $table->boolean('is_password_protected')->default(false);
            }
            if (!Schema::hasColumn('zip_downloads', 'user_ip')) {
                $table->string('user_ip')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('zip_downloads', function (Blueprint $table) {
            $table->dropColumn(['is_password_protected', 'user_ip']);
        });
    }
};