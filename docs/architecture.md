# Архитектура проекта

## Назначение
Корпоративный портал: новости, справочники, телефонная книга, SOP, интеграция с Redmine.

## Общая схема

Apache (Kerberos/NTLM)
  └─ WindowsAuthentication (middleware)
       ├─ ActiveDirectoryService → LDAP (ФИО, отдел, email, должность)
       └─ RedmineUserService → MySQL redmine (id, login, ФИО)
            ↓
       session: windows_user, redmine_user_id, ...
            ↓
       SsoGuard → SsoUser (DTO)
            ↓
       Контроллеры → Сервисы → Модели

## Слои приложения

| Слой | Расположение | Ответственность |
|---|---|---|
| HTTP | app/Http/Controllers | Тонкие, отдают view/JSON |
| Filament | app/Filament/Resources | Админка CRUD, 15 ресурсов |
| Auth | app/Auth/SsoGuard.php | Guard над сессией |
| Middleware | app/Http/Middleware | WindowsAuthentication |
| Services | app/Services | Интеграции: AD, Redmine |
| Models | app/Models | Eloquent (основная БД) |
| Redmine Models | app/Models/Redmine | Внешняя БД Redmine |
| Observers | app/Observers | NewsObserver |

## Внешние системы

| Система | Назначение | Доступ |
|---|---|---|
| Active Directory | Аутентификация, атрибуты | LDAP |
| Redmine | Задачи, проекты, вложения | MySQL + HTTP API |
| Apache | Источник REMOTE_USER | Kerberos/NTLM |
| MySQL (основная) | Данные портала | PDO |
| MySQL (redmine) | Данные Redmine | PDO, connection=redmine |

## Ключевые правила

1. **Redmine-модели** — только через `App\Services\Redmine\*`
2. **Запись в Redmine** — только в транзакции `DB::connection('redmine')->transaction`
3. **Новые вложения** в `AttachmentController` — добавлять в whitelist моделей
4. **Бизнес-логику портала** выносить в `app/Services/*`, не в контроллеры
5. **Прямой доступ к PHP-FPM** в обход Apache запрещён

## Что где лежит
app/
├── Auth/SsoGuard.php — Guard над сессией
├── Console/Commands/ — artisan-команды
├── Filament/Resources/ — 15 CRUD-ресурсов админки
├── Http/
│ ├── Controllers/ — 10 контроллеров портала
│ ├── Controllers/Redmine/ — 4 контроллера Redmine
│ └── Middleware/ — WindowsAuthentication
├── Models/ — 20 моделей портала
├── Models/Redmine/ — 16 моделей Redmine
├── Observers/NewsObserver.php — observer новостей
├── Providers/ — сервис-провайдеры
└── Services/
├── ActiveDirectoryService.php — LDAP
└── Redmine/ — 5 сервисов Redmine


## Известные проблемы

- Дубль `CorpusDeptResource` и `CorpusResource` (роуты ведут на один контроллер)
- Нет Policies/Gates — авторизация вне Laravel
- `SsoGuard::hasUser()` всегда `false` до вызова `user()` — логическая ошибка
- `ActiveDirectoryService` открывает LDAP-соединение на каждый вызов
- `RedmineIssueService` — большой класс, кандидат на разбиение