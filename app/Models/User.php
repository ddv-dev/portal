<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_permissions',
            'user_id',
            'permission_id'
        );
    }

    /**
     * Коды прав: ['duty_manage_doc', 'duty_manage_anesthesiologist']
     */
    public function permissionCodes(): array
    {
        return $this->relationLoaded('permissions')
            ? $this->permissions->pluck('permission')->all()
            : $this->permissions()->pluck('permission')->all();
    }

    public function hasPermission(string $code): bool
    {
        return in_array($code, $this->permissionCodes(), true);
    }


    public function manageableDutyRoleIds(): array
    {
        if ($this->hasPermission('duty_manage_all')) {
            return \App\Models\DutyRole::pluck('id')->all();
        }

        return $this->permissions()
            ->whereNotNull('duty_role_id')
            ->pluck('duty_role_id')
            ->all();
    }
    public function canManageDutyRole(int $roleId): bool
    {
        return in_array($roleId, $this->manageableDutyRoleIds(), true);
    }
}
