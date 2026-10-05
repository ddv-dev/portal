# Скан логики проекта
Дата: 2026-10-05 12:51

## 1. Публичные методы сервисов
```
=== app/Services/ActiveDirectoryService.php ===
10:    public function getUserInfo(string $username): ?array

=== app/Services/Redmine/RedmineAttachmentService.php ===
14:    public function __construct()
23:    public function upload(UploadedFile $file): ?array
64:    public function uploadMany(array $files): array
78:    public function attachToIssue(int $issueId, array $uploads, ?string $notes = null): bool

=== app/Services/Redmine/RedmineCustomFieldService.php ===
16:public function forProjectAndTracker(int $projectId, int $trackerId): Collection
34:    public function valuesForIssue(int $issueId): Collection
47:    public function saveForIssue(RedmineIssue $issue, array $values): void

=== app/Services/Redmine/RedmineIssueService.php ===
16:    public function getUserIssues(int $userId, int $perPage = 20): LengthAwarePaginator
25:    public function getHistory(int $issueId): Collection
41:    public function createIssue(RedmineUser $author, array $data): ?RedmineIssue
124:    public function addComment(int $issueId, RedmineUser $user, string $notes): ?RedmineJournal
164:    public function getFormDictionaries(): array
176:    public function getTrackersForProject(int $projectId): Collection
200:    public function getUserIssuesInProject(int $userId, int $projectId, string $filter = 'open')

=== app/Services/Redmine/RedmineProjectService.php ===
23:    public function getAvailableForUser(RedmineUser $user): Collection
71:    public function forget(int $userId): void

=== app/Services/Redmine/RedmineUserService.php ===
13:    public function findByLogin(string $login): ?RedmineUser
41:    public function findIdByLogin(string $login): ?int
59:    public function forget(string $login): void

```

## 2. Зависимости сервисов (use + конструктор)
```
=== app/Services/ActiveDirectoryService.php ===
5:use Illuminate\Support\Facades\Log;

=== app/Services/Redmine/RedmineAttachmentService.php ===
5:use Illuminate\Http\UploadedFile;
6:use Illuminate\Support\Facades\Http;
7:use Illuminate\Support\Facades\Log;
14:    public function __construct()

=== app/Services/Redmine/RedmineCustomFieldService.php ===
6:use App\Models\Redmine\RedmineCustomField;
7:use App\Models\Redmine\RedmineCustomValue;
8:use App\Models\Redmine\RedmineIssue;
9:use Illuminate\Support\Collection;

=== app/Services/Redmine/RedmineIssueService.php ===
5:use App\Models\Redmine\RedmineIssue;
6:use App\Models\Redmine\RedmineJournal;
7:use App\Models\Redmine\RedmineUser;
8:use Illuminate\Contracts\Pagination\LengthAwarePaginator;
9:use Illuminate\Support\Facades\DB;
10:use App\Models\Redmine\RedmineTracker;
11:use Illuminate\Support\Collection;
12:use Illuminate\Support\Facades\Log;

=== app/Services/Redmine/RedmineProjectService.php ===
6:use App\Models\Redmine\RedmineProject;
7:use App\Models\Redmine\RedmineUser;
8:use Illuminate\Support\Collection;
9:use Illuminate\Support\Facades\Cache;
10:use Illuminate\Support\Facades\Log;

=== app/Services/Redmine/RedmineUserService.php ===
5:use App\Models\Redmine\RedmineUser;
6:use Illuminate\Support\Facades\Cache;
7:use Illuminate\Support\Facades\Log;

```

