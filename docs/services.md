# Сервисы

## ActiveDirectoryService

`app/Services/ActiveDirectoryService.php`

| Метод | Назначение |
|---|---|
| `getUserInfo(string $username): ?array` | LDAP-запрос по sAMAccountName |

Возвращает: `fio`, `department`, `email`, `title`.
Кэш: 10 ч (в middleware).

Использует `ldap_connect`, `ldap_bind`, `ldap_search` с экранированием логина.

---

## Redmine\RedmineUserService

`app/Services/Redmine/RedmineUserService.php`

| Метод | Назначение |
|---|---|
| `findByLogin(string $login): ?RedmineUser` | Поиск по логину (LOWER) |
| `findIdByLogin(string $login): ?int` | Только ID |
| `forget(string $login): void` | Сброс кэша |

---

## Redmine\RedmineProjectService

`app/Services/Redmine/RedmineProjectService.php`

| Метод | Назначение |
|---|---|
| `getAvailableForUser(RedmineUser $user): Collection` | Доступные проекты |
| `forget(int $userId): void` | Сброс кэша |

Фильтр: `status=1`, модуль `issue_tracking`, публичные ИЛИ member.

---

## Redmine\RedmineIssueService

`app/Services/Redmine/RedmineIssueService.php`

| Метод | Назначение |
|---|---|
| `getUserIssues(int $userId, int $perPage = 20)` | Issues пользователя |
| `getHistory(int $issueId): Collection` | Journal без private_notes |
| `createIssue(RedmineUser $author, array $data): ?RedmineIssue` | Создание issue |
| `addComment(int $issueId, RedmineUser $user, string $notes): ?RedmineJournal` | Комментарий |
| `getFormDictionaries(): array` | trackers, statuses, priorities |
| `getTrackersForProject(int $projectId): Collection` | Трекеры проекта |
| `getUserIssuesInProject(int $userId, int $projectId, string $filter = 'open')` | Issues в проекте |

Запись — только в `DB::connection('redmine')->transaction`.

---

## Redmine\RedmineCustomFieldService

`app/Services/Redmine/RedmineCustomFieldService.php`

| Метод | Назначение |
|---|---|
| `forProjectAndTracker(int $projectId, int $trackerId): Collection` | Поля для формы |
| `valuesForIssue(int $issueId): Collection` | Значения issue |
| `saveForIssue(RedmineIssue $issue, array $values): void` | Сохранение значений |

Запись: `custom_values.save()`, `custom_values.delete()`.

---

## Redmine\RedmineAttachmentService

`app/Services/Redmine/RedmineAttachmentService.php`

| Метод | Назначение |
|---|---|
| `upload(UploadedFile $file): ?array` | Загрузка одного файла |
| `uploadMany(array $files): array` | Загрузка нескольких |
| `attachToIssue(int $issueId, array $uploads, ?string $notes = null): bool` | Привязка к issue |

Работает через HTTP API Redmine (`POST /uploads.json`).