<?php

namespace App\Console\Commands;

use App\Models\Worker;
use Illuminate\Console\Command;

class SyncAdLogins extends Command
{
    protected $signature = 'workers:sync-ad 
                            {file : Путь к CSV/JSON с AD-пользователями}
                            {--dry-run : Показать, что будет обновлено, без записи в БД}';

    protected $description = 'Проставляет login_ad воркерам по ФИО из AD';

    public function handle(): int
    {
        $file = $this->argument('file');
        $dryRun = (bool) $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("Файл не найден: {$file}");
            return self::FAILURE;
        }

        try {
            $adUsers = $this->loadAdUsers($file);
        } catch (\Throwable $e) {
            $this->error("Ошибка чтения: " . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Загружено AD-пользователей: ' . count($adUsers));

        $byName = [];
        $emptyNames = 0;

        foreach ($adUsers as $u) {
            $key = $this->normalize($u['displayName']);
            if ($key === '') { $emptyNames++; continue; }

            if (isset($byName[$key])) {
                $this->warn("Дубликат по ФИО: {$u['displayName']} (логины: {$byName[$key]} / {$u['sAMAccountName']})");
                continue;
            }
            $byName[$key] = $u['sAMAccountName'];
        }

        if ($emptyNames > 0) {
            $this->warn("Пропущено записей с пустым ФИО: {$emptyNames}");
        }

        $updated = 0;
        $notFound = [];

        Worker::query()
            ->where(function ($q) {
                $q->whereNull('login_ad')->orWhere('login_ad', '');
            })
            ->chunkById(500, function ($workers) use ($byName, $dryRun, &$updated, &$notFound) {
                foreach ($workers as $worker) {
                    $key = $this->normalize($worker->name_full);

                    if (isset($byName[$key])) {
                        if ($dryRun) {
                            $this->line("  [dry] {$worker->name_full} => {$byName[$key]}");
                        } else {
                            $worker->login_ad = $byName[$key];
                            $worker->save();
                        }
                        $updated++;
                    } else {
                        $notFound[] = $worker->name_full;
                    }
                }
            });

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Обновлено записей: {$updated}");

        if ($notFound) {
            $this->warn('Не найдено совпадений: ' . count($notFound));
            foreach (array_slice($notFound, 0, 20) as $n) {
                $this->line("  - {$n}");
            }
            if (count($notFound) > 20) {
                $this->line('  ... и ещё ' . (count($notFound) - 20));
            }
        }

        return self::SUCCESS;
    }

private function loadAdUsers(string $file): array
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    // ---------- JSON ----------
    if ($ext === 'json') {
        $raw = @file_get_contents($file);
        if ($raw === false) {
            throw new \RuntimeException("Не могу прочитать JSON-файл: {$file}");
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new \RuntimeException("Некорректный JSON в файле: {$file}");
        }

        return array_map(fn($u) => [
            'displayName'    => $u['displayName']    ?? '',
            'sAMAccountName' => $u['sAMAccountName'] ?? '',
        ], $data);
    }

    // ---------- CSV ----------
    $fh = @fopen($file, 'r');
    if ($fh === false) {
        throw new \RuntimeException("Не могу открыть файл: {$file}");
    }

    $firstLine = fgets($fh);
    if ($firstLine === false) {
        fclose($fh);
        return [];   // ← пустой файл — возвращаем пустой массив, не падаем
    }
    rewind($fh);

    // Определяем разделитель
    $delimiter = ';';
    if (substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
        $delimiter = ',';
    } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ';')) {
        $delimiter = "\t";
    }

    $header = fgetcsv($fh, 0, $delimiter);
    if ($header === false || $header === null) {
        fclose($fh);
        return [];  
    }

    // Нормализуем заголовки: BOM, регистр, пробелы
    $header = array_map(function ($h) {
        $h = preg_replace('/^\xEF\xBB\xBF/', '', (string) $h); // BOM
        return strtolower(trim($h));
    }, $header);

    $nameIdx  = array_search('displayname', $header, true);
    $loginIdx = array_search('samaccountname', $header, true);

    if ($nameIdx === false || $loginIdx === false) {
        fclose($fh);
        throw new \RuntimeException(
            "В CSV нет колонок displayName/sAMAccountName. Найдено: " . implode(', ', $header)
        );
    }

    $result = [];
    while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
        $name  = trim((string) ($row[$nameIdx]  ?? ''));
        $login = trim((string) ($row[$loginIdx] ?? ''));

        if ($name === '' || $login === '') {
            continue;
        }

        $result[] = [
            'displayName'    => $name,
            'sAMAccountName' => $login,
        ];
    }
    fclose($fh);

    return $result;   
}

    private function normalize(?string $name): string
    {
        $name = (string) $name;
        $name = str_replace(['ё', 'Ё'], ['е', 'Е'], $name);
        $name = mb_strtolower($name, 'UTF-8');
        $name = preg_replace('/\s+/u', ' ', $name);
        return trim($name);
    }
}