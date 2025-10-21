<?php

namespace App\Filament\Resources\ModelFileResource\Pages;

use App\Filament\Resources\ModelFileResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditModelFile extends EditRecord
{
    protected static string $resource = ModelFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
