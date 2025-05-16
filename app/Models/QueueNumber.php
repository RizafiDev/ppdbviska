<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueueNumber extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'queue_number',
        'queue_id',
        'qr_code',
        'status',
        'called_at',
        'finished_at',
    ];
}
