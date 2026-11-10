<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DutyResource\Pages;
use App\Filament\Resources\DutyResource\RelationManagers;
use App\Models\Duty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DutyResource extends Resource
{
    protected static ?string $model = Duty::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Дежурства';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Дата дежурства')
                    ->required()
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Подгружаем счётчик только по разрешённым ролям
                $user = Auth::user();

                if (!$user) {
                    return $query;
                }

                $allowedIds = $user->manageableDutyRoleIds();
                $fullAccess = $user->hasPermission('duty_manage_all');

                if ($fullAccess) {
                    $query->withCount('workers');
                } elseif (!empty($allowedIds)) {
                    $query->withCount([
                        'workers as workers_count' => fn (Builder $q) =>
                            $q->whereIn('duty_role_id', $allowedIds),
                    ]);
                } else {
                    // Нет прав — показываем 0
                    $query->withCount([
                        'workers as workers_count' => fn (Builder $q) =>
                            $q->whereRaw('1 = 0'),
                    ]);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('workers_count')
                    ->label('Сотрудников (доступных)')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Duty $record) => static::userCanEditDuty($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()?->hasPermission('duty_manage_all')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => Auth::user()?->hasPermission('duty_manage_all')),
            ])
            ->defaultSort('date', 'desc');
    }

    protected static function userCanEditDuty(Duty $duty): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->hasPermission('duty_manage_all')
            || !empty($user->manageableDutyRoleIds());
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WorkersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDuties::route('/'),
            'create' => Pages\CreateDuty::route('/create'),
            'edit' => Pages\EditDuty::route('/{record}/edit'),
        ];
    }
}