<?php
namespace App\Filament\Resources\QueueNumberResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\QueueNumber;

/**
 * @property QueueNumber $resource
 */
class QueueNumberTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}