## 3. Вызовы моделей и БД в сервисах
```
app/Services/Redmine/RedmineProjectService.php:29:            $ids = RedmineProject::query()
app/Services/Redmine/RedmineProjectService.php:54:        return RedmineProject::query()
app/Services/Redmine/RedmineUserService.php:38:        return RedmineUser::find($userId);
app/Services/Redmine/RedmineIssueService.php:27:        return RedmineJournal::where('journalized_id', $issueId)
app/Services/Redmine/RedmineIssueService.php:52:        $statusId = (int) (\App\Models\Redmine\RedmineTracker::where('id', $data['tracker_id'])
app/Services/Redmine/RedmineIssueService.php:57:            $statusId = (int) (\App\Models\Redmine\RedmineIssueStatus::where('is_closed', 0)
app/Services/Redmine/RedmineIssueService.php:77:            return DB::connection('redmine')->transaction(function () use ($author, $data, $statusId, $priorityId) {
app/Services/Redmine/RedmineIssueService.php:80:                $issue = RedmineIssue::create([
app/Services/Redmine/RedmineIssueService.php:131:            return DB::connection('redmine')->transaction(function () use ($issueId, $user, $notes) {
app/Services/Redmine/RedmineIssueService.php:134:                $journal = RedmineJournal::create([
app/Services/Redmine/RedmineIssueService.php:144:                RedmineIssue::where('id', $issueId)->update(['updated_on' => $now]);
app/Services/Redmine/RedmineIssueService.php:180:        $hasRestrictions = DB::connection('redmine')
app/Services/Redmine/RedmineIssueService.php:186:            return RedmineTracker::query()
app/Services/Redmine/RedmineIssueService.php:191:        return RedmineTracker::query()
app/Services/Redmine/RedmineIssueService.php:202:        $query = RedmineIssue::query()
app/Services/Redmine/RedmineCustomFieldService.php:18:    return RedmineCustomField::query()
app/Services/Redmine/RedmineCustomFieldService.php:36:        return RedmineCustomValue::query()
app/Services/Redmine/RedmineCustomFieldService.php:55:            $existing = RedmineCustomValue::query()
app/Services/Redmine/RedmineCustomFieldService.php:62:                $existing?->delete();
app/Services/Redmine/RedmineCustomFieldService.php:72:                $existing->save();
app/Services/Redmine/RedmineCustomFieldService.php:80:                $cv->save();
```

## 4. SSO / Auth
```
--- SsoGuard ---
<?php

namespace App\Auth;

use App\Models\SsoUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class SsoGuard implements Guard
{
    protected ?SsoUser $user = null;

    public function __construct(protected Request $request) {}

    public function check(): bool
    {
        return session()->has('windows_user');
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?SsoUser
    {
        if ($this->user) {
            return $this->user;
        }

        if (! $this->check()) {
            return null;
        }

        $this->user = new SsoUser(
            username:   session('windows_user'),
            fio:        session('windows_fio') ?? session('windows_user'),
            department: session('windows_department'),
            email:      session('windows_email'),
            position:      session('windows_title'),
        );

        return $this->user;
    }

    public function id(): ?string
    {
        return session('windows_user');
    }

    public function validate(array $credentials = []): bool
    {
        return false; 
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser($user): static
    {
        $this->user = $user;
        return $this;
    }
}
--- WindowsAuthentication ---
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ActiveDirectoryService;
use App\Services\Redmine\RedmineUserService;

class WindowsAuthentication
{
    public function __construct(
        private ActiveDirectoryService $ad,
        private RedmineUserService $redmineUsers,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Уже авторизованы в сессии — пропускаем
        if (session()->has('windows_user')) {
            return $next($request);
        }

        $username = $_SERVER['REMOTE_USER']
            ?? $_SERVER['GSSAPI_NAME']
            ?? $_SERVER['PHP_AUTH_USER']
            ?? null;

        if (empty($username)) {
            Log::warning('SSO: REMOTE_USER пуст — Apache не передал пользователя', [
                'server_keys' => array_filter(
                    array_keys($_SERVER),
                    fn($k) => str_contains($k, 'AUTH') || str_contains($k, 'USER') || str_contains($k, 'GSS')
                ),
            ]);

            return response('Unauthorized', 401);
        }

        $username = str_replace(['M01\\', '@M01.DZM', 'M01-'], '', $username);
        $username = preg_replace('/^.*\\\\/', '', $username);
        $username = preg_replace('/[^a-zA-Z0-9._-]/', '', trim($username));

        // 1. AD
        $info = Cache::remember("ad_info_{$username}", 36000, function () use ($username) {
            return $this->ad->getUserInfo($username);
        });

        $fio = $info['fio'] ?? $username;

        // 2. Redmine (MySQL) — ищем по логину
        $redmineUser = $this->redmineUsers->findByLogin($username);

        // 3. Кладём в сессию
        session([
            'windows_user'         => $username,
            'windows_fio'          => $fio,
            'windows_department'   => $info['department'] ?? null,
            'windows_email'        => $info['email']      ?? null,
            'windows_title'        => $info['title']      ?? null,

            'redmine_user_id'      => $redmineUser?->id,
            'redmine_login'        => $redmineUser?->login,
            'redmine_fio'          => $redmineUser?->full_name,
        ]);

        $request->attributes->set('windows_user', $username);
        $request->merge(['windows_user' => $username]);

        Log::info('SSO auth success', [
            'user'        => $username,
            'windows_fio' => $fio,
            'redmine_id'  => $redmineUser?->id,
        ]);

        return $next($request);
    }
}
--- AuthController ---
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = session('windows_user', 'Гость');
        
        return view('dashboard', [
            'user' => $user,
            'authenticated' => session()->has('windows_user')
        ]);
    }
    
    public function status(Request $request)
    {
        return response()->json([
            'authenticated' => session()->has('windows_user'),
            'user' => session('windows_user'),
            'session_id' => session()->getId(),
            'ip' => $request->ip()
        ]);
    }
    
    public function logout(Request $request)
    {
        session()->forget('windows_user');
        session()->invalidate();
        
        return redirect('/')->with('message', 'Вы вышли из системы');
    }
}
--- ActiveDirectoryService ---
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ActiveDirectoryService
{

    public function getUserInfo(string $username): ?array
    {
        $host     = config('services.ldap.host');
        $baseDn   = config('services.ldap.base_dn');
        $bindUser = config('services.ldap.username');
        $bindPass = config('services.ldap.password');

        $conn = ldap_connect($host);
        if (!$conn) {
            Log::error('LDAP: cannot connect', ['host' => $host]);
            return null;
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($conn, $bindUser, $bindPass)) {
            Log::error('LDAP: bind failed', ['error' => ldap_error($conn)]);
            ldap_unbind($conn);
            return null;
        }

        // Экранируем логин, чтобы не сломать фильтр
        $safeUser = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
        $filter   = "(sAMAccountName={$safeUser})";

        $attributes = ['displayname', 'cn', 'department', 'mail', 'title', 'samaccountname'];

        $search = @ldap_search($conn, $baseDn, $filter, $attributes);
        if (!$search) {
            Log::warning('LDAP: search failed', ['error' => ldap_error($conn), 'user' => $username]);
            ldap_unbind($conn);
            return null;
        }

        $entries = ldap_get_entries($conn, $search);
        ldap_unbind($conn);

        if (($entries['count'] ?? 0) === 0) {
            Log::info('LDAP: user not found', ['user' => $username]);
            return null;
        }

        $e = $entries[0];

        return [
            'fio'        => $e['displayname'][0] ?? $e['cn'][0] ?? null,
            'department' => $e['department'][0]  ?? null,
            'email'      => $e['mail'][0]        ?? null,
            'title'      => $e['title'][0]       ?? null,
        ];
    }
}```

