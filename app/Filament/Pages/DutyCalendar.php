<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Illuminate\Support\Facades\Log;

class DutyCalendar extends Page
{
    protected string $view = 'filament.pages.duty-calendar';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Дежурства (календарь)';
    protected static ?string $title = 'Календарь дежурств';

    public static function canAccess(): bool
    {
        Log::info('DutyCalendar::canAccess called', [
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'has_duty_manage_all' => \Illuminate\Support\Facades\Auth::user()?->hasPermission('duty_manage_all'),
            'manageable_roles' => \Illuminate\Support\Facades\Auth::user()?->manageableDutyRoleIds(),
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user instanceof \App\Models\User) {
            return false;
        }
        return $user->hasPermission('duty_manage_all')
            || ! empty($user->manageableDutyRoleIds());
    }
}
