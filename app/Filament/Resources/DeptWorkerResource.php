<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeptWorkerResource\Pages;
use App\Filament\Resources\DeptWorkerResource\RelationManagers;
use App\Models\DeptWorker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeptWorkerResource extends Resource
{
    protected static ?string $model = DeptWorker::class;

    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('worker_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('dept_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('position')
                    ->maxLength(1000),
                Forms\Components\TextInput::make('order_in_dept')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('worker_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dept_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_in_dept')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDeptWorkers::route('/'),
            'create' => Pages\CreateDeptWorker::route('/create'),
            'edit' => Pages\EditDeptWorker::route('/{record}/edit'),
        ];
    }
}
