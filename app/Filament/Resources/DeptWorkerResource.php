<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\DeptWorkerResource\Pages\ListDeptWorkers;
use App\Filament\Resources\DeptWorkerResource\Pages\CreateDeptWorker;
use App\Filament\Resources\DeptWorkerResource\Pages\EditDeptWorker;
use App\Filament\Resources\DeptWorkerResource\Pages;
use App\Filament\Resources\DeptWorkerResource\RelationManagers;
use App\Models\DeptWorker;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeptWorkerResource extends Resource
{
    protected static ?string $model = DeptWorker::class;

    protected static bool $shouldRegisterNavigation = false;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('worker_id')
                    ->required()
                    ->numeric(),
                TextInput::make('dept_id')
                    ->required()
                    ->numeric(),
                TextInput::make('position')
                    ->maxLength(1000),
                TextInput::make('order_in_dept')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('worker_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dept_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('position')
                    ->searchable(),
                TextColumn::make('order_in_dept')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeptWorkers::route('/'),
            'create' => CreateDeptWorker::route('/create'),
            'edit' => EditDeptWorker::route('/{record}/edit'),
        ];
    }
}
