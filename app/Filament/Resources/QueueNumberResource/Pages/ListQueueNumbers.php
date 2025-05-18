<?php

namespace App\Filament\Resources\QueueNumberResource\Pages;

use App\Filament\Resources\QueueNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQueueNumbers extends ListRecords
{
    protected static string $resource = QueueNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
