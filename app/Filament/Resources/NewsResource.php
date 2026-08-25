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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                                ->placeholder('Заголовок новости...'),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Опубликовано')
                                ->default(true)
                                ->inline(false),

                            Forms\Components\Textarea::make('description')
                                ->label('Текст новости')
                                ->rows(6)
                                ->required()
                                ->placeholder('Текст новости...'),
                        ]),
                    ]),

                Section::make('Вложения')
                    ->schema([
                        Grid::make(2)->schema([
                            // 1. Загрузка новых файлов - УПРОЩЕННАЯ ВЕРСИЯ
                            FileUpload::make('new_attachments')
                                ->label('Загрузить новые файлы')
                                ->multiple()
                                ->directory('attachments/news/' . date('Y/m/d'))
                                ->preserveFilenames()
                                ->maxSize(20480) // 20MB
                                ->nullable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && is_array($state)) {
                                        session()->put('news_attachments', $state);
                                    }
                                }),
                            
                            // 2. Выбор существующих вложений
                            Select::make('attachments')
                                ->label('Выбрать существующие вложения')
                                ->relationship('attachments', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->placeholder('Выберите существующие вложения')
                                ->helperText('Выберите уже загруженные файлы'),
                        ]),
                    ]),

                Section::make('Загруженные файлы (предпросмотр)')
                    ->schema([
                        Forms\Components\Placeholder::make('preview_attachments')
                            ->label('Текущие вложения')
                            ->content(function ($record) {
                                if (!$record) {
                                    $sessionFiles = session('news_attachments', []);
                                    if (!empty($sessionFiles)) {
                                        $html = '<ul class="list-disc list-inside space-y-1">';
                                        foreach ($sessionFiles as $file) {
                                            $html .= '<li>📎 ' . htmlspecialchars(basename($file)) . '</li>';
                                        }
                                        $html .= '</ul>';
                                        return $html;
                                    }
                                    return 'Нет загруженных файлов';
                                }
                                
                                $attachments = $record->attachments;
                                if ($attachments->isEmpty()) {
                                    return 'Нет загруженных файлов';
                                }
                                
                                $html = '<ul class="list-disc list-inside space-y-1">';
                                foreach ($attachments as $attachment) {
                                    $html .= '<li>';
                                    $html .= htmlspecialchars($attachment->name);
                                    $html .= ' ';
                                    
                                    if ($attachment->path) {
                                        $html .= '<span class="text-xs text-gray-500">(' . htmlspecialchars($attachment->path) . ')</span>';
                                    }
                                    
                                    if (str_starts_with($attachment->type, 'image/')) {
                                        $html .= ' <span class="text-xs text-green-600">🖼️ Изображение</span>';
                                    } elseif ($attachment->type === 'application/pdf') {
                                        $html .= ' <span class="text-xs text-red-600">📄 PDF</span>';
                                    } elseif (str_contains($attachment->type, 'word')) {
                                        $html .= ' <span class="text-xs text-blue-600">📝 Word</span>';
                                    } elseif (str_contains($attachment->type, 'excel') || str_contains($attachment->type, 'spreadsheet')) {
                                        $html .= ' <span class="text-xs text-green-600">📊 Excel</span>';
                                    } else {
                                        $html .= ' <span class="text-xs text-gray-500">📎 ' . htmlspecialchars($attachment->type) . '</span>';
                                    }
                                    
                                    $html .= '</li>';
                                }
                                $html .= '</ul>';
                                
                                return $html;
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
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

                Tables\Columns\TextColumn::make('description')
                    ->label('Текст')
                    ->limit(100)
                    ->html()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Опубликовано')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Вложений')
                    ->counts('attachments')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    // Метод для сохранения файлов после создания/обновления
    public static function afterCreate($record, $data)
    {
        static::saveAttachments($record, $data);
    }

    public static function afterUpdate($record, $data)
    {
        static::saveAttachments($record, $data);
    }

    protected static function saveAttachments($record, $data)
    {
        try {
            $uploadedFiles = session('news_attachments', []);
            
            if (!empty($uploadedFiles) && is_array($uploadedFiles)) {
                foreach ($uploadedFiles as $filePath) {
                    $fullPath = storage_path('app/public/' . $filePath);
                    
                    if (file_exists($fullPath)) {
                        $mimeType = mime_content_type($fullPath);
                        $filename = basename($filePath);
                        
                        $attachment = $record->attachments()->create([
                            'name' => $filename,
                            'path' => $filePath,
                            'type' => $mimeType ?: 'application/octet-stream',
                        ]);
                        
                        Log::info('Attachment saved', [
                            'news_id' => $record->id,
                            'attachment_id' => $attachment->id,
                            'file' => $filePath
                        ]);
                    }
                }
                
                session()->forget('news_attachments');
            }
        } catch (\Exception $e) {
            Log::error('Error saving attachments', [
                'error' => $e->getMessage(),
                'news_id' => $record->id
            ]);
        }
    }
}
