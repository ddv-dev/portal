<?php

namespace App\Filament\Resources\DutyResource\RelationManagers;

use App\Models\DutyRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers';

    protected static ?string $title = 'Сотрудники на дежурстве';

    protected static function user(): ?\App\Models\User
    {
        /** @var \App\Models\User|null $user */
        return Auth::user();
    }

    protected static function manageableRoleIds(): array
    {
        return static::user()?->manageableDutyRoleIds() ?? [];
    }

    protected static function hasFullAccess(): bool
    {
        return (bool) static::user()?->hasPermission('duty_manage_all');
    }

    protected static function canManageAnything(): bool
    {
        return static::hasFullAccess() || !empty(static::manageableRoleIds());
    }

    public function form(Form $form): Form
    {
        $allowedIds = static::manageableRoleIds();
        $fullAccess = static::hasFullAccess();
        $defaultRole = count($allowedIds) === 1 ? $allowedIds[0] : null;

        return $form->schema([
            Forms\Components\TextInput::make('fullname')
                ->label('ФИО')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('number')
                ->label('Номер')
                ->tel()
                ->maxLength(50),

            Forms\Components\Select::make('duty_role_id')
                ->label('Роль')
                ->options(
                    DutyRole::whereIn('id', $allowedIds)
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->default($defaultRole)
                ->disabled(fn () => !$fullAccess && count($allowedIds) === 1)
                ->dehydrated()
                ->required()
                // Скрываем, если пользователю нечего выбирать
                ->visible(fn () => $fullAccess || !empty($allowedIds)),
        ]);
    }

    public function table(Table $table): Table
    {
        $allowedIds = static::manageableRoleIds();
        $fullAccess = static::hasFullAccess();
        $canManage = static::canManageAnything();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($allowedIds, $fullAccess) {
                if ($fullAccess) {
                    return $query;
                }
                if (empty($allowedIds)) {
                    return $query->whereRaw('1 = 0');
                }
                return $query->whereIn('duty_role_id', $allowedIds);
            })
            ->recordTitleAttribute('fullname')
            ->columns([
                Tables\Columns\TextColumn::make('fullname')->label('ФИО')->searchable(),
                Tables\Columns\TextColumn::make('number')->label('Номер'),
                Tables\Columns\TextColumn::make('role.name')->label('Роль')->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить сотрудника')
                    ->visible(fn () => $canManage)
                    ->mutateFormDataUsing(function (array $data) use ($allowedIds, $fullAccess) {
                        if (!$fullAccess) {
                            $roleId = (int) ($data['duty_role_id'] ?? 0);
                            if (!in_array($roleId, $allowedIds, true)) {
                                $data['duty_role_id'] = $allowedIds[0] ?? null;
                            }
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) =>
                        $fullAccess || in_array($record->duty_role_id, $allowedIds, true)
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Model $record) =>
                        $fullAccess || in_array($record->duty_role_id, $allowedIds, true)
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => $fullAccess),
            ])
            // Если управлять нечем — Relation Manager не показываем вовсе
            ->emptyStateHeading(
                $canManage
                    ? 'Сотрудников пока нет'
                    : 'У вас нет прав на управление сотрудниками дежурства'
            )
            ->emptyStateDescription(
                $canManage
                    ? 'Нажмите «Добавить сотрудника», чтобы назначить первого.'
                    : 'Обратитесь к администратору за правами.'
            );
    }
}