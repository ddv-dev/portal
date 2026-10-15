<?php

// database/migrations/xxxx_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('worker_id')->index();            // получатель (workers.id)
            $table->unsignedBigInteger('actor_worker_id')->nullable()->index(); // инициатор (workers.id)

            $table->string('type', 64)->index();
            // issue_status_changed | issue_comment | news_created

            $table->string('notifiable_type', 64)->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->index(['notifiable_type', 'notifiable_id'], 'notifiable_idx');

            $table->string('title', 255)->nullable();
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('url', 512)->nullable();

            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->string('dedup_key', 160)->nullable();
            $table->unique(['worker_id', 'dedup_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};