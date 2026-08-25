<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Получаем список всех таблиц
        $tables = $this->getTables();
        
        // === 1. Связь department_workers.worker_id → users.id ===
        if (in_array('department_workers', $tables) && in_array('users', $tables)) {
            if ($this->columnExists('department_workers', 'worker_id')) {
                $this->addForeignKeyIfNotExists(
                    'department_workers',
                    'worker_id',
                    'users',
                    'id',
                    'department_workers_worker_id_foreign'
                );
            }
        }
        
        // === 2. Связь order_storage.worker_id → users.id ===
        if (in_array('order_storage', $tables) && in_array('users', $tables)) {
            if ($this->columnExists('order_storage', 'worker_id')) {
                $this->addForeignKeyIfNotExists(
                    'order_storage',
                    'worker_id',
                    'users',
                    'id',
                    'order_storage_worker_id_foreign'
                );
            }
        }
        
        // === 3. Связь order_storage.user_id → users.id ===
        if (in_array('order_storage', $tables) && in_array('users', $tables)) {
            if ($this->columnExists('order_storage', 'user_id')) {
                $this->addForeignKeyIfNotExists(
                    'order_storage',
                    'user_id',
                    'users',
                    'id',
                    'order_storage_user_id_foreign'
                );
            }
        }
        
        // === 4. Связь order_storage.phone_id → phones.id (если есть таблица phones) ===
        if (in_array('order_storage', $tables) && in_array('phones', $tables)) {
            if ($this->columnExists('order_storage', 'phone_id')) {
                $this->addForeignKeyIfNotExists(
                    'order_storage',
                    'phone_id',
                    'phones',
                    'id',
                    'order_storage_phone_id_foreign'
                );
            }
        }
        
        // === 5. Связь failed_jobs.batch_id → job_batches.id ===
        if (in_array('failed_jobs', $tables) && in_array('job_batches', $tables)) {
            // Проверяем тип поля batch_id
            $batchIdType = $this->getColumnType('failed_jobs', 'batch_id');
            
            // Если batch_id UUID, а job_batches.id VARCHAR - меняем тип
            if ($batchIdType === 'uuid') {
                // Сначала удаляем внешний ключ если есть
                $this->dropForeignKeyIfExists('failed_jobs', 'failed_jobs_batch_id_foreign');
                // Меняем тип
                DB::statement('ALTER TABLE failed_jobs ALTER COLUMN batch_id TYPE VARCHAR(255)');
            }
            
            // Добавляем поле batch_id если его нет
            if (!$this->columnExists('failed_jobs', 'batch_id')) {
                DB::statement('ALTER TABLE failed_jobs ADD COLUMN batch_id VARCHAR(255) NULL');
            }
            
            // Добавляем внешний ключ
            $this->addForeignKeyIfNotExists(
                'failed_jobs',
                'batch_id',
                'job_batches',
                'id',
                'failed_jobs_batch_id_foreign'
            );
        }
    }
    
    public function down(): void
    {
        // Удаляем все внешние ключи
        $this->dropForeignKeyIfExists('department_workers', 'department_workers_worker_id_foreign');
        $this->dropForeignKeyIfExists('order_storage', 'order_storage_worker_id_foreign');
        $this->dropForeignKeyIfExists('order_storage', 'order_storage_user_id_foreign');
        $this->dropForeignKeyIfExists('order_storage', 'order_storage_phone_id_foreign');
        $this->dropForeignKeyIfExists('failed_jobs', 'failed_jobs_batch_id_foreign');
    }
    
    // === Вспомогательные методы ===
    
    private function getTables(): array
    {
        $tables = DB::select("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_type = 'BASE TABLE'
        ");
        return array_map(fn($t) => $t->table_name, $tables);
    }
    
    private function columnExists(string $table, string $column): bool
    {
        $result = DB::select("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = ? AND column_name = ?
        ", [$table, $column]);
        
        return count($result) > 0;
    }
    
    private function getColumnType(string $table, string $column): ?string
    {
        $result = DB::select("
            SELECT data_type 
            FROM information_schema.columns 
            WHERE table_name = ? AND column_name = ?
        ", [$table, $column]);
        
        return $result[0]->data_type ?? null;
    }
    
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $result = DB::select("
            SELECT conname 
            FROM pg_constraint 
            WHERE conrelid = ?::regclass 
            AND conname = ? 
            AND contype = 'f'
        ", [$table, $constraintName]);
        
        return count($result) > 0;
    }
    
    private function addForeignKeyIfNotExists(
        string $table,
        string $column,
        string $referencesTable,
        string $referencesColumn,
        string $constraintName
    ): void {
        if (!$this->foreignKeyExists($table, $constraintName)) {
            DB::statement("
                ALTER TABLE {$table} 
                ADD CONSTRAINT {$constraintName} 
                FOREIGN KEY ({$column}) 
                REFERENCES {$referencesTable}({$referencesColumn}) 
                ON DELETE SET NULL 
                ON UPDATE CASCADE
            ");
        }
    }
    
    private function dropForeignKeyIfExists(string $table, string $constraintName): void
    {
        if ($this->foreignKeyExists($table, $constraintName)) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraintName}");
        }
    }
};