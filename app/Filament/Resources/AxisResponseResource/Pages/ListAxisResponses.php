<?php

namespace App\Filament\Resources\AxisResponseResource\Pages;

use App\Filament\Resources\AxisResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAxisResponses extends ListRecords
{
    protected static string $resource = AxisResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
