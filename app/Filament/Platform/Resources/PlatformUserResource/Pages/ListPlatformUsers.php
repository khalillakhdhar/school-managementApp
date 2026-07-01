<?php

namespace App\Filament\Platform\Resources\PlatformUserResource\Pages;

use App\Filament\Platform\Resources\PlatformUserResource;
use Filament\Resources\Pages\ListRecords;

class ListPlatformUsers extends ListRecords
{
    protected static string $resource = PlatformUserResource::class;
}
