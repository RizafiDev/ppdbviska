<?php
namespace App\Filament\Resources\QueueResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\QueueResource;
use App\Filament\Resources\QueueResource\Api\Requests\CreateQueueRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = QueueResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Queue
     *
     * @param CreateQueueRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateQueueRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}