<?php

namespace App\Filament\Resources\QueueNumberResource\Pages;

use App\Filament\Resources\QueueNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQueueNumber extends EditRecord
{
    protected static string $resource = QueueNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
