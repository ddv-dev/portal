<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Сначала добавляем колонки
        Schema::table('corpus_depts', function (Blueprint $table) {
            if (!Schema::hasColumn('corpus_depts', 'floor')) {
                $table->integer('floor')->nullable()->after('dept_id')->comment('Этаж');
            }
            
            if (!Schema::hasColumn('corpus_depts', 'sector')) {
                $table->string('sector', 100)->nullable()->after('floor')->comment('Сектор/ближайший лифт');
            }
        });

        // Теперь создаем индексы ТОЛЬКО если колонки существуют
        if (Schema::hasColumn('corpus_depts', 'floor')) {
            $this->createIndexIfNotExists('corpus_depts', 'floor');
        }
        
        if (Schema::hasColumn('corpus_depts', 'sector')) {
            $this->createIndexIfNotExists('corpus_depts', 'sector');
        }
    }

    public function down(): void
    {
        // Удаляем индексы если они есть
        $this->dropIndexIfExists('corpus_depts', 'floor');
        $this->dropIndexIfExists('corpus_depts', 'sector');

        // Удаляем колонки
        Schema::table('corpus_depts', function (Blueprint $table) {
            if (Schema::hasColumn('corpus_depts', 'floor')) {
                $table->dropColumn('floor');
            }
            
            if (Schema::hasColumn('corpus_depts', 'sector')) {
                $table->dropColumn('sector');
            }
        });
    }

    private function createIndexIfNotExists(string $table, string $column): void
    {
        try {
            $indexName = $table . '_' . $column . '_index';
            
            // Проверяем существует ли колонка
            if (!Schema::hasColumn($table, $column)) {
                echo "⚠ Колонка {$column} не существует в таблице {$table}\n";
                return;
            }
            
            // Проверяем существует ли индекс
            $indices = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
            
            if (empty($indices)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
                echo "✓ Индекс создан: {$table}.{$column}\n";
            } else {
                echo "✗ Индекс уже существует: {$table}.{$column}\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Ошибка при создании индекса {$table}.{$column}: " . $e->getMessage() . "\n";
        }
    }

    private function dropIndexIfExists(string $table, string $column): void
    {
        try {
            $indexName = $table . '_' . $column . '_index';
            
            // Проверяем существует ли индекс
            $indices = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
            
            if (!empty($indices)) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
                echo "✓ Индекс удален: {$indexName}\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Ошибка при удалении индекса: " . $e->getMessage() . "\n";
        }
    }
};