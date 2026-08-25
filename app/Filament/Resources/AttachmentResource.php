<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttachmentResource\Pages;
use App\Models\Attachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class AttachmentResource extends Resource
{
    protected static ?string $model = Attachment::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Документация';
    protected static ?string $modelLabel = 'Вложение';
    protected static ?string $pluralModelLabel = 'Вложения';
    protected static ?string $navigationLabel = 'Вложения';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о вложении')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Введите название файла...'),
                            
                            Forms\Components\TextInput::make('type')
                                ->label('Тип')
                                ->maxLength(1000)
                                ->placeholder('image, document, pdf...'),
                            
                            Forms\Components\TextInput::make('path')
                                ->label('Путь к файлу')
                                ->maxLength(1000)
                                ->placeholder('/uploads/files/document.pdf'),
                            
                            Forms\Components\Select::make('sop_id')
                                ->label('SOP')
                                ->relationship('sop', 'title')
                                ->preload()
                                ->searchable()
                                ->placeholder('Выберите SOP'),
                            
                            Forms\Components\Select::make('order_id')
                                ->label('Приказ')
                                ->relationship('order', 'title')
                                ->preload()
                                ->searchable()
                                ->placeholder('Выберите приказ'),
                            
                            Forms\Components\Select::make('new_id')
                                ->label('Новость')
                                ->relationship('news', 'title')
                                ->preload()
                                ->searchable()
                                ->placeholder('Выберите новость'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Название скопировано'),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('path')
                    ->label('Путь')
                    ->limit(50)
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Путь скопирован'),
                
                Tables\Columns\TextColumn::make('sop.title')
                    ->label('SOP')
                    ->limit(30)
                    ->toggleable()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('order.title')
                    ->label('Приказ')
                    ->limit(30)
                    ->toggleable()
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('news.title')
                    ->label('Новость')
                    ->limit(30)
                    ->toggleable()
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'image' => 'Изображение',
                        'document' => 'Документ',
                        'pdf' => 'PDF',
                        'video' => 'Видео',
                        'audio' => 'Аудио',
                        'archive' => 'Архив',
                    ]),
                
                Tables\Filters\SelectFilter::make('sop_id')
                    ->label('SOP')
                    ->relationship('sop', 'title')
                    ->preload()
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('order_id')
                    ->label('Приказ')
                    ->relationship('order', 'title')
                    ->preload()
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('new_id')
                    ->label('Новость')
                    ->relationship('news', 'title')
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->searchable()
            ->searchPlaceholder('Поиск вложений...')
            ->defaultSort('id', 'desc'); // ← Сортируем по id, а не по created_at
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttachments::route('/'),
            'create' => Pages\CreateAttachment::route('/create'),
            'edit' => Pages\EditAttachment::route('/{record}/edit'),
        ];
    }
}