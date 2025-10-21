<?php

namespace App\Filament\Resources\ModelFileResource\Pages;

use App\Filament\Resources\ModelFileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModelFiles extends ListRecords
{
    protected static string $resource = ModelFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
