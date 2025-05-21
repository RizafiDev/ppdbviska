<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'date',
        'period_type',
        'period_label',
        'total_queue_created',
        'total_queue_called',
        'total_queue_finished',
        'total_queue_canceled',
        'avg_wait_time',
        'avg_service_time',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
    public function tempatLayanan()
{
    return $this->belongsTo(Queue::class, 'tempat_layanan_id');
}
}
