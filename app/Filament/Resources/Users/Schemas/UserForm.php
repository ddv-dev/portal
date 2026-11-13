<?php
// app/Filament/Resources/Users/Schemas/UserForm.php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                CheckboxList::make('permissions')
                    ->label('Права доступа')
                    ->relationship('permissions', 'permission')
                    ->options(
                        Permission::query()
                            ->with('dutyRole')
                            ->get()
                            ->mapWithKeys(fn (Permission $p) => [
                                $p->id => $p->permission
                                    . ($p->dutyRole ? ' — ' . $p->dutyRole->name : '')
                                    . ($p->description ? ' (' . $p->description . ')' : ''),
                            ])
                            ->toArray()
                    )
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable(),
            ]);
    }
}