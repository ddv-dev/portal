<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL уже поддерживает отрицательные числа в integer
        // Поэтому просто обновляем комментарий для ясности
        Schema::table('corpus_depts', function (Blueprint $table) {
            if (Schema::hasColumn('corpus_depts', 'floor')) {
                // Меняем комментарий к колонке
                DB::statement("COMMENT ON COLUMN corpus_depts.floor IS 'Этаж (отрицательные значения - подземные этажи)'");
            }
        });
    }

    public function down(): void
    {
        Schema::table('corpus_depts', function (Blueprint $table) {
            if (Schema::hasColumn('corpus_depts', 'floor')) {
                // Возвращаем старый комментарий
                DB::statement("COMMENT ON COLUMN corpus_depts.floor IS 'Этаж'");
            }
        });
    }
};