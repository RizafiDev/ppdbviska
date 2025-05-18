<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

public function currentQueueNumber(): HasOne
{
    return $this->hasOne(\App\Models\QueueNumber::class)
        ->where('status', 'dipanggil')
        ->orderBy('created_at', 'desc');
}

public function queueNumbers()
{
    return $this->hasMany(QueueNumber::class)->latest();
}

    
    public function queueSessions()
    {
        return $this->hasMany(QueueSession::class);
    }

    public function currentQueue()
    {
        return $this->belongsTo(QueueNumber::class, 'current_queue_id');
    }
}