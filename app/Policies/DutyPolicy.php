<?php

namespace App\Policies;

use App\Models\Duty;
use App\Models\User;

class DutyPolicy
{
    /**
     * Может ли пользователь вообще видеть раздел «Дежурства».
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('duty_manage_all')
            || !empty($user->manageableDutyRoleIds());
    }

    public function view(User $user, Duty $duty): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        // Создавать дни дежурств может только полный администратор
        return $user->hasPermission('duty_manage_all');
    }

    public function update(User $user, Duty $duty): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Duty $duty): bool
    {
        return $user->hasPermission('duty_manage_all');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('duty_manage_all');
    }
}