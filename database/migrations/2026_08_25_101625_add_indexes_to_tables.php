<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Проверяем и создаем индексы для workers
        $this->createIndexIfNotExists('workers', 'login_ad');
        $this->createIndexIfNotExists('workers', 'is_active');

        // Индексы для dept_workers
        $this->createIndexIfNotExists('dept_workers', 'worker_id');
        $this->createIndexIfNotExists('dept_workers', 'dept_id');
        
        // Составной индекс для быстрого поиска
        $this->createCompositeIndexIfNotExists('dept_workers', ['dept_id', 'worker_id'], 'dept_workers_dept_worker_idx');

        // Индексы для worker_phones
        $this->createIndexIfNotExists('worker_phones', 'worker_id');
        $this->createIndexIfNotExists('worker_phones', 'phone_id');

        // Индексы для corpus_depts
        $this->createIndexIfNotExists('corpus_depts', 'corpus_id');
        $this->createIndexIfNotExists('corpus_depts', 'dept_id');
        
        // Проверяем наличие колонок floor и sector перед созданием индексов
        if ($this->columnExists('corpus_depts', 'floor')) {
            $this->createIndexIfNotExists('corpus_depts', 'floor');
        }
        if ($this->columnExists('corpus_depts', 'sector')) {
            $this->createIndexIfNotExists('corpus_depts', 'sector');
        }

        // Индексы для attachments
        $this->createIndexIfNotExists('attachments', 'sop_id');
        $this->createIndexIfNotExists('attachments', 'order_id');
        $this->createIndexIfNotExists('attachments', 'new_id');
        $this->createIndexIfNotExists('attachments', 'type');

        // Индексы для sop
        $this->createIndexIfNotExists('sop', 'is_active');

        // Индексы для order_storage (ТОЛЬКО существующие колонки!)
        $this->createIndexIfNotExists('order_storage', 'is_active');
        // НЕ ДОБАВЛЯЙТЕ worker_id - его нет в таблице!

        // Индексы для new (новости)
        $this->createIndexIfNotExists('new', 'is_active');

        // Индексы для phone
        $this->createIndexIfNotExists('phone', 'phone_number');
        $this->createIndexIfNotExists('phone', 'is_city_number');

        // Индексы для building
        $this->createIndexIfNotExists('building', 'name');
    }

    public function down(): void
    {
        // Удаляем индексы
        $this->dropIndexIfExists('workers', 'login_ad');
        $this->dropIndexIfExists('workers', 'is_active');

        $this->dropIndexIfExists('dept_workers', 'worker_id');
        $this->dropIndexIfExists('dept_workers', 'dept_id');
        $this->dropIndexIfExists('dept_workers', 'dept_workers_dept_worker_idx');

        $this->dropIndexIfExists('worker_phones', 'worker_id');
        $this->dropIndexIfExists('worker_phones', 'phone_id');

        $this->dropIndexIfExists('corpus_depts', 'corpus_id');
        $this->dropIndexIfExists('corpus_depts', 'dept_id');
        $this->dropIndexIfExists('corpus_depts', 'floor');
        $this->dropIndexIfExists('corpus_depts', 'sector');

        $this->dropIndexIfExists('attachments', 'sop_id');
        $this->dropIndexIfExists('attachments', 'order_id');
        $this->dropIndexIfExists('attachments', 'new_id');
        $this->dropIndexIfExists('attachments', 'type');

        $this->dropIndexIfExists('sop', 'is_active');
        $this->dropIndexIfExists('order_storage', 'is_active');
        $this->dropIndexIfExists('new', 'is_active');

        $this->dropIndexIfExists('phone', 'phone_number');
        $this->dropIndexIfExists('phone', 'is_city_number');

        $this->dropIndexIfExists('building', 'name');
    }

    private function createIndexIfNotExists(string $table, string $column): void
    {
        try {
            $indexName = $table . '_' . $column . '_index';
            
            // Проверяем существует ли индекс
            $indices = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ?", [$table]);
            $indexExists = collect($indices)->contains('indexname', $indexName);
            
            // Проверяем существует ли колонка
            $columnExists = $this->columnExists($table, $column);
            
            if (!$indexExists && $columnExists) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
                echo "✓ Индекс создан: {$table}.{$column}\n";
            } else {
                echo "✗ Индекс пропущен: {$table}.{$column} (индекс существует или колонка отсутствует)\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Ошибка при создании индекса {$table}.{$column}: " . $e->getMessage() . "\n";
        }
    }

    private function createCompositeIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        try {
            // Проверяем существует ли индекс
            $indices = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ?", [$table]);
            $indexExists = collect($indices)->contains('indexname', $indexName);
            
            if (!$indexExists) {
                Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
                echo "✓ Составной индекс создан: {$table} (" . implode(',', $columns) . ")\n";
            } else {
                echo "✗ Составной индекс пропущен: {$table} ({$indexName}) - уже существует\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Ошибка при создании составного индекса {$table}: " . $e->getMessage() . "\n";
        }
    }

    private function dropIndexIfExists(string $table, string $column): void
    {
        try {
            $indexName = $table . '_' . $column . '_index';
            
            // Проверяем существует ли индекс
            $indices = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ?", [$table]);
            $indexExists = collect($indices)->contains('indexname', $indexName);
            
            if ($indexExists) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
                echo "✓ Индекс удален: {$indexName}\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Ошибка при удалении индекса: " . $e->getMessage() . "\n";
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $columns = Schema::getColumnListing($table);
            return in_array($column, $columns);
        } catch (\Exception $e) {
            return false;
        }
    }
};