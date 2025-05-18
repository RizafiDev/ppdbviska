<?php

namespace App\Filament\Resources\QueueSessionResource\Pages;

use App\Filament\Resources\QueueSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQueueSessions extends ListRecords
{
    protected static string $resource = QueueSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
