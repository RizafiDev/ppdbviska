<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use App\Models\QueueNumber;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class Antrian extends Widget
{
    protected static string $view = 'filament.widgets.antrian';
    protected int | string | array $columnSpan = 'full';
    
    // Menambahkan polling interval 10 detik
    // protected static ?string $pollingInterval = '10s';
    
    // Atau bisa menggunakan property ini jika Anda ingin lebih kontrol
    protected static ?int $pollingInterval = 1000; // dalam milliseconds

    public $queues;

    public function mount()
    {
        $this->loadQueues();
    }

    public function loadQueues()
    {
        $this->queues = Queue::with([
            'queueNumbers' => function ($query) {
                $query->where('status', 'menunggu')->orderByDesc('created_at');
            },
            'currentQueueNumber'
        ])->where('status', 'melayani')->get();
    }

    // Method ini akan dipanggil setiap polling interval
    public function refreshData()
    {
        $this->loadQueues();
    }
}