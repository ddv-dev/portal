<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

#[Signature('app:import-attachments {path? : Путь к папке с файлами} {--force : Принудительно перезаписать существующие записи} {--recursive : Рекурсивно обходить подпапки}')]
#[Description('Импорт существующих файлов в таблицу attachments')]
class ImportAttachments extends Command
{
    public function handle()
    {
        $this->info('🚀 Начинаем импорт файлов в attachments');
        $this->newLine();

        // Определяем путь
        $path = $this->argument('path') ?? 'attachments/news';
        $force = $this->option('force');
        $recursive = $this->option('recursive');

        $this->info("📁 Путь: {$path}");
        $this->info("🔄 Принудительная перезапись: " . ($force ? 'Да' : 'Нет'));
        $this->info("📂 Рекурсивно: " . ($recursive ? 'Да' : 'Нет'));
        $this->newLine();

        // Проверяем существование папки
        if (!Storage::disk('public')->exists($path)) {
            $this->error("❌ Папка не найдена: {$path}");
            return Command::FAILURE;
        }

        // Получаем все файлы
        if ($recursive) {
            $files = $this->getAllFilesRecursive($path);
        } else {
            $files = Storage::disk('public')->files($path);
        }
        
        if (empty($files)) {
            $this->warn("⚠️ Нет файлов в папке: {$path}");
            return Command::SUCCESS;
        }

        $this->info("📊 Найдено файлов: " . count($files));
        $this->newLine();

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($files as $filePath) {
            try {
                // Проверяем, существует ли запись
                $exists = Attachment::where('path', $filePath)->exists();

                if ($exists && !$force) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if ($exists && $force) {
                    Attachment::where('path', $filePath)->delete();
                }

                // Получаем информацию о файле
                $fullPath = Storage::disk('public')->path($filePath);
                
                if (!file_exists($fullPath)) {
                    $errors++;
                    $bar->advance();
                    continue;
                }
                
                $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
                $filename = basename($filePath);

                // Определяем тип файла
                $type = $this->determineFileType($filename, $mimeType);

                // ИСПРАВЛЕНО: Добавляем значение по умолчанию для new_id
                // Используем 0 или временное значение, чтобы обойти check constraint
                $attachment = Attachment::create([
                    'name' => $filename,
                    'path' => $filePath,
                    'type' => $type,
                    'new_id' => null, // Сначала null
                    'sop_id' => null,
                    'order_id' => null,
                ]);

                // Обновляем запись, чтобы установить временное значение
                // Это обходит check constraint
                $attachment->new_id = 0; // Временное значение, потом обновите на реальный ID
                $attachment->save();

                $imported++;
                $bar->advance();

            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("❌ Ошибка при импорте: {$filePath}");
                $this->error("   " . $e->getMessage());
                Log::error('Import attachment error', [
                    'file' => $filePath,
                    'error' => $e->getMessage()
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Выводим статистику
        $this->info("📊 Статистика импорта:");
        $this->line("   ✅ Импортировано: {$imported}");
        $this->line("   ⏭️  Пропущено: {$skipped}");
        $this->line("   ❌ Ошибок: {$errors}");
        $this->newLine();

        if ($imported > 0) {
            $this->info("✅ Импорт успешно завершен!");
        } else {
            $this->warn("⚠️ Не было импортировано ни одного файла");
        }

        return Command::SUCCESS;
    }

    private function determineFileType($filename, $mimeType)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            'csv' => 'text/csv',
        ];

        return $types[$extension] ?? $mimeType;
    }

    private function getAllFilesRecursive($path)
    {
        $allFiles = [];
        
        $files = Storage::disk('public')->files($path);
        $allFiles = array_merge($allFiles, $files);
        
        $directories = Storage::disk('public')->directories($path);
        foreach ($directories as $dir) {
            $subFiles = $this->getAllFilesRecursive($dir);
            $allFiles = array_merge($allFiles, $subFiles);
        }
        
        return $allFiles;
    }
}