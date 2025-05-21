<?php
namespace App\Filament\Resources\QueueResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\QueueResource;
use Illuminate\Routing\Router;


class QueueApiService extends ApiService
{
    protected static string | null $resource = QueueResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
