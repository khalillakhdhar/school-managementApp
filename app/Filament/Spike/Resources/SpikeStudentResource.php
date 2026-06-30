<?php

namespace App\Filament\Spike\Resources;

use App\Filament\Spike\Resources\SpikeStudentResource\Pages;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * PHASE 0 SPIKE — pilot resource validating tenant-scoped CRUD.
 * Filament auto-scopes queries to the current School tenant via the
 * `school` ownership relationship declared on the panel.
 */
class SpikeStudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Élèves (spike)';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('first_name')->required(),
            Forms\Components\TextInput::make('last_name')->required(),
            Forms\Components\DatePicker::make('date_of_birth')->required(),
            Forms\Components\DatePicker::make('enrollment_date')->required(),
            Forms\Components\TextInput::make('class')->required(),
            Forms\Components\TextInput::make('level')->required(),
            Forms\Components\TextInput::make('status')->default('active')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('school_id')->label('Tenant')->badge()->color('info'),
                Tables\Columns\TextColumn::make('first_name')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->searchable(),
                Tables\Columns\TextColumn::make('class'),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->headerActions([Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpikeStudents::route('/'),
        ];
    }
}
