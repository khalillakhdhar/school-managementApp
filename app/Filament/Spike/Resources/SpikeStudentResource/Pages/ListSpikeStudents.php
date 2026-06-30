<?php

namespace App\Filament\Spike\Resources\SpikeStudentResource\Pages;

use App\Filament\Spike\Resources\SpikeStudentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpikeStudents extends ListRecords
{
    protected static string $resource = SpikeStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
