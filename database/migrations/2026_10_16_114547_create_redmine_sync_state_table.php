<?php

// database/migrations/2026_10_16_xxxxxx_create_redmine_sync_state_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redmine_sync_state', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->string('value', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redmine_sync_state');
    }
};