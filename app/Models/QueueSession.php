<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueueSession extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'queue_id',
        'date',
        'start_time',
        'end_time',
    ];
}
