<?php

namespace App\Filament\Resources\PhoneResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\BulkActionGroup;

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers';

    protected static ?string $title = 'Сотрудники с этим телефоном';

    protected static ?string $modelLabel = 'сотрудник';

    protected static ?string $pluralModelLabel = 'сотрудники';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_full')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name_full')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('deptWorkers.dept.name_short')
                    ->label('Отдел')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->toggleable(),

                TextColumn::make('login_ad')
                    ->label('Логин AD')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // если не нужна привязка «вручную» — можно оставить пустым
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\WorkerResource::getUrl('edit', ['record' => $record])),
                DissociateAction::make()
                    ->label('Отвязать'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Отвязать выбранных'),
                ]),
            ])
            ->defaultSort('name_full');
    }
}