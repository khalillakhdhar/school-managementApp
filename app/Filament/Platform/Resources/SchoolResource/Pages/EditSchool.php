<?php

namespace App\Filament\Platform\Resources\SchoolResource\Pages;

use App\Filament\Platform\Resources\SchoolResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
