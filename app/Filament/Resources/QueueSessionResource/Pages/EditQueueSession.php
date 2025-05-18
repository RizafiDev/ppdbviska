<?php

namespace App\Filament\Resources\QueueSessionResource\Pages;

use App\Filament\Resources\QueueSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQueueSession extends EditRecord
{
    protected static string $resource = QueueSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
