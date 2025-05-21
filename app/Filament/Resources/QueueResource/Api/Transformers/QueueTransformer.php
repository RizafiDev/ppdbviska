<?php
namespace App\Filament\Resources\QueueResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Queue;

/**
 * @property Queue $resource
 */
class QueueTransformer extends JsonResource
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
