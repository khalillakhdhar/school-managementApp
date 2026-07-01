<?php

namespace App\Filament\Platform\Resources\PlatformUserResource\Pages;

use App\Filament\Platform\Resources\PlatformUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlatformUser extends EditRecord
{
    protected static string $resource = PlatformUserResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
