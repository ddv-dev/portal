<?php

namespace App\Services\Redmine;

use App\Models\Redmine\RedmineUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedmineUserService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $cacheTtl = 36000;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.redmine.url'), '/');
        $this->apiKey  = (string) config('services.redmine.api_key');
    }

    /**
     * Найти пользователя Redmine по логину.
     * Сначала смотрим в локальной БД (кэш), затем — в API.
     */
    public function findByLogin(string $login): ?RedmineUser
    {
        $login    = $this->normalizeLogin($login);
        $cacheKey = "redmine_user_id_{$login}";

        $userId = Cache::remember($cacheKey, $this->cacheTtl, function () use ($login) {
            // 1) локальная БД
            $id = RedmineUser::whereRaw('LOWER(login) = ?', [$login])->value('id');
            if ($id !== null) {
                Log::info('Redmine user found (local)', ['login' => $login, 'id' => $id]);
                return (int) $id;
            }

            // 2) API Redmine
            $apiId = $this->findIdInRedmine($login);
            if ($apiId !== null) {
                Log::info('Redmine user found (API)', ['login' => $login, 'id' => $apiId]);
                return $apiId;
            }

            Log::info('Redmine user not found', ['login' => $login]);
            return null;
        });

        if ($userId === null) {
            return null;
        }

        return RedmineUser::find($userId);
    }

    /**
     * Найти id пользователя в Redmine через API.
     * GET /users.json?name=login
     */
    public function findIdInRedmine(string $login): ?int
    {
        $login = $this->normalizeLogin($login);

        try {
            $response = Http::withHeaders([
                'X-Redmine-API-Key' => $this->apiKey,
                'Content-Type'      => 'application/json',
            ])->get("{$this->baseUrl}/users.json", [
                'name'  => $login,
                'limit' => 100,
            ]);

            if (!$response->successful()) {
                Log::error('Redmine findIdInRedmine failed', [
                    'login'  => $login,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $users = $response->json('users') ?? [];
            foreach ($users as $u) {
                if (strtolower($u['login'] ?? '') === $login) {
                    return (int) $u['id'];
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Redmine findIdInRedmine exception', [
                'login' => $login,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Найти или создать пользователя в Redmine из данных AD.
     *
     * @param string      $login  логин из AD (d.demekhin)
     * @param string|null $fio    ФИО ("Фамилия Имя Отчество")
     * @param string|null $email  email из AD (может быть пусто)
     * @param string|null $domain домен для синтетического email (DD.COM)
     */
    public function findOrCreateByLoginAd(
        string $login,
        ?string $fio = null,
        ?string $email = null,
        ?string $domain = null
    ): ?RedmineUser {
        $login = $this->normalizeLogin($login);

        // 1) уже есть?
        $existing = $this->findByLogin($login);
        if ($existing !== null) {
            return $existing;
        }

        // 2) создаём в Redmine
        $email = $this->normalizeEmail($email, $login, $domain);
        $name  = $this->splitFio($fio, $login);

        $created = $this->createInRedmine(
            $login,
            $name['lastname'],
            $name['firstname'],
            $email
        );

        if ($created === null) {
            return null;
        }

        // 3) сбрасываем кэш и пробуем перечитать
        $this->forget($login);

        // локальная БД могла ещё не синхронизироваться — пробуем найти
        $user = $this->findByLogin($login);
        if ($user !== null) {
            return $user;
        }

        // 4) если локальной записи нет — создаём заглушку-модель,
        //    чтобы middleware получил id
        return $this->makeLocalStub($created, $login, $name, $email);
    }

    /**
     * Создать пользователя в Redmine через API.
     * POST /users.json
     *
     * @return int|null id созданного пользователя
     */
    public function createInRedmine(
        string $login,
        string $lastname,
        string $firstname,
        string $mail
    ): ?int {
        $payload = [
            'user' => [
                'login'             => $login,
                'lastname'          => $lastname,
                'firstname'         => $firstname,
                'mail'              => $mail,
                'mail_notification' => 'only_my_events',
            ],
        ];

        // Если в Redmine настроен LDAP — привязываем к источнику,
        // чтобы пароль управлялся AD, а не Redmine.
        $authSourceId = config('services.redmine.auth_source_id');
        if ($authSourceId) {
            $payload['user']['auth_source_id'] = (int) $authSourceId;
        } else {
            // иначе — пусть Redmine сгенерирует пароль
            $payload['user']['generate_password'] = true;
        }

        try {
            $response = Http::withHeaders([
                'X-Redmine-API-Key' => $this->apiKey,
                'Content-Type'      => 'application/json',
            ])->post("{$this->baseUrl}/users.json", $payload);

            if ($response->status() === 201) {
                $id = $response->json('user.id');
                Log::info('Redmine user created via API', [
                    'login' => $login,
                    'id'    => $id,
                    'mail'  => $mail,
                ]);
                return $id ? (int) $id : null;
            }

            Log::error('Redmine createInRedmine failed', [
                'login'  => $login,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Redmine createInRedmine exception', [
                'login' => $login,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Найти id пользователя по логину (только id, с кэшем).
     */
    public function findIdByLogin(string $login): ?int
    {
        $login    = $this->normalizeLogin($login);
        $cacheKey = "redmine_user_id_{$login}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($login) {
            $id = RedmineUser::whereRaw('LOWER(login) = ?', [$login])->value('id');
            if ($id !== null) {
                return (int) $id;
            }
            return $this->findIdInRedmine($login);
        });
    }

    /**
     * Сбросить кэш по логину.
     */
    public function forget(string $login): void
    {
        $login = $this->normalizeLogin($login);
        Cache::forget("redmine_user_id_{$login}");
        Cache::forget("redmine_user_{$login}");
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function normalizeLogin(string $login): string
    {
        return strtolower(trim($login));
    }

    /**
     * Если email пустой/невалидный — генерируем "{login}@{domain}".
     */
    private function normalizeEmail(?string $email, string $login, ?string $domain): string
    {
        $email = is_string($email) ? trim($email) : '';

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        $domain = $domain
            ?: config('services.ad.email_domain')
            ?: config('services.redmine.email_domain')
            ?: parse_url((string) config('app.url'), PHP_URL_HOST)
            ?: 'local';

        return "{$login}@" . strtoupper((string) $domain);
    }

    /**
     * Разбор ФИО из AD.
     *
     * "Демехин Даниил Валентинович" →
     *   lastname  = "Демехин"
     *   firstname = "Даниил Валентинович"
     *
     * @return array{lastname: string, firstname: string}
     */
    private function splitFio(?string $fio, string $login): array
    {
        $fio = trim((string) $fio);

        if ($fio === '') {
            return ['lastname' => '', 'firstname' => $login];
        }

        $parts = preg_split('/\s+/u', $fio, -1, PREG_SPLIT_NO_EMPTY);

        if ($parts === false || count($parts) === 0) {
            return ['lastname' => '', 'firstname' => $login];
        }

        if (count($parts) === 1) {
            return ['lastname' => $parts[0], 'firstname' => $parts[0]];
        }

        $lastname  = array_shift($parts);
        $firstname = implode(' ', $parts);

        return [
            'lastname'  => $lastname,
            'firstname' => $firstname,
        ];
    }

    /**
     * Локальная заглушка, если после API-создания запись ещё не появилась
     * в локальной БД. Нужна, чтобы middleware получил id.
     */
    private function makeLocalStub(
        int $id,
        string $login,
        array $name,
        string $email
    ): RedmineUser {
        $user = new RedmineUser();
        $user->id        = $id;
        $user->login     = $login;
        $user->lastname  = $name['lastname'];
        $user->firstname = $name['firstname'];
        $user->mail      = $email;
        $user->exists    = true;

        return $user;
    }
}