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

    public function handler(CreateQueueNumberRequest $request)
    {
        $model = new (static::getModel());
        $model->fill($request->all());
        $model->save();

        // Load relationship untuk mendapatkan nama queue
        $model->load('queue');

        // Tambahkan QR code data dalam response
        $response_data = $model->toArray();
        $response_data['qr_code'] = [
            'url' => $model->status_check_url,
            'svg' => $model->getQrCodeSvg(),
            'base64' => base64_encode($model->qr_code_data)
        ];

        return static::sendSuccessResponse($response_data, "Successfully Create Resource");
    }
}