<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomResource\Pages;
use App\Models\Classroom;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return __('School'); }
    public static function getNavigationLabel(): string  { return __('Classrooms'); }
    public static function getModelLabel(): string       { return __('Classroom'); }
    public static function getPluralModelLabel(): string { return __('Classrooms'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Classroom Information'))->schema([
                Forms\Components\Select::make('level_id')
                    ->label(__('Level'))
                    ->relationship('level', 'name')
                    ->required()->searchable()->preload(),
                Forms\Components\TextInput::make('name')
                    ->label(__('Classroom Name'))
                    ->required()->maxLength(50)
                    ->placeholder('1A'),
                Forms\Components\TextInput::make('capacity')
                    ->label(__('Capacity'))
                    ->numeric()->required()->default(30)->minValue(1)->maxValue(100),
                Forms\Components\Select::make('teacher_id')
                    ->label(__('Class Teacher'))
                    ->options(
                        Employee::active()
                            ->teachers()
                            ->orderBy('last_name')
                            ->get()
                            ->mapWithKeys(fn ($e) => [$e->id => "{$e->full_name}" . ($e->specialite ? " — {$e->specialite}" : '')])
                    )
                    ->nullable()->searchable(),
                Forms\Components\Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('level.code')
                    ->label(__('Level'))->badge()->color('primary')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Classroom'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('teacher.first_name')
                    ->label(__('Class Teacher'))
                    ->formatStateUsing(fn ($state, $record) => $record->teacher
                        ? $record->teacher->full_name . ($record->teacher->specialite ? " ({$record->teacher->specialite})" : '')
                        : '—'),
                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('Capacity'))->sortable(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label(__('Students'))
                    ->counts('students')
                    ->badge()->color('success'),
            ])
            ->defaultSort('level_id')
            ->filters([
                Tables\Filters\SelectFilter::make('level_id')
                    ->label(__('Level'))
                    ->relationship('level', 'name'),
                Tables\Filters\Filter::make('no_teacher')
                    ->label(__('Without Teacher'))
                    ->query(fn ($query) => $query->whereNull('teacher_id')),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClassrooms::route('/'),
            'create' => Pages\CreateClassroom::route('/create'),
            'edit'   => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