## 5. Redmine: соединения и таблицы
```
app/Models/Redmine/RedmineJournal.php:8:    protected $table = 'journals';
app/Models/Redmine/RedmineModel.php:9:    protected $connection = 'redmine';
app/Models/Redmine/RedmineModel.php:11:    public $timestamps = false;
app/Models/Redmine/RedmineTracker.php:8:    protected $table = 'trackers';
app/Models/Redmine/RedmineJournalDetail.php:8:    protected $table = 'journal_details';
app/Models/Redmine/RedmineRole.php:7:    protected $table = 'roles';
app/Models/Redmine/RedmineMember.php:9:    protected $table = 'members';
app/Models/Redmine/RedmineAttachment.php:8:    protected $table = 'attachments';
app/Models/Redmine/RedmineUser.php:8:    protected $table = 'users';
app/Models/Redmine/RedmineEnumeration.php:8:    protected $table = 'enumerations';
app/Models/Redmine/RedmineCustomValue.php:6:    protected $table = 'custom_values';
app/Models/Redmine/RedmineEnabledModule.php:7:    protected $table = 'enabled_modules';
app/Models/Redmine/RedmineIssueStatus.php:8:    protected $table = 'issue_statuses';
app/Models/Redmine/RedmineCustomField.php:7:    protected $table = 'custom_fields';
app/Models/Redmine/RedmineIssue.php:8:    protected $table = 'issues';
app/Models/Redmine/RedmineProject.php:8:    protected $table = 'projects';
app/Models/Redmine/RedmineMemberRole.php:7:    protected $table = 'member_roles';
```

