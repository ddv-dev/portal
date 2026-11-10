<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('duty_worker', function (Blueprint $table) {
            $table->date('duty_date')->nullable()->after('duty_id')->index();
        });

        // Переносим даты из duty в duty_worker
        DB::statement('
        UPDATE duty_worker
        SET duty_date = duty.date
        FROM duty
        WHERE duty_worker.duty_id = duty.id
    ');
    }

    public function down(): void
    {
        Schema::table('duty_worker', function (Blueprint $table) {
            $table->dropColumn('duty_date');
        });
    }
};
