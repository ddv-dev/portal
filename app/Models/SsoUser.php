<?php

namespace App\Models;

class SsoUser
{
    public function __construct(
        public string $username,
        public string $fio,
        public ?string $department = null,
        public ?string $email = null,
        public ?string $title = null,
    ) {}
}