// database/migrations/xxxx_add_corpus_dept_id_to_dept_workers.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dept_workers', function (Blueprint $table) {
            $table->foreignId('corpus_dept_id')
                  ->nullable()
                  ->constrained('corpus_depts')
                  ->onDelete('set null');
            
            // Если хотите, можно сделать уникальным для сотрудника
            // $table->unique(['worker_id', 'corpus_dept_id']);
        });
    }

    public function down()
    {
        Schema::table('dept_workers', function (Blueprint $table) {
            $table->dropForeign(['corpus_dept_id']);
            $table->dropColumn('corpus_dept_id');
        });
    }
};