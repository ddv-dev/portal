<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Удаляем FK и колонку duty_id из duty_worker
        Schema::table('duty_worker', function (Blueprint $table) {
            $table->dropForeign(['duty_id']);
            $table->dropColumn('duty_id');
        });

        // 2. Теперь можно дропнуть duty
        Schema::dropIfExists('duty');
    }

    public function down(): void
    {
        // Восстанавливаем структуру (по желанию — обычно down для drop не нужен)
        Schema::create('duty', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->timestamp('updated')->nullable();
            $table->timestamps();
        });

        Schema::table('duty_worker', function (Blueprint $table) {
            $table->foreignId('duty_id')->nullable()->constrained('duty');
        });
    }
};