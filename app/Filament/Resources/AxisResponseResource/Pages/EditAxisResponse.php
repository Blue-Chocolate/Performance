<?php

namespace App\Filament\Resources\AxisResponseResource\Pages;

use App\Filament\Resources\AxisResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAxisResponse extends EditRecord
{
    protected static string $resource = AxisResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
