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

    // Ubah properti width menjadi 'full' jika ada
protected int | string | array $columnSpan = 'full';

// ATAU gunakan metode ini di dalam konfigurasi widget
public static function canView(): bool
{
    return true;
}

public function getColumnSpan(): int | string | array
{
    return 'full';
}

// Jika menggunakan card
protected function getCardWidth(): string
{
    return 'full';
}
    public $queues;
    
    public function mount()
    {
        $this->loadQueues();
    }
    
    // Load queues data
    public function loadQueues()
    {
        $this->queues = Queue::with(['queueNumbers', 'currentQueueNumber'])->get();
    }
    
    // Method to create a new queue number directly
    public function createQueueNumber($queueId)
    {
        $queue = Queue::find($queueId);
        
        if (!$queue) {
            Notification::make()
                ->title('Error')
                ->body('Tempat layanan tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }
        
// Tetapkan prefix sesuai nama queue (termasuk angka, huruf, tanpa spasi atau simbol)
$queuePrefix = Str::of($queue->name)
    ->replaceMatches('/[^a-zA-Z0-9]/', '') // Hilangkan simbol tapi pertahankan angka & huruf
    ->upper(); // Jadikan kapital

// Ambil antrian terakhir untuk prefix ini
$lastQueueNumber = QueueNumber::where('queue_id', $queue->id)
    ->where('queue_number', 'LIKE', $queuePrefix . '-%')
    ->orderByRaw('CAST(SUBSTRING(queue_number, LENGTH(?) + 2) AS UNSIGNED) DESC', [$queuePrefix])
    ->first();

// Tentukan nomor berikutnya
$nextNumber = 1;
if ($lastQueueNumber) {
    $parts = explode('-', $lastQueueNumber->queue_number);
    if (count($parts) > 1) {
        $nextNumber = (int)$parts[1] + 1;
    }
}

// Format queue_number baru
$newQueueNumber = $queuePrefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        // Create the queue number record
        QueueNumber::create([
            'queue_id' => $queue->id,
            'queue_number' => $newQueueNumber,
            'status' => 'menunggu', // Default status
        ]);
        
        // Update total queues count in the queue
        $queue->increment('total_queues');
        
        // Show success notification
        Notification::make()
            ->title('Berhasil')
            ->body("Antrian $newQueueNumber berhasil dibuat.")
            ->success()
            ->send();
            
        // Reload queues data
        $this->loadQueues();
    }
}
