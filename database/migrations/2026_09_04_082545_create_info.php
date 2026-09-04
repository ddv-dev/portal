<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('info_type', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order_menu')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('info_type');
    }
};