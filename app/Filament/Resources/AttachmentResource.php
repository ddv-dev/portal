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
use Illuminate\Support\Collection;

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

                Tables\Columns\TextColumn::make('sop_list')
                    ->label('SOP')
                    ->getStateUsing(function ($record) {
                        return $record->sop()->pluck('title')->implode(', ');
                    })
                    ->toggleable()
                    ->badge()
                    ->color('success')
                    ->limit(30)
                    ->html()
                    ->formatStateUsing(fn($state) => str_replace(', ', '<br>', $state)),

                Tables\Columns\TextColumn::make('order_list')
                    ->label('Приказы')
                    ->getStateUsing(function ($record) {
                        return $record->orders()->pluck('title')->implode(', ');
                    })
                    ->toggleable()
                    ->badge()
                    ->color('warning')
                    ->limit(30)
                    ->html()
                    ->formatStateUsing(fn($state) => str_replace(', ', '<br>', $state)),

                Tables\Columns\TextColumn::make('news_list')
                    ->label('Новости')
                    ->getStateUsing(function ($record) {
                        return $record->news()->pluck('title')->implode(', ');
                    })
                    ->toggleable()
                    ->badge()
                    ->color('info')
                    ->limit(30)
                    ->html()
                    ->formatStateUsing(fn($state) => str_replace(', ', '<br>', $state)),

                Tables\Columns\TextColumn::make('links_count')
                    ->label('Кол-во связей')
                    ->counts('links')
                    ->toggleable()
                    ->badge()
                    ->color('gray'),
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

                Tables\Filters\SelectFilter::make('sop')
                    ->label('SOP')
                    ->relationship('sop', 'title')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('orders')
                    ->label('Приказы')
                    ->relationship('orders', 'title')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('news')
                    ->label('Новости')
                    ->relationship('news', 'title')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->before(function ($record) {
                        // Проверяем, не используется ли вложение
                        if ($record->isUsed()) {
                            throw new \Exception('Нельзя удалить вложение, которое используется');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->isUsed()) {
                                    throw new \Exception('Некоторые вложения используются и не могут быть удалены');
                                }
                            }
                        }),
                ]),
            ])
            ->searchable()
            ->searchPlaceholder('Поиск вложений...')
            ->defaultSort('id', 'desc');
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