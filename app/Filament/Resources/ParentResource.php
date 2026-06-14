<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ParentResource\Pages;
use App\Models\SchoolParent;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ParentResource extends Resource
{
    protected static ?string $model = SchoolParent::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Students');
    }

    public static function getNavigationLabel(): string
    {
        return __('Parents');
    }

    public static function getModelLabel(): string
    {
        return __('Parent');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Parents');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Parent Information'))->schema([
                Forms\Components\TextInput::make('first_name')->label(__('First Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label(__('Last Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label(__('Phone'))->required()->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->label(__('Email'))->email()->maxLength(255),
                Forms\Components\TextInput::make('occupation')->label(__('Occupation'))->maxLength(255),
                Forms\Components\Toggle::make('is_payer')->label(__('Primary Payer'))->default(false),
                Forms\Components\Textarea::make('address')->label(__('Address'))->columnSpanFull(),
            ])->columns(2),

            Section::make(__('Assigned Students'))->schema([
                Forms\Components\Select::make('students')
                    ->label(__('Assigned Students'))
                    ->relationship('students', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->label(__('First Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label(__('Last Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('occupation')->label(__('Occupation'))->toggleable(),
                Tables\Columns\IconColumn::make('is_payer')->label(__('Payer'))->boolean(),
                Tables\Columns\TextColumn::make('students_count')->counts('students')->label(__('Children')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_payer')->label(__('Primary Payer')),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListParents::route('/'),
            'create' => Pages\CreateParent::route('/create'),
            'edit'   => Pages\EditParent::route('/{record}/edit'),
        ];
    }
}
