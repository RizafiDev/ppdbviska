<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'current_queue_id',
        'total_queues',
    ];

    public const STATUSES = [
    'istirahat' => 'Istirahat',
    'melayani' => 'Melayani', 
    'libur' => 'Libur'
];

    public function queueNumbers()
    {
        return $this->hasMany(QueueNumber::class);
    }

    public function queueSessions()
    {
        return $this->hasMany(QueueSession::class);
    }

    public function currentQueue()
    {
        return $this->belongsTo(QueueNumber::class, 'current_queue_id');
    }
    public function currentQueueNumber()
{
    return $this->belongsTo(QueueNumber::class, 'current_queue_id');
}
}