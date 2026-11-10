<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duty_journal', function (Blueprint $table) {
            // 1. Удаляем старый внешний ключ (имя сгенерировано Laravel: duty_journal_updated_id_foreign)
            $table->dropForeign(['updated_id']);
        });

        Schema::table('duty_journal', function (Blueprint $table) {
            // 2. Добавляем новый FK на users
            $table->foreign('updated_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('duty_journal', function (Blueprint $table) {
            $table->dropForeign(['updated_id']);
        });

        Schema::table('duty_journal', function (Blueprint $table) {
            $table->foreign('updated_id')
                ->references('id')
                ->on('workers')
                ->nullOnDelete();
        });
    }
};