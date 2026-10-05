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