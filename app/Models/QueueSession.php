<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueueSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'date',
        'start_time',
        'end_time',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
}