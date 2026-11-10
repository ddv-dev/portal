<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Справочник ролей
        Schema::create('duty_role', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('duty_role')->insert([
            ['name' => 'Администратор'],
            ['name' => 'Врач'],
            ['name' => 'Анестезиолог'],
            ['name' => 'Операционная мед.сестра'],
        ]);

        // 2. Дежурства
        Schema::create('duty', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->timestamp('updated')->nullable();
            $table->timestamps();
        });

        // 3. Работники на дежурстве
        Schema::create('duty_worker', function (Blueprint $table) {
            $table->id();

            $table->foreignId('duty_id')
                ->constrained('duty')
                ->cascadeOnDelete();

            $table->string('fullname');
            $table->string('number')->nullable();

            $table->foreignId('duty_role_id')
                ->constrained('duty_role')
                ->restrictOnDelete();

            $table->timestamps();
        });

        // 4. Журнал изменений
        Schema::create('duty_journal', function (Blueprint $table) {
            $table->id();

            $table->foreignId('duty_worker_id')
                ->constrained('duty_worker')
                ->cascadeOnDelete();

            $table->timestamp('updated_at')->nullable();

            // Кто изменил (пользователь/воркер системы)
            $table->foreignId('updated_id')
                ->nullable()
                ->constrained('workers') // поменяйте на нужную таблицу, напр. 'workers'
                ->nullOnDelete();

            $table->string('col_update');
            $table->text('value_old')->nullable();
            $table->text('value_new')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_journal');
        Schema::dropIfExists('duty_worker');
        Schema::dropIfExists('duty');
        Schema::dropIfExists('duty_role');
    }
};