<?php
namespace App\Filament\Resources\QueueNumberResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\QueueNumberResource;
use App\Filament\Resources\QueueNumberResource\Api\Requests\UpdateQueueNumberRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = QueueNumberResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update QueueNumber
     *
     * @param UpdateQueueNumberRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateQueueNumberRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}