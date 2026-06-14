<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ParentResource\Pages;
use App\Models\SchoolParent;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class ParentResource extends Resource
{
    protected static ?string $model = SchoolParent::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Élèves';
    protected static ?string $navigationLabel = 'Parents';
    protected static ?string $modelLabel = 'Parent';
    protected static ?string $pluralModelLabel = 'Parents';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations du parent')->schema([
                Forms\Components\TextInput::make('first_name')->label('Prénom')->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label('Nom')->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label('Téléphone')->required()->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(255),
                Forms\Components\TextInput::make('occupation')->label('Profession')->maxLength(255),
                Forms\Components\Toggle::make('is_payer')->label('Payeur principal')->default(false),
                Forms\Components\Textarea::make('address')->label('Adresse')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->label('Prénom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('occupation')->label('Profession')->toggleable(),
                Tables\Columns\IconColumn::make('is_payer')->label('Payeur')->boolean(),
                Tables\Columns\TextColumn::make('students_count')->counts('students')->label('Enfants'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_payer')->label('Payeur principal'),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParents::route('/'),
            'create' => Pages\CreateParent::route('/create'),
            'edit' => Pages\EditParent::route('/{record}/edit'),
        ];
    }
}
