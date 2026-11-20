
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_journal', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('user_name')->nullable();

            $table->unsignedBigInteger('duty_worker_id')->nullable();

            $table->string('action', 20);

            $table->date('duty_date')->nullable();

            $table->unsignedBigInteger('duty_role_id')->nullable();

            $table->string('role_name')->nullable();

            $table->unsignedInteger('number')->nullable();

            $table->jsonb('old_values')->nullable();

            $table->jsonb('new_values')->nullable();

            $table->timestampTz('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('duty_date');
            $table->index('user_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_journal');
    }
};
