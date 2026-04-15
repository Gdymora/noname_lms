<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Настройки системы';
    protected static ?string $navigationLabel = 'Статичные страницы';

    protected static ?string $modelLabel = 'Статичную страницу';
protected static ?string $pluralModelLabel = 'Статичные страницы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Контент страницы')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Ссылка (URL)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Например: offer, privacy, contacts'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Текст страницы')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Опубликовано')
                            ->default(true),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Ссылка')
                    ->prefix('/p/')
                    ->badge()
                    ->color('gray')
                    ->copyable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Статус')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Кнопка просмотра на сайте
                Tables\Actions\Action::make('view')
                    ->label('Открыть')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record) => route('public.page', $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}