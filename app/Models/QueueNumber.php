<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class QueueNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'queue_number',
        'status',
        'called_at',
        'finished_at',
    ];
    
    

    // relasi ke model Queue
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
    public function queueNumbers()
{
    return $this->hasMany(QueueNumber::class)->latest();
}
public function currentQueueNumber()
{
    return $this->hasOne(QueueNumber::class)
        ->where('status', 'dipanggil')
        ->latestOfMany(); // Ambil yang terbaru berdasarkan created_at
}

// generate QR code
public function getStatusCheckUrlAttribute()
    {
        // URL yang akan di-encode dalam QR code
        // Bisa disesuaikan dengan domain frontend Anda
        return config('app.frontend_url', 'http://localhost:3000') . '/check-status/' . $this->id;
    }

    // Generate QR code PNG sebagai base64
    public function getQrCodeDataAttribute()
    {
        $qr = new EndroidQrCode($this->status_check_url);
        $qr->setSize(150);

        $writer = new PngWriter();
        $result = $writer->write($qr);

        return $result->getString(); // PNG binary
    }

    // Generate QR code SVG string
    public function getQrCodeSvg()
    {
        $qr = new EndroidQrCode($this->status_check_url);
        $qr->setSize(100);

        $writer = new SvgWriter();
        $result = $writer->write($qr);

        return $result->getString(); // SVG string
    }

    public function getQrCodeForPrinter()
    {
        return [
            'url' => $this->status_check_url,
            'svg' => $this->getQrCodeSvg(),
            'raw_data' => $this->status_check_url
        ];
    }
}