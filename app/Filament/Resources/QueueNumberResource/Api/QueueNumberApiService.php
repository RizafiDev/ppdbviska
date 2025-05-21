<?php
namespace App\Filament\Resources\QueueNumberResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\QueueNumberResource;
use Illuminate\Routing\Router;


class QueueNumberApiService extends ApiService
{
    protected static string | null $resource = QueueNumberResource::class;

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
