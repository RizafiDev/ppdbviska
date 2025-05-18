<x-filament-widgets::widget>
    <x-filament::section>
        <div class="pb-4 pt-4">
            <h2 class="text-xl font-bold  text-gray-800 dark:text-gray-200 px-6">
                Daftar Tempat Layanan
            </h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6 px-6 pb-6 w-full">
            @foreach ($queues as $queue)
                <div 
                    wire:click="createQueueNumber('{{ $queue->id }}')"
                    class="bg-white rounded-lg border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700 hover:shadow-lg transition-shadow duration-200 cursor-pointer"
                >
                    <div class="space-y-3 p-6">
                        <h3 class="text-xl font-bold text-primary-600 dark:text-primary-400">
                            {{ $queue->name }}
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nomor Antrian Saat Ini</p>
                               <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
    {{ optional($queue->currentQueueNumber)->queue_number ?? '-' }}
</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nomor Antrian Terakhir</p>
                                @php
                                    $lastWaitingQueue = $queue->queueNumbers
                                        ->where('status', 'menunggu')
                                        ->sortByDesc('created_at')
                                        ->first();
                                @endphp

                                @if ($lastWaitingQueue)
                                    <p class="text-xl font-semibold text-gray-700 dark:text-gray-200">
                                        {{ $lastWaitingQueue->queue_number }}
                                    </p>
                                @else
                                    <p class="text-xl font-semibold text-gray-700 dark:text-gray-200">-</p>
                                @endif
                            </div>
                             <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sisa Antrian</p>
                               @php
    $sisaAntrian = $queue->queueNumbers
        ->where('status', 'menunggu')
        ->count();

    // Kurangi 1 jika ada currentQueueNumber
    if ($queue->currentQueueNumber) {
        $sisaAntrian = max(0, $sisaAntrian - 1);
    }
@endphp

<p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
    {{ $sisaAntrian }}
</p>

                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
