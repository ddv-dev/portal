<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InfoResource\Pages;
use App\Models\Info;
use App\Models\InfoType;
use App\Models\Attachment;
use App\Models\AttachmentLink;
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
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;

class InfoResource extends Resource
{
    protected static ?string $model = Info::class;

    protected static ?string $modelLabel = 'Информация';
    protected static ?string $pluralModelLabel = 'Информация';
    protected static ?string $navigationLabel = 'Информация';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о записи')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Заголовок')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Введите заголовок...'),

                            Select::make('type_id')
                                ->label('Тип информации')
                                ->relationship('type', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Название типа')
                                        ->required()
                                        ->maxLength(1000)
                                        ->placeholder('Введите название нового типа...'),
                                    
                                    Forms\Components\TextInput::make('order_menu')
                                        ->label('Порядок в меню')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->helperText('Чем меньше число, тем выше позиция в меню'),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $type = InfoType::create([
                                        'name' => $data['name'],
                                        'order_menu' => $data['order_menu'] ?? 0,
                                    ]);
                                    return $type->id;
                                })
                                ->placeholder('Выберите или создайте тип информации'),

                            Forms\Components\TextInput::make('order')
                                ->label('Порядок сортировки')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText('Чем меньше число, тем выше позиция'),

                            Forms\Components\DateTimePicker::make('created_at')
                                ->label('Дата создания')
                                ->default(now())
                                ->required()
                                ->helperText('Укажите дату публикации'),
                        ]),
                    ]),

                Section::make('📎 Вложения')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('new_attachments')
                                ->label('Загрузить новые файлы')
                                ->multiple()
                                ->disk('public')
                                ->directory('attachments/info/' . date('Y/m/d'))
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
                                ->columnSpan(1)
                                ->afterStateHydrated(function ($component, $state, $record) {
                                    // Очищаем поле после сохранения
                                    if ($record && $component->getState() === null) {
                                        // Ничего не делаем
                                    }
                                }),

                            Select::make('existing_attachments')
                                ->label('Выбрать существующие файлы')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->placeholder('Выберите файлы...')
                                ->options(function () {
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

                                    $icon = self::getFileIcon($attachment->type);
                                    $color = self::getFileColor($attachment->type);
                                    $html .= '<div class="flex-shrink-0 w-10 h-10 rounded-lg ' . $color . ' flex items-center justify-center shadow-sm">';
                                    $html .= '<i class="fas ' . $icon . ' text-white text-lg"></i>';
                                    $html .= '</div>';

                                    $html .= '<div class="flex-1 min-w-0">';
                                    $html .= '<div class="text-sm font-medium text-gray-700 truncate">';
                                    $html .= htmlspecialchars($attachment->name);
                                    $html .= '</div>';
                                    $html .= '<div class="text-xs text-gray-400">';
                                    $html .= self::getFileTypeLabel($attachment->type);
                                    $html .= '</div>';
                                    $html .= '</div>';

                                    $html .= '</div>';

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
                    ->collapsed(fn($record) => !$record || $record->attachments->count() === 0),
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

                Tables\Columns\TextColumn::make('order')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'gray' : 'success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type.name')
                    ->label('Тип')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Вложений')
                    ->counts('attachments')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_id')
                    ->label('Тип информации')
                    ->relationship('type', 'name')
                    ->preload()
                    ->searchable(),

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
            ->defaultSort('order', 'asc')
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInfo::route('/'),
            'create' => Pages\CreateInfo::route('/create'),
            'edit' => Pages\EditInfo::route('/{record}/edit'),
        ];
    }

    // ============ Обработка вложений ============

    /**
     * Обработка вложений перед сохранением
     */
    public static function beforeSave(Model $record, array $data): void
    {
        // Обрабатываем новые загруженные файлы
        if (isset($data['new_attachments']) && is_array($data['new_attachments'])) {
            foreach ($data['new_attachments'] as $filePath) {
                // Проверяем, существует ли уже такое вложение
                $existingAttachment = Attachment::where('path', $filePath)->first();
                
                if (!$existingAttachment) {
                    // Создаем новое вложение
                    $attachment = Attachment::create([
                        'name' => basename($filePath),
                        'path' => $filePath,
                        'type' => self::getMimeType($filePath),
                    ]);
                    
                    // Привязываем к текущей записи
                    $record->attachments()->attach($attachment->id);
                } else {
                    // Если вложение уже существует, привязываем его
                    $record->attachments()->attach($existingAttachment->id);
                }
            }
        }

        // Обрабатываем выбранные существующие вложения
        if (isset($data['existing_attachments']) && is_array($data['existing_attachments'])) {
            foreach ($data['existing_attachments'] as $attachmentId) {
                $attachment = Attachment::find($attachmentId);
                if ($attachment && !$attachment->isUsed()) {
                    // Привязываем свободное вложение
                    $record->attachments()->attach($attachmentId);
                }
            }
        }
    }

    /**
     * Метод для отвязывания вложения
     */
    public static function detachAttachment($recordId, $attachmentId)
    {
        $record = Info::find($recordId);
        if ($record) {
            $record->attachments()->detach($attachmentId);
        }
    }

    /**
     * Получить MIME тип файла
     */
    private static function getMimeType($path)
    {
        $fullPath = Storage::disk('public')->path($path);
        if (file_exists($fullPath)) {
            $mime = mime_content_type($fullPath);
            return $mime ?: 'application/octet-stream';
        }
        return 'application/octet-stream';
    }

    // ============ Вспомогательные методы для иконок ============

    private static function getFileIcon($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'fa-image';
        }

        return match ($mimeType) {
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

    private static function getFileColor($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'bg-purple-600';
        }

        return match ($mimeType) {
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

    private static function getFileTypeLabel($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'Изображение';
        }

        return match ($mimeType) {
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
}