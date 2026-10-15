<?php

namespace App\Notifications\Types;

final class NotificationType
{
    public const ISSUE_STATUS_CHANGED = 'issue_status_changed';
    public const ISSUE_COMMENT        = 'issue_comment';
    public const NEWS_CREATED         = 'news_created';

    /**
     * Все известные типы — удобно для валидации, фильтров, сидов.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::ISSUE_STATUS_CHANGED,
            self::ISSUE_COMMENT,
            self::NEWS_CREATED,
        ];
    }

    /**
     * Человекочитаемые подписи — пригодятся во вьюхах и в письмах.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ISSUE_STATUS_CHANGED => 'Смена статуса задачи',
            self::ISSUE_COMMENT        => 'Новый комментарий к задаче',
            self::NEWS_CREATED         => 'Новая новость',
        ];
    }

    public static function label(string $type): string
    {
        return self::labels()[$type] ?? $type;
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::all(), true);
    }
}