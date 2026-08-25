<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = $this->getTables();
        
        // === 1. department_workers.worker_id → users.id ===
        if ($this->tableExists('department_workers') && $this->tableExists('users')) {
            if ($this->columnExists('department_workers', 'worker_id')) {
                // Проверяем, есть ли уже внешний ключ
                if (!$this->foreignKeyExists('department_workers', 'department_workers_worker_id_foreign')) {
                    // Проверяем тип данных
                    $workerIdType = $this->getColumnType('department_workers', 'worker_id');
                    $userIdType = $this->getColumnType('users', 'id');
                    
                    if ($workerIdType !== $userIdType) {
                        // Меняем тип если нужно
                        DB::statement('ALTER TABLE department_workers ALTER COLUMN worker_id TYPE BIGINT USING worker_id::BIGINT');
                    }
                    
                    DB::statement('
                        ALTER TABLE department_workers 
                        ADD CONSTRAINT department_workers_worker_id_foreign 
                        FOREIGN KEY (worker_id) REFERENCES users(id) 
                        ON DELETE SET NULL
                    ');
                    
                    echo "✅ Добавлена связь department_workers.worker_id → users.id\n";
                } else {
                    echo "⚠️ Связь department_workers.worker_id → users.id уже существует\n";
                }
            } else {
                echo "❌ В таблице department_workers нет поля worker_id\n";
            }
        } else {
            echo "❌ Таблицы department_workers или users не существуют\n";
        }
        
        // === 2. order_storage.worker_id → users.id ===
        if ($this->tableExists('order_storage') && $this->tableExists('users')) {
            if ($this->columnExists('order_storage', 'worker_id')) {
                if (!$this->foreignKeyExists('order_storage', 'order_storage_worker_id_foreign')) {
                    // Проверяем тип данных
                    $workerIdType = $this->getColumnType('order_storage', 'worker_id');
                    $userIdType = $this->getColumnType('users', 'id');
                    
                    if ($workerIdType !== $userIdType) {
                        DB::statement('ALTER TABLE order_storage ALTER COLUMN worker_id TYPE BIGINT USING worker_id::BIGINT');
                    }
                    
                    DB::statement('
                        ALTER TABLE order_storage 
                        ADD CONSTRAINT order_storage_worker_id_foreign 
                        FOREIGN KEY (worker_id) REFERENCES users(id) 
                        ON DELETE SET NULL
                    ');
                    
                    echo "✅ Добавлена связь order_storage.worker_id → users.id\n";
                } else {
                    echo "⚠️ Связь order_storage.worker_id → users.id уже существует\n";
                }
            } else {
                echo "❌ В таблице order_storage нет поля worker_id\n";
            }
        }
        
        // === 3. order_storage.user_id → users.id ===
        if ($this->tableExists('order_storage') && $this->tableExists('users')) {
            if ($this->columnExists('order_storage', 'user_id')) {
                if (!$this->foreignKeyExists('order_storage', 'order_storage_user_id_foreign')) {
                    // Проверяем тип данных
                    $userIdType = $this->getColumnType('order_storage', 'user_id');
                    $usersIdType = $this->getColumnType('users', 'id');
                    
                    if ($userIdType !== $usersIdType) {
                        DB::statement('ALTER TABLE order_storage ALTER COLUMN user_id TYPE BIGINT USING user_id::BIGINT');
                    }
                    
                    DB::statement('
                        ALTER TABLE order_storage 
                        ADD CONSTRAINT order_storage_user_id_foreign 
                        FOREIGN KEY (user_id) REFERENCES users(id) 
                        ON DELETE SET NULL
                    ');
                    
                    echo "✅ Добавлена связь order_storage.user_id → users.id\n";
                } else {
                    echo "⚠️ Связь order_storage.user_id → users.id уже существует\n";
                }
            } else {
                echo "❌ В таблице order_storage нет поля user_id\n";
            }
        }
        
        // === 4. Индексы для производительности ===
        $this->addIndexIfNotExists('department_workers', 'worker_id');
        $this->addIndexIfNotExists('order_storage', 'worker_id');
        $this->addIndexIfNotExists('order_storage', 'user_id');
    }
    
    public function down(): void
    {
        $this->dropForeignKeyIfExists('department_workers', 'department_workers_worker_id_foreign');
        $this->dropForeignKeyIfExists('order_storage', 'order_storage_worker_id_foreign');
        $this->dropForeignKeyIfExists('order_storage', 'order_storage_user_id_foreign');
        
        $this->dropIndexIfExists('department_workers', 'department_workers_worker_id_index');
        $this->dropIndexIfExists('order_storage', 'order_storage_worker_id_index');
        $this->dropIndexIfExists('order_storage', 'order_storage_user_id_index');
    }
    
    // === Вспомогательные методы ===
    
    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }
    
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
        return Schema::hasColumn($table, $column);
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
    
    private function dropForeignKeyIfExists(string $table, string $constraintName): void
    {
        if ($this->foreignKeyExists($table, $constraintName)) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraintName}");
        }
    }
    
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("
            SELECT indexname 
            FROM pg_indexes 
            WHERE schemaname = 'public' 
            AND tablename = ? 
            AND indexname = ?
        ", [$table, $indexName]);
        
        return count($result) > 0;
    }
    
    private function addIndexIfNotExists(string $table, string $column): void
    {
        if ($this->columnExists($table, $column)) {
            $indexName = $table . '_' . $column . '_index';
            if (!$this->indexExists($table, $indexName)) {
                DB::statement("CREATE INDEX {$indexName} ON {$table}({$column})");
                echo "✅ Добавлен индекс {$indexName}\n";
            }
        }
    }
    
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            DB::statement("DROP INDEX {$indexName}");
        }
    }
};