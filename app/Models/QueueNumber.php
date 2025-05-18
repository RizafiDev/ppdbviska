<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueueNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'queue_number',
        'status',
        'called_at',
        'finished_at',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
}