<?php
// database/migrations/xxxx_xx_xx_add_spr_order_to_workers.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->integer('spr_order')->default(0)->after('is_active');
            
            // Добавляем индекс для ускорения сортировки
            $table->index('spr_order');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn('spr_order');
        });
    }
};