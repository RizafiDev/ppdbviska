<?php
namespace App\Filament\Resources\QueueNumberResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filament\Resources\QueueNumberResource;
use App\Filament\Resources\QueueNumberResource\Api\Transformers\QueueNumberTransformer;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = QueueNumberResource::class;
public static bool $public = true;

    /**
     * List of QueueNumber
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function handler()
{
    $query = static::getEloquentQuery();

    $query = QueryBuilder::for($query)
        ->allowedFields([
            'queue_number',
            'status',
            'queue.id',         // ID relasi queue (atau sesuaikan dengan atribut)
            'queue.name',       // jika perlu nama queue
        ])
        ->allowedIncludes(['queue']) // hanya izinkan include relasi 'queue'
        ->get(); // ambil semua data tanpa pagination

    return QueueNumberTransformer::collection($query);
}
}
