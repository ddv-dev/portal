
---

## `docs/redmine.md`

```markdown
# Redmine: интеграция

## Два канала связи

### 1. MySQL (прямые запросы)
- Соединение `redmine` в `config/database.php`
- Переменные: `DB_MYSQL_HOST`, `DB_MYSQL_DATABASE`, `DB_MYSQL_USERNAME`, `DB_MYSQL_PASSWORD`
- Базовая модель: `App\Models\Redmine\RedmineModel`
  - `$connection = 'redmine'`
  - `$timestamps = false`

### 2. HTTP API
- Только для загрузки вложений: `RedmineAttachmentService`
- Эндпоинт: `{baseUrl}/uploads.json`
- Конфиг: `config/services.php` → `redmine.url`, `redmine.api_key`

## Таблицы

| Модель | Таблица | Read | Write |
|---|---|---|---|
| RedmineIssue | issues | ✅ | ✅ |
| RedmineJournal | journals | ✅ | ✅ |
| RedmineJournalDetail | journal_details | ✅ | ❌ |
| RedmineUser | users | ✅ | ❌ |
| RedmineProject | projects | ✅ | ❌ |
| RedmineMember | members | ✅ | ❌ |
| RedmineMemberRole | member_roles | ✅ | ❌ |
| RedmineTracker | trackers | ✅ | ❌ |
| RedmineIssueStatus | issue_statuses | ✅ | ❌ |
| RedmineEnumeration | enumerations | ✅ | ❌ |
| RedmineCustomField | custom_fields | ✅ | ❌ |
| RedmineCustomValue | custom_values | ✅ | ✅ |
| RedmineAttachment | attachments | ✅ | ❌ |
| RedmineEnabledModule | enabled_modules | ✅ | ❌ |
| RedmineRole | roles | ✅ | ❌ |

## Сервисы

| Сервис | Ответственность | Пишет |
|---|---|---|
| RedmineIssueService | Issues, journals, трекеры | ✅ |
| RedmineProjectService | Проекты пользователя | ❌ |
| RedmineUserService | Поиск по логину | ❌ |
| RedmineCustomFieldService | Кастомные поля issues | ✅ |
| RedmineAttachmentService | Загрузка через API | ❌ |

## Логика createIssue

1. Определяет `status_id`:
   - если трекер `is_in_chlog` — первый `is_closed=0` статус
   - иначе — статус по умолчанию трекера
2. Определяет `priority_id` из `enumerations` (IssuePriority, is_default)
3. В транзакции `redmine` создаёт `RedmineIssue::create`
4. Возвращает `?RedmineIssue`

## Логика addComment

1. В транзакции создаёт `RedmineJournal::create`
2. Обновляет `RedmineIssue.updated_on`
3. Возвращает `?RedmineJournal`

## Логика getAvailableForUser (ProjectService)

Проекты, где:
- `projects.status = 1`
- есть модуль `issue_tracking`
- `is_public = 1` ИЛИ пользователь — member

## КРИТИЧНО

- Redmine обновляется независимо. Схема БД может поехать.
- Фиксировать **версию Redmine** при деплое портала.
- Никаких прямых `RedmineIssue::create` вне сервисов.
- Все записи — только в транзакции.