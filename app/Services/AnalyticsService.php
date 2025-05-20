<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Analytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\QueueNumber;


class AnalyticsService
{
    /**
     * Create or update analytics entry for today
     * 
     * @param int $queueId The ID of the queue
     * @param string $eventType The type of event (created, called, finished, canceled)
     * @return Analytics
     */
    public static function recordEvent(int $queueId, string $eventType): Analytics
    {
        // Validate queue exists
        $queue = Queue::find($queueId);
        if (!$queue) {
            throw new \Exception("Queue with ID {$queueId} not found");
        }
        
        $today = Carbon::today();
        
        // Get or create today's analytics record
        $analytics = Analytics::firstOrNew([
            'queue_id' => $queueId,
            'date' => $today,
            'period_type' => 'daily'
        ]);
        
        // If this is a new record, set up initial values
        if (!$analytics->exists) {
            $analytics->period_label = $today->format('Y-m-d');
            $analytics->total_queue_created = 0;
            $analytics->total_queue_called = 0;
            $analytics->total_queue_finished = 0;
            $analytics->total_queue_canceled = 0;
        }
        
        // Increment the appropriate counter
        switch ($eventType) {
            case 'created':
                $analytics->total_queue_created++;
                break;
            case 'called':
                $analytics->total_queue_called++;
                break;
            case 'finished':
                $analytics->total_queue_finished++;
                break;
            case 'canceled':
                $analytics->total_queue_canceled++;
                break;
            default:
                throw new \Exception("Invalid event type: {$eventType}");
        }
        
        // Save the record
        $analytics->save();
        
        // Update waiting and service time averages
        self::updateServiceTimeMetrics($queueId, $today);
        
        return $analytics;
    }
    
    /**
     * Update service time metrics (avg_wait_time and avg_service_time)
     * 
     * @param int $queueId The ID of the queue
     * @param Carbon $date The date to update metrics for
     * @return void
     */
    private static function updateServiceTimeMetrics(int $queueId, Carbon $date): void
    {
        // Calculate average wait time (time between creation and being called)
        $avgWaitTime = DB::table('queue_numbers')
            ->where('queue_id', $queueId)
            ->whereDate('created_at', $date)
            ->whereNotNull('called_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait_time'))
            ->first();
        
        // Calculate average service time (time between being called and finishing)
        $avgServiceTime = DB::table('queue_numbers')
            ->where('queue_id', $queueId)
            ->whereDate('created_at', $date)
            ->whereNotNull('called_at')
            ->whereNotNull('finished_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, called_at, finished_at)) as avg_service_time'))
            ->first();
        
        // Update the analytics record
        $analytics = Analytics::where('queue_id', $queueId)
            ->where('date', $date->format('Y-m-d'))
            ->where('period_type', 'daily')
            ->first();
        
        if ($analytics) {
            $analytics->avg_wait_time = $avgWaitTime ? $avgWaitTime->avg_wait_time : null;
            $analytics->avg_service_time = $avgServiceTime ? $avgServiceTime->avg_service_time : null;
            $analytics->save();
        }
    }
    
    /**
     * Generate analytics report for a given date, period, and queue (optional)
     */
    public static function generate(Carbon $date, string $periodType = 'daily', $queueId = null)
    {
        // Ambil queue yang dipilih, atau semua jika null
        $queues = $queueId ? [Queue::find($queueId)] : Queue::all();

        foreach ($queues as $queue) {
            if (!$queue) continue;

            // Hitung data sesuai kebutuhan, contoh sederhana:
            $created = QueueNumber::where('queue_id', $queue->id)
                ->whereDate('created_at', $date)
                ->count();

            $called = QueueNumber::where('queue_id', $queue->id)
                ->whereDate('called_at', $date)
                ->count();

            $finished = QueueNumber::where('queue_id', $queue->id)
                ->whereDate('finished_at', $date)
                ->count();

            // Simpan ke analytics
            Analytics::updateOrCreate(
                [
                    'queue_id' => $queue->id,
                    'date' => $date->format('Y-m-d'),
                    'period_type' => $periodType,
                ],
                [
                    'period_label' => $date->format('Y-m-d'),
                    'total_queue_created' => $created,
                    'total_queue_called' => $called,
                    'total_queue_finished' => $finished,
                ]
            );
        }
    }
}