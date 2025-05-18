<x-filament-widgets::widget>
    <x-filament::section>
        <div class="pb-4 pt-4">
            <h2 class="text-xl font-bold tracking-tight text-gray-800 dark:text-gray-200 px-6">
                Daftar Tempat Layanan
            </h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6 px-6 pb-6 w-full">
            @foreach ($queues as $queue)
                <div 
                    wire:click="createQueueNumber('{{ $queue->id }}')"
                    class="bg-white rounded-lg border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700 hover:shadow-lg transition-shadow duration-200 cursor-pointer"
                    :style="'border-left: 4px solid ' . ($queue->status === 'active' ? '#10B981' : '#EF4444')"
                >
                    <div class="p-4 space-y-3">
                        <h3 class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                            {{ $queue->name }}
                        </h3>
                        
                        <div class="flex items-center gap-2 text-sm">
                            @php
                                $statusClass = match($queue->status) {
                                    'menunggu' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                    'dipanggil' => 'bg-amber-100 text-amber-800 dark:bg-amber-700 dark:text-amber-200',
                                    'selesai' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-700 dark:text-emerald-200',
                                    'batal' => 'bg-rose-100 text-rose-800 dark:bg-rose-700 dark:text-rose-200',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-md text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($queue->status) }}
                            </span>
                        </div>
                        
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
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
