<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use App\Models\QueueNumber;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class Antrian extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.antrian';
    protected int | string | array $columnSpan = 'full';

    public $queues;
    public $selectedPort;
    
    // Printer settings
    protected $baudRate = 9600;  // Common baud rate for thermal printers
    
    public function mount()
{
    $this->loadQueues();

    // Hapus default port COM3
    $this->form->fill([
        'selectedPort' => null,
    ]);
}

    
    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedPort')
                ->label('Pilih Port Printer')
                ->options($this->getAvailableSerialPorts())
                ->required()
                ->searchable(),
        ];
    }
    
    public function loadQueues()
    {
        $this->queues = Queue::with(['queueNumbers', 'currentQueueNumber'])
            ->where('status', 'melayani')
            ->get();
    }
    
    public function createQueueNumber($queueId)
    {
        $data = $this->form->getState();
        $this->selectedPort = $data['selectedPort'] ?? null;
        
        $queue = Queue::find($queueId);
        
        if (!$queue) {
            Notification::make()
                ->title('Error')
                ->body('Tempat layanan tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }
        if (!$this->selectedPort) {
        Notification::make()
            ->title('Pilih Port Printer')
            ->body('Silakan pilih port printer terlebih dahulu sebelum mencetak.')
            ->warning()
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
        $printResult = $this->printToThermalPrinter(
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
    
    public function getAvailableSerialPorts(): array
    {
        $ports = [];
        
        // Windows - menggunakan perintah mode
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            @exec('mode', $output);

            foreach ($output as $line) {
                if (preg_match('/^COM\d+:/', $line, $matches)) {
                    $port = rtrim($matches[0], ':');
                    $ports[$port] = $port;
                }
            }
        } else {
            // Linux/Mac - cek device files
            $deviceFiles = glob('/dev/tty*');
            foreach ($deviceFiles as $device) {
                if (preg_match('/\/dev\/tty(USB|ACM|S)/', $device)) {
                    $ports[$device] = $device;
                }
            }
        }
        
        // Tambahkan port default jika tidak ada yang terdeteksi
        if (empty($ports)) {
            $ports = [
                'COM3' => 'COM3',
                'COM4' => 'COM4',
                '/dev/ttyUSB0' => '/dev/ttyUSB0'
            ];
        }
        
        return $ports;
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
    
    protected function printToThermalPrinter($queueNumber, $queueName, $createdAt, $estimatedTime): bool
    {
        if (empty($this->selectedPort)) {
            \Log::error('No printer port selected');
            return false;
        }

        try {
            $ESC = "\x1B";
            $GS  = "\x1D";

            // Format ESC/POS commands
            $commands = [
                $ESC . '@',                  // Initialize printer
                $ESC . 'a' . "\x01",         // Center alignment
                $ESC . 'E' . "\x01",         // Bold on
                "TIKET ANTRIAN\n",
                $ESC . 'E' . "\x00",         // Bold off
                "{$queueName}\n",
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

            $fp = @fopen("\\\\.\\{$this->selectedPort}", 'w');
            if (!$fp) {
                throw new \Exception("Gagal membuka port printer. Pastikan printer terhubung ke " . $this->selectedPort);
            }

            // Set binary mode
            if (function_exists('stream_set_write_buffer')) {
                stream_set_write_buffer($fp, 0);
            }

            $bytesWritten = fwrite($fp, $data);
            fflush($fp);
            fclose($fp);

            if ($bytesWritten === false || $bytesWritten != strlen($data)) {
                throw new \Exception("Gagal menulis data ke printer.");
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Thermal printer error: ' . $e->getMessage());
            return false;
        }
    }
}