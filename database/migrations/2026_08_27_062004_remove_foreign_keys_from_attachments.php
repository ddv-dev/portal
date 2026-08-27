<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attachments', function (Blueprint $table) {
            // Проверяем существование колонок и удаляем их
            $columns = ['new_id', 'sop_id', 'order_id'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('attachments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down()
    {
        Schema::table('attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('attachments', 'new_id')) {
                $table->unsignedBigInteger('new_id')->nullable();
            }
            if (!Schema::hasColumn('attachments', 'sop_id')) {
                $table->unsignedBigInteger('sop_id')->nullable();
            }
            if (!Schema::hasColumn('attachments', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable();
            }
        });
    }
};