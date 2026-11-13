<?php

namespace App\Console\Commands;

use App\Models\Phone;
use Illuminate\Console\Command;

class PruneOrphanPhones extends Command
{
    protected $signature = 'phones:prune-orphans {--dry-run : Только показать, сколько будет удалено}';

    protected $description = 'Удаляет телефоны, не привязанные ни к одному сотруднику';

    public function handle(): int
    {
        $query = Phone::query()
            ->whereDoesntHave('workers');
            // Если появятся другие ссылки на phone — добавить сюда:
            // ->whereDoesntHave('dutyJournal')
            // ->whereDoesntHave('someOtherRelation');

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("Найдено сирот: {$count}");
            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('Сирот нет.');
            return self::SUCCESS;
        }

        // Удаляем пачками, чтобы не ловить длинную транзакцию на больших объёмах
        $deleted = 0;
        $query->chunkById(500, function ($phones) use (&$deleted) {
            foreach ($phones as $phone) {
                // Повторная проверка на случай гонки: пока шёл chunk, кто-то привязал
                if ($phone->workers()->exists()) {
                    continue;
                }
                $phone->delete();
                $deleted++;
            }
        });

        $this->info("Удалено сирот: {$deleted}");

        return self::SUCCESS;
    }
}