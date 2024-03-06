<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use function Livewire\wrap;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->live(onBlur: true)->maxLength(255)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255)
                            ->unique(Post::class, 'slug', ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpan('full'),
                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpan('full'),
                        Select::make('category_id')
                            ->label('Category')
                            ->preload()
                            ->relationship('category', 'name')
                            ->searchable()
                            ->columnSpan('full')
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->unique(Category::class, 'name', ignoreRecord: true)
                                    ->live(onBlur: true)->maxLength(255),

                            ]),
                    ])->columns(),
                Section::make('Image')->relationship('image')->schema([
                    FileUpload::make('path')
                        ->directory('posts')
                        ->image()
                        ->required(),
                    TextInput::make('alt')
                        ->label('Alt')
                        ->columnSpan('full'),
                    TextInput::make('credit')
                        ->label('Credit')
                        ->columnSpan('full'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image.path')
                    ->label('Image'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->getStateUsing(fn(Post $post) => Str::limit($post->title, 50))
                    ->searchable(),
                ToggleColumn::make('published')
                    ->label('Published'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->getStateUsing(fn(Post $post) => $post->created_at->toDateString())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
