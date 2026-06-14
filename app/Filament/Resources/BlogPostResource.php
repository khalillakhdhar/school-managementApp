<?php
namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return __('Communication'); }
    public static function getNavigationLabel(): string  { return __('Blog'); }
    public static function getModelLabel(): string       { return __('Post'); }
    public static function getPluralModelLabel(): string { return __('Blog Posts'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Post Content'))->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()->maxLength(255)->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('excerpt')
                    ->label(__('Excerpt'))
                    ->rows(2)->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->label(__('Content'))
                    ->required()->columnSpanFull()
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike',
                        'h2', 'h3', 'bulletList', 'orderedList',
                        'blockquote', 'link', 'undo', 'redo',
                    ]),
            ])->columns(2),
            Section::make(__('Publication'))->schema([
                Forms\Components\FileUpload::make('cover_image_path')
                    ->label(__('Cover Image'))
                    ->image()->directory('blog/covers')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')
                    ->label(__('Published'))
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $state
                        ? $set('published_at', now())
                        : $set('published_at', null)
                    ),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label(__('Publication Date'))
                    ->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image_path')
                    ->label('')
                    ->circular()->size(40),
                Tables\Columns\TextColumn::make('title')->label(__('Title'))->searchable()->sortable()->limit(50),
                Tables\Columns\TextColumn::make('author.name')->label(__('Author'))->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('Published'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('Published At'))
                    ->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->date()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label(__('Published')),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label(__('Publish'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['is_published' => true, 'published_at' => now()]))
                    ->visible(fn ($record) => !$record->is_published),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit'   => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
