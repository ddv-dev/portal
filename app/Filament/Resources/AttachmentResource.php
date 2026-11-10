<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Exception;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AttachmentResource\Pages\ListAttachments;
use App\Filament\Resources\AttachmentResource\Pages\CreateAttachment;
use App\Filament\Resources\AttachmentResource\Pages\EditAttachment;
use App\Filament\Resources\AttachmentResource\Pages;
use App\Models\Attachment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class AttachmentResource extends Resource
{
    protected static ?string $model = Attachment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paper-clip';
    protected static string | \UnitEnum | null $navigationGroup = 'Документация';
    protected static ?string $modelLabel = 'Вложение';
    protected static ?string $pluralModelLabel = 'Вложения';
    protected static ?string $navigationLabel = 'Вложения';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о вложении')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Введите название файла...'),

                            TextInput::make('type')
                                ->label('Тип')
                                ->maxLength(1000)
                                ->placeholder('image, document, pdf...'),

                            TextInput::make('path')
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
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Название скопировано'),

                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('path')
                    ->label('Путь')
                    ->limit(50)
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Путь скопирован'),

                TextColumn::make('sop_list')
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

                TextColumn::make('order_list')
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

                TextColumn::make('news_list')
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

                TextColumn::make('links_count')
                    ->label('Кол-во связей')
                    ->counts('links')
                    ->toggleable()
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'image' => 'Изображение',
                        'document' => 'Документ',
                        'pdf' => 'PDF',
                        'video' => 'Видео',
                        'audio' => 'Аудио',
                        'archive' => 'Архив',
                    ]),

                SelectFilter::make('sop')
                    ->label('SOP')
                    ->relationship('sop', 'title')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('orders')
                    ->label('Приказы')
                    ->relationship('orders', 'title')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('news')
                    ->label('Новости')
                    ->relationship('news', 'title')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр'),
                EditAction::make()
                    ->label('Редактировать'),
                DeleteAction::make()
                    ->label('Удалить')
                    ->before(function ($record) {
                        // Проверяем, не используется ли вложение
                        if ($record->isUsed()) {
                            throw new Exception('Нельзя удалить вложение, которое используется');
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->isUsed()) {
                                    throw new Exception('Некоторые вложения используются и не могут быть удалены');
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
            'index' => ListAttachments::route('/'),
            'create' => CreateAttachment::route('/create'),
            'edit' => EditAttachment::route('/{record}/edit'),
        ];
    }
}