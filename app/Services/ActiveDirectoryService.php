<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ActiveDirectoryService
{
    /**
     * Возвращает атрибуты пользователя AD по логину (sAMAccountName).
     * Возвращает массив ['fio' => ..., 'department' => ..., 'email' => ..., 'title' => ...]
     * или null, если пользователь не найден.
     */
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
}