## 6. Redmine: конфиг БД
```
37:        'redmine' => [
38-            'driver' => 'mysql',
39-            'host' => env('DB_MYSQL_HOST', '127.0.0.1'),
40-            'port' => env('DB_MYSQL_PORT', '3306'),
41-            'database' => env('DB_MYSQL_DATABASE', 'laravel'),
42-            'username' => env('DB_MYSQL_USERNAME', 'root'),
43-            'password' => env('DB_MYSQL_PASSWORD', ''),
44-            'unix_socket' => env('DB_SOCKET', ''),
45-            'charset' => env('DB_MYSQL_CHARSET', 'utf8mb4'),
46-            'collation' => env('DB_MYSQL_COLLATION', 'utf8mb4_unicode_ci'),
47-            'prefix' => '',
48-            'prefix_indexes' => true,
49-            'strict' => true,
50-            'engine' => null,
51-            'options' => extension_loaded('pdo_mysql') ? array_filter([
52-                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
53-            ]) : [],
54-        ],
55-
56-
57-
```

## 7. Redmine-сервисы: что дергают
```
=== app/Services/Redmine/RedmineAttachmentService.php ===
26:            $response = Http::withHeaders([
32:            )->post("{$this->baseUrl}/uploads.json");
85:        $response = Http::withHeaders([

=== app/Services/Redmine/RedmineCustomFieldService.php ===
18:    return RedmineCustomField::query()
20:        ->where('custom_fields.visible', 1)
25:                ->where('cft.tracker_id', $trackerId);
28:        ->get();
36:        return RedmineCustomValue::query()
37:            ->where('customized_type', 'Issue')
38:            ->where('customized_id', $issueId)
40:            ->where('value', '!=', '')
42:            ->get()
55:            $existing = RedmineCustomValue::query()
56:                ->where('customized_type', 'Issue')
57:                ->where('customized_id', $issue->id)
58:                ->where('custom_field_id', $fieldId)

=== app/Services/Redmine/RedmineIssueService.php ===
18:        return RedmineIssue::forUser($userId)
27:        return RedmineJournal::where('journalized_id', $issueId)
28:            ->where('journalized_type', 'Issue')
29:            ->where(function ($q) {
30:                $q->where('private_notes', 0)
34:            ->where('notes', '!=', '')
37:            ->get();
52:        $statusId = (int) (\App\Models\Redmine\RedmineTracker::where('id', $data['tracker_id'])
57:            $statusId = (int) (\App\Models\Redmine\RedmineIssueStatus::where('is_closed', 0)
63:        $priorityId = (int) (\App\Models\Redmine\RedmineEnumeration::ofType('IssuePriority')
65:            ->where('is_default', 1)
70:            $priorityId = (int) (\App\Models\Redmine\RedmineEnumeration::ofType('IssuePriority')
80:                $issue = RedmineIssue::create([
134:                $journal = RedmineJournal::create([
144:                RedmineIssue::where('id', $issueId)->update(['updated_on' => $now]);
167:            'trackers' => \App\Models\Redmine\RedmineTracker::orderBy('position')->get(['id', 'name']),
168:            'statuses' => \App\Models\Redmine\RedmineIssueStatus::orderBy('position')->get(['id', 'name', 'is_closed']),
169:            'priorities' => \App\Models\Redmine\RedmineEnumeration::ofType('IssuePriority')
172:                ->get(['id', 'name', 'is_default']),
182:            ->where('project_id', $projectId)
186:            return RedmineTracker::query()
188:                ->get(['id', 'name']);
191:        return RedmineTracker::query()
195:                    ->where('project_id', $projectId);
198:            ->get(['id', 'name']);
202:        $query = RedmineIssue::query()
203:            ->where('project_id', $projectId)
204:            ->where('author_id', $userId)
219:        return $query->limit(200)->get();

=== app/Services/Redmine/RedmineProjectService.php ===
29:            $ids = RedmineProject::query()
30:                ->where('projects.status', 1)
31:                ->whereHas('enabledModules', fn($q) => $q->where('name', 'issue_tracking'))
32:                ->where(function ($q) use ($user) {
33:                    $q->where('projects.is_public', 1)
34:                        ->orWhereHas('members', fn($m) => $m->where('user_id', $user->id));
54:        return RedmineProject::query()
57:            ->get([

=== app/Services/Redmine/RedmineUserService.php ===
19:            $user = RedmineUser::whereRaw('LOWER(login) = ?', [$login])->first();
38:        return RedmineUser::find($userId);
47:            $id = RedmineUser::whereRaw('LOWER(login) = ?', [$login])->value('id');

```

