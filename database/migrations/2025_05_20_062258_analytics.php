<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->nullable()->constrained()->onDelete('cascade'); // Tempat layanan
            $table->date('date'); // Tanggal laporan (harian)

            $table->unsignedInteger('total_queue_created')->default(0); // Jumlah antrean dibuat
            $table->unsignedInteger('total_queue_called')->default(0);  // Jumlah yang dipanggil
            $table->unsignedInteger('total_queue_finished')->default(0); // Jumlah yang selesai
            $table->unsignedInteger('total_queue_canceled')->default(0); // Jika ada fitur pembatalan

            $table->decimal('avg_wait_time', 5, 2)->nullable(); // Waktu tunggu rata-rata (dalam menit)
            $table->decimal('avg_service_time', 5, 2)->nullable(); // Waktu pelayanan rata-rata (dalam menit)
            $table->string('period_type')->default('daily'); // daily, weekly, monthly
            $table->string('period_label')->nullable(); // 2025-05-20, 2025-W21, 2025-05

            $table->timestamps();
            $table->unique(['queue_id', 'date', 'period_type']); // Agar tidak duplikat laporan untuk queue + date + period_type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics'); // Fixed to drop the analytics table
    }
};