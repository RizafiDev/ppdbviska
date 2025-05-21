<?php
namespace App\Filament\Resources\QueueNumberResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\QueueNumberResource;
use App\Filament\Resources\QueueNumberResource\Api\Requests\CreateQueueNumberRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = QueueNumberResource::class;
    public static bool $public = true;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create QueueNumber
     *
     * @param CreateQueueNumberRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateQueueNumberRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}