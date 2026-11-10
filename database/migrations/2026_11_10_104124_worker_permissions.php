<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Справочник прав
        Schema::create('permission', function (Blueprint $table) {
            $table->id();
            $table->string('permission')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('permission')->insert([
            ['permission' => 'duty_manage_all',              'description' => 'Управление дежурствами — все роли'],
            ['permission' => 'duty_manage_doc',              'description' => 'Управление дежурствами — врач'],
            ['permission' => 'duty_manage_admin',            'description' => 'Управление дежурствами — администратор'],
            ['permission' => 'duty_manage_oper_nurse',       'description' => 'Управление дежурствами — операционная мед.сестра'],
            ['permission' => 'duty_manage_anesthesiologist', 'description' => 'Управление дежурствами — анестезиолог'],
        ]);

        // 2. Связь user ↔ права
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('permission_id')
                ->constrained('permission')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('permission');
    }
};