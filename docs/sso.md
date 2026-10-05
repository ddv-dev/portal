# SSO / Аутентификация

## Как работает

1. **Apache** (mod_auth_kerb / mod_auth_ntlm) аутентифицирует пользователя
2. Apache передаёт логин в `$_SERVER['REMOTE_USER']` (или GSSAPI_NAME, PHP_AUTH_USER)
3. **Middleware `WindowsAuthentication`**:
   - Если в сессии уже есть `windows_user` — пропускает
   - Нормализует логин (убирает `M01\`, `@M01.DZM`, `M01-`)
   - Запрашивает AD через `ActiveDirectoryService` (кэш 10 ч)
   - Ищет пользователя в Redmine через `RedmineUserService`
   - Кладёт всё в сессию
4. **`SsoGuard`** читает сессию и создаёт DTO `SsoUser`

## Ключи сессии

| Ключ | Источник |
|---|---|
| windows_user | REMOTE_USER |
| windows_fio | AD: displayname / cn |
| windows_department | AD: department |
| windows_email | AD: mail |
| windows_title | AD: title |
| redmine_user_id | Redmine: users.id |
| redmine_login | Redmine: users.login |
| redmine_fio | Redmine: users.full_name |

## Файлы

- `app/Http/Middleware/WindowsAuthentication.php`
- `app/Auth/SsoGuard.php`
- `app/Services/ActiveDirectoryService.php`
- `app/Models/SsoUser.php`
- `app/Http/Controllers/AuthController.php`

## Конфиг

`config/services.php`:
- `ldap.host`
- `ldap.base_dn`
- `ldap.username`
- `ldap.password`

## КРИТИЧНО

- Middleware **доверяет** `REMOTE_USER`. Прямой доступ к PHP-FPM в обход Apache запрещён.
- `SsoGuard::validate()` всегда `false` — вход по логину/паролю через Laravel невозможен.
- Кэш AD 10 ч — при смене атрибутов в AD изменения видны не сразу.
- Если LDAP недоступен — `getUserInfo` вернёт `null`, портал пустит с логином вместо ФИО.

## Логика нормализации логина

```php
$username = str_replace(['M01\\', '@M01.DZM', 'M01-'], '', $username);
$username = preg_replace('/^.*\\\\/', '', $username);
$username = preg_replace('/[^a-zA-Z0-9._-]/', '', trim($username));

```

Убирает домен, оставляет только sAMAccountName.

API методы

- AuthController::dashboard() — view dashboard

- AuthController::status() — JSON: authenticated, user, session_id, ip

- AuthController::logout() — очистка сессии

