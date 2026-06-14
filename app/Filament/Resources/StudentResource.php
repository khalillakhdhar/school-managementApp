<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Students');
    }

    public static function getNavigationLabel(): string
    {
        return __('Students');
    }

    public static function getModelLabel(): string
    {
        return __('Student');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Students');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Personal Information'))->schema([
                Forms\Components\TextInput::make('first_name')->label(__('First Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label(__('Last Name'))->required()->maxLength(255),
                Forms\Components\DatePicker::make('date_of_birth')->label(__('Birth Date'))->required(),
                Forms\Components\TextInput::make('id_number')->label(__('ID Number'))->maxLength(255),
            ])->columns(2),

            Section::make(__('School Information'))->schema([
                Forms\Components\Select::make('classroom_id')
                    ->label(__('Classroom'))
                    ->relationship('classroom', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($r) => $r->level ? "{$r->level->code} — {$r->name}" : $r->name)
                    ->nullable()->searchable()->preload(),
                Forms\Components\TextInput::make('class')->label(__('Class'))->maxLength(255),
                Forms\Components\TextInput::make('level')->label(__('Level'))->maxLength(255),
                Forms\Components\DatePicker::make('enrollment_date')->label(__('Enrollment Date')),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active'    => __('Active'),
                        'inactive'  => __('Inactive'),
                        'suspended' => __('Suspended'),
                        'graduated' => __('Graduated'),
                    ])
                    ->default('active')->required(),
                Forms\Components\Textarea::make('address')->label(__('Address'))->columnSpanFull(),
            ])->columns(2),

            Section::make(__('Health Information'))->schema([
                Forms\Components\Textarea::make('health_info')->label(__('Health Info')),
                Forms\Components\Textarea::make('allergies')->label(__('Allergies')),
                Forms\Components\Textarea::make('medications')->label(__('Medications')),
            ])->columns(3)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->label(__('First Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label(__('Last Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('class')->label(__('Class'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('level')->label(__('Level'))->sortable(),
                Tables\Columns\TextColumn::make('classroom.name')
                    ->label(__('Classroom'))
                    ->badge()->color('info')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'inactive'  => 'danger',
                        'suspended' => 'warning',
                        'graduated' => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'    => __('Active'),
                        'inactive'  => __('Inactive'),
                        'suspended' => __('Suspended'),
                        'graduated' => __('Graduated'),
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('date_of_birth')->label(__('Birth Date'))->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active'    => __('Active'),
                        'inactive'  => __('Inactive'),
                        'suspended' => __('Suspended'),
                        'graduated' => __('Graduated'),
                    ]),
                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label(__('Classroom'))
                    ->relationship('classroom', 'name'),
                Tables\Filters\SelectFilter::make('class')
                    ->label(__('Class'))
                    ->options(fn () => Student::distinct()->pluck('class', 'class')),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit'   => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
