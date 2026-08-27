<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use App\Models\Attachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $modelLabel = 'Новость';
    protected static ?string $pluralModelLabel = 'Новости';
    protected static ?string $navigationLabel = 'Новости';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о новости')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Заголовок')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Введите заголовок новости...'),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Опубликовано')
                                ->default(true)
                                ->helperText('Если включено, новость будет видна на сайте'),

                            Forms\Components\Textarea::make('description')
                                ->label('Текст новости')
                                ->rows(8)
                                ->required()
                                ->placeholder('Введите текст новости...')
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('📎 Вложения')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. Загрузка новых файлов
                            FileUpload::make('new_attachments')
                                ->label('Загрузить новые файлы')
                                ->multiple()
                                ->disk('public')
                                ->directory('attachments/news/' . date('Y/m/d'))
                                ->preserveFilenames()
                                ->maxSize(20480)
                                ->acceptedFileTypes([
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                    'image/webp',
                                    'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'text/plain',
                                ])
                                ->helperText('Максимальный размер файла: 20MB')
                                ->columnSpan(1),

                            // 2. Выбор существующих вложений
                            Select::make('existing_attachments')
                                ->label('Выбрать существующие файлы')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->placeholder('Выберите файлы...')
                                ->options(function () {
                                    // Показываем только свободные файлы
                                    $unusedIds = Attachment::whereDoesntHave('links')->pluck('id');
                                    return Attachment::whereIn('id', $unusedIds)
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(function ($item) {
                                            return [$item->id => $item->name . ' (' . self::getFileTypeLabel($item->type) . ')'];
                                        })
                                        ->toArray();
                                })
                                ->helperText('Выберите уже загруженные файлы, которые не привязаны к другим записям')
                                ->columnSpan(1),
                        ]),
                    ]),

                // Привязанные вложения
                Section::make('📎 Привязанные вложения')
                    ->schema([
                        Placeholder::make('current_attachments')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record || $record->attachments->isEmpty()) {
                                    return new HtmlString('<div class="text-gray-400 text-sm py-4 text-center">Нет привязанных вложений</div>');
                                }

                                $html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
                                
                                foreach ($record->attachments as $attachment) {
                                    $html .= '<div class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-all duration-200 border border-gray-200 hover:border-blue-300">';
                                    $html .= '<div class="flex items-center gap-3 flex-1 min-w-0">';
                                    
                                    // Иконка по типу файла
                                    $icon = self::getFileIcon($attachment->type);
                                    $color = self::getFileColor($attachment->type);
                                    $html .= '<div class="flex-shrink-0 w-10 h-10 rounded-lg ' . $color . ' flex items-center justify-center shadow-sm">';
                                    $html .= '<i class="fas ' . $icon . ' text-white text-lg"></i>';
                                    $html .= '</div>';
                                    
                                    // Имя файла и тип
                                    $html .= '<div class="flex-1 min-w-0">';
                                    $html .= '<div class="text-sm font-medium text-gray-700 truncate">';
                                    $html .= htmlspecialchars($attachment->name);
                                    $html .= '</div>';
                                    $html .= '<div class="text-xs text-gray-400">';
                                    $html .= self::getFileTypeLabel($attachment->type);
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    
                                    $html .= '</div>';
                                    
                                    // Кнопка отвязывания
                                    $html .= '<button type="button" onclick="if(confirm(\'Отвязать вложение «' . htmlspecialchars($attachment->name) . '»?\')) { @this.call(\'detachAttachment\', ' . $attachment->id . ') }" class="flex-shrink-0 ml-2 p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200" title="Отвязать">';
                                    $html .= '<i class="fas fa-unlink"></i>';
                                    $html .= '</button>';
                                    
                                    $html .= '</div>';
                                }
                                
                                $html .= '</div>';
                                
                                return new HtmlString($html);
                            })
                            ->columnSpan('full'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => !$record || $record->attachments->count() === 0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable()
                    ->width('80px'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Опубликовано')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Вложений')
                    ->counts('attachments')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Опубликованные')
                    ->query(fn($query) => $query->where('is_active', true))
                    ->toggle(),

                Tables\Filters\Filter::make('has_attachments')
                    ->label('С вложениями')
                    ->query(fn($query) => $query->whereHas('attachments'))
                    ->toggle(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }

    // ============ Вспомогательные методы для иконок ============

    /**
     * Получить иконку для типа файла
     */
    private static function getFileIcon($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'fa-image';
        }
        
        return match($mimeType) {
            'application/pdf' => 'fa-file-pdf',
            'application/msword' => 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
            'application/vnd.ms-excel' => 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
            'text/plain' => 'fa-file-alt',
            'application/zip' => 'fa-file-archive',
            'application/x-rar-compressed' => 'fa-file-archive',
            default => 'fa-file',
        };
    }

    /**
     * Получить цвет для типа файла
     */
    private static function getFileColor($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'bg-purple-600';
        }
        
        return match($mimeType) {
            'application/pdf' => 'bg-red-600',
            'application/msword' => 'bg-blue-600',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'bg-blue-600',
            'application/vnd.ms-excel' => 'bg-green-600',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'bg-green-600',
            'text/plain' => 'bg-gray-600',
            'application/zip' => 'bg-yellow-600',
            'application/x-rar-compressed' => 'bg-yellow-600',
            default => 'bg-gray-500',
        };
    }

    /**
     * Получить человекочитаемое название типа файла
     */
    private static function getFileTypeLabel($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'Изображение';
        }
        
        return match($mimeType) {
            'application/pdf' => 'PDF документ',
            'application/msword' => 'Word документ',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word документ (DOCX)',
            'application/vnd.ms-excel' => 'Excel таблица',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel таблица (XLSX)',
            'text/plain' => 'Текстовый файл',
            'application/zip' => 'ZIP архив',
            'application/x-rar-compressed' => 'RAR архив',
            default => 'Файл',
        };
    }

    /**
     * Форматировать размер файла
     */
    private static function formatFileSize($path)
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                $size = Storage::disk('public')->size($path);
                
                if ($size < 1024) {
                    return $size . ' Б';
                } elseif ($size < 1048576) {
                    return round($size / 1024, 1) . ' КБ';
                } else {
                    return round($size / 1048576, 1) . ' МБ';
                }
            }
        } catch (\Exception $e) {
            return '—';
        }
        
        return '—';
    }
}