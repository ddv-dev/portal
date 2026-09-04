<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('info', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('created_at');
            $table->integer('order')->default(0);
            $table->foreignId('type_id')->constrained('info_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('info');
    }
};
