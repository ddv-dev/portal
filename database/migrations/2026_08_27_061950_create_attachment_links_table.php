<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attachment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attachment_id')->constrained('attachments')->onDelete('cascade');
            $table->string('linkable_type'); // 'news', 'sop', 'order'
            $table->unsignedBigInteger('linkable_id');
            $table->timestamps();
            
            // Уникальный индекс для предотвращения дублей
            $table->unique(['attachment_id', 'linkable_type', 'linkable_id']);
            
            // Индексы для быстрого поиска
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attachment_links');
    }
};