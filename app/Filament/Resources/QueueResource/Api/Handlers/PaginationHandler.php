<?php
namespace App\Filament\Resources\QueueResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filament\Resources\QueueResource;
use App\Filament\Resources\QueueResource\Api\Transformers\QueueTransformer;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = QueueResource::class;
    public static bool $public = true;


    /**
     * List of Queue
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function handler()
{
    $query = static::getEloquentQuery()
        ->where('status', 'melayani');

    $query = QueryBuilder::for($query)
        ->allowedFields($this->getAllowedFields() ?? [])
        ->allowedSorts($this->getAllowedSorts() ?? [])
        ->allowedFilters($this->getAllowedFilters() ?? [])
        ->allowedIncludes($this->getAllowedIncludes() ?? []);

    // Periksa jika per_page=all maka ambil semua data tanpa paginate
    $perPage = request()->query('per_page');

    if ($perPage === 'all') {
        $results = $query->get();
        return QueueTransformer::collection($results);
    }

    $results = $query
        ->paginate($perPage)
        ->appends(request()->query());

    return QueueTransformer::collection($results);
}

}
