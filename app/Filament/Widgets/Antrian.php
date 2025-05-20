<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use App\Models\QueueNumber;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;
// Import EscPos classes
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class Antrian extends Widget
{
    protected static string $view = 'filament.widgets.antrian';
    protected int | string | array $columnSpan = 'full';

    public $queues;
    
    // Printer settings
    protected $printerPort = "COM3";
    protected $baudRate = 9600;  // Baud rate for the printer
    
    public function mount()
    {
        $this->loadQueues();
    }
    
    public function loadQueues()
    {
        $this->queues = Queue::with(['queueNumbers', 'currentQueueNumber'])
            ->where('status', 'melayani')
            ->get();
    }
    
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
        
        $queuePrefix = Str::of($queue->name)
            ->replaceMatches('/[^a-zA-Z0-9]/', '')
            ->upper();

        $lastQueueNumber = QueueNumber::where('queue_id', $queue->id)
            ->where('queue_number', 'LIKE', $queuePrefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(queue_number, LENGTH(?) + 2) AS UNSIGNED) DESC', [$queuePrefix])
            ->first();

        $nextNumber = 1;
        if ($lastQueueNumber) {
            $parts = explode('-', $lastQueueNumber->queue_number);
            if (count($parts) > 1) {
                $nextNumber = (int)$parts[1] + 1;
            }
        }

        $newQueueNumber = $queuePrefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        // Create the queue number record
        $createdQueue = QueueNumber::create([
            'queue_id' => $queue->id,
            'queue_number' => $newQueueNumber,
            'status' => 'menunggu',
        ]);
        
        $queue->increment('total_queues');
        
        // Hitung estimasi waktu
        $estimatedTime = $this->calculateEstimatedTime($queue);
        
        // Print ticket
        $printResult = $this->printTicket(
            $newQueueNumber,
            $queue->name,
            $createdQueue->created_at->format('d/m/Y H:i:s'),
            $estimatedTime
        );
        
        if ($printResult) {
            Notification::make()
                ->title('Berhasil')
                ->body("Antrian $newQueueNumber berhasil dibuat dan dicetak.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Berhasil (Cetak Gagal)')
                ->body("Antrian $newQueueNumber berhasil dibuat tetapi gagal dicetak.")
                ->warning()
                ->send();
        }
            
        $this->loadQueues();
    }
    
    protected function calculateEstimatedTime(Queue $queue): string
    {
        // Get waiting count directly from database
        $waitingCount = QueueNumber::where('queue_id', $queue->id)
            ->where('status', 'menunggu')
            ->count();
        
        // If there's a queue being served, subtract 1
        if ($queue->currentQueueNumber) {
            $waitingCount = max(0, $waitingCount - 1);
        }
        
        $averageTime = 5; // minutes per customer (default)
        $totalMinutes = $waitingCount * $averageTime;
        
        if ($totalMinutes < 60) {
            return "±{$totalMinutes} menit";
        }
        
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        return "±{$hours} jam {$minutes} menit";
    }
    
    /**
     * Print a queue ticket using mike42/escpos-php
     * 
     * @param string $queueNumber The queue number
     * @param string $queueName The name of the queue
     * @param string $createdAt The creation date and time
     * @param string $estimatedTime The estimated waiting time
     * @return bool Whether printing was successful
     */
    protected function printTicket($queueNumber, $queueName, $createdAt, $estimatedTime): bool
    {
        try {
            // Connect to the printer
            $connector = new WindowsPrintConnector($this->printerPort);
            $printer = new Printer($connector);
            
            // Start printing
            $printer->initialize();
            
            // Print header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("TIKET ANTRIAN\n");
            $printer->setEmphasis(false);
            $printer->text("{$queueName}\n\n");
            
            // Print queue number
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setEmphasis(true);
            $printer->text("Nomor  : {$queueNumber}\n");
            $printer->setEmphasis(false);
            $printer->text("Tanggal: {$createdAt}\n");
            $printer->text("Estimasi: {$estimatedTime}\n");
            $printer->feed(1);
            
            // Print footer
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Terima kasih atas kunjungan Anda\n");
            
            // Feed and cut
            $printer->feed(3);
            $printer->cut();
            $printer->close();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Thermal printer error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Alternative method using serial communication if WindowsPrintConnector doesn't work
     * This can be used as a fallback
     */
    protected function printTicketSerial($queueNumber, $queueName, $createdAt, $estimatedTime): bool
    {
        try {
            // Open COM port with specified baud rate
            $fp = @fopen("\\\\.\\{$this->printerPort}", 'w');
            
            if (!$fp) {
                throw new \Exception("Failed to open printer port. Make sure printer is connected to " . $this->printerPort);
            }
            
            // Configure serial port
            if (function_exists('stream_set_write_buffer')) {
                stream_set_write_buffer($fp, 0);
            }
            
            // Set baud rate if running on Windows
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                exec("mode {$this->printerPort}: BAUD={$this->baudRate} PARITY=N DATA=8 STOP=1 XON=OFF");
            }
            
            // ESC/POS Commands
            $ESC = "\x1B";
            $GS = "\x1D";
            
            $commands = [
                $ESC . '@',                  // Initialize printer
                $ESC . 'a' . "\x01",         // Center alignment
                $ESC . 'E' . "\x01",         // Bold on
                "TIKET ANTRIAN\n",
                $ESC . 'E' . "\x00",         // Bold off
                "{$queueName}\n\n",
                $ESC . 'a' . "\x00",         // Left alignment
                $ESC . 'E' . "\x01",         // Bold on
                "Nomor  : {$queueNumber}\n",
                $ESC . 'E' . "\x00",         // Bold off
                "Tanggal: {$createdAt}\n",
                "Estimasi: {$estimatedTime}\n",
                "\n",
                $ESC . 'a' . "\x01",         // Center alignment
                "Terima kasih atas kunjungan Anda\n",
                "\n\n\n",
                $GS . 'V' . "\x00"           // Cut paper
            ];

            $data = implode('', $commands);
            
            $bytesWritten = fwrite($fp, $data);
            fflush($fp);
            fclose($fp);
            
            if ($bytesWritten === false || $bytesWritten != strlen($data)) {
                throw new \Exception("Failed to write data to printer.");
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Thermal printer error: ' . $e->getMessage());
            return false;
        }
    }
}