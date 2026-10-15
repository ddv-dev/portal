<?php


// database/migrations/xxxx_add_redmine_user_id_to_workers.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->unsignedBigInteger('redmine_user_id')
                ->nullable()
                ->unique()
                ->after('login_ad');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn('redmine_user_id');
        });
    }
};