## 8. Роуты → контроллеры → сервисы
```
app/Http/Controllers/AttachmentController.php:16:            'news' => \App\Models\News::class,
app/Http/Controllers/AttachmentController.php:17:            'info' => \App\Models\Info::class,
app/Http/Controllers/AttachmentController.php:18:            'sop' => \App\Models\Sop::class,
app/Http/Controllers/AttachmentController.php:19:            'order' => \App\Models\OrderStorage::class,
app/Http/Controllers/Redmine/ProjectController.php:7:use App\Services\Redmine\RedmineProjectService;
app/Http/Controllers/Redmine/ProjectController.php:8:use App\Services\Redmine\RedmineIssueService;
app/Http/Controllers/Redmine/ProjectController.php:9:use App\Services\Redmine\RedmineCustomFieldService;
app/Http/Controllers/Redmine/ProjectController.php:15:        protected RedmineProjectService     $projects,
app/Http/Controllers/Redmine/ProjectController.php:16:        protected RedmineIssueService       $issues,
app/Http/Controllers/Redmine/ProjectController.php:17:        protected RedmineCustomFieldService $customFields,
app/Http/Controllers/Redmine/IssueController.php:9:use App\Services\Redmine\RedmineIssueService;
app/Http/Controllers/Redmine/IssueController.php:11:use App\Services\Redmine\RedmineCustomFieldService;
app/Http/Controllers/Redmine/IssueController.php:12:use App\Services\Redmine\RedmineAttachmentService;
app/Http/Controllers/Redmine/IssueController.php:17:        protected RedmineIssueService $issues,
app/Http/Controllers/Redmine/IssueController.php:18:        protected RedmineCustomFieldService $customFields,
app/Http/Controllers/Redmine/IssueController.php:21:    public function store(Request $request,  RedmineAttachmentService $attachments)
app/Http/Controllers/Redmine/IssueController.php:75:    public function comment(Request $request, int $id, RedmineAttachmentService $attachments)
app/Http/Controllers/Redmine/CustomFieldController.php:6:use App\Services\Redmine\RedmineCustomFieldService;
app/Http/Controllers/Redmine/CustomFieldController.php:12:        protected RedmineCustomFieldService $fields,
app/Http/Controllers/Redmine/CustomFieldController.php:44:    public function trackers(Request $request, \App\Services\Redmine\RedmineIssueService $issues)
app/Http/Controllers/Redmine/ProjectController.php:7:use App\Services\Redmine\RedmineProjectService;
app/Http/Controllers/Redmine/ProjectController.php:8:use App\Services\Redmine\RedmineIssueService;
app/Http/Controllers/Redmine/ProjectController.php:9:use App\Services\Redmine\RedmineCustomFieldService;
app/Http/Controllers/Redmine/ProjectController.php:15:        protected RedmineProjectService     $projects,
app/Http/Controllers/Redmine/ProjectController.php:16:        protected RedmineIssueService       $issues,
app/Http/Controllers/Redmine/ProjectController.php:17:        protected RedmineCustomFieldService $customFields,
app/Http/Controllers/Redmine/IssueController.php:9:use App\Services\Redmine\RedmineIssueService;
app/Http/Controllers/Redmine/IssueController.php:11:use App\Services\Redmine\RedmineCustomFieldService;
app/Http/Controllers/Redmine/IssueController.php:12:use App\Services\Redmine\RedmineAttachmentService;
app/Http/Controllers/Redmine/IssueController.php:17:        protected RedmineIssueService $issues,
app/Http/Controllers/Redmine/IssueController.php:18:        protected RedmineCustomFieldService $customFields,
app/Http/Controllers/Redmine/IssueController.php:21:    public function store(Request $request,  RedmineAttachmentService $attachments)
app/Http/Controllers/Redmine/IssueController.php:75:    public function comment(Request $request, int $id, RedmineAttachmentService $attachments)
app/Http/Controllers/Redmine/CustomFieldController.php:6:use App\Services\Redmine\RedmineCustomFieldService;
app/Http/Controllers/Redmine/CustomFieldController.php:12:        protected RedmineCustomFieldService $fields,
app/Http/Controllers/Redmine/CustomFieldController.php:44:    public function trackers(Request $request, \App\Services\Redmine\RedmineIssueService $issues)
```
