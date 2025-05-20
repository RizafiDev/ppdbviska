<?php

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\Queue;
use Carbon\Carbon;

class ListAnalytics extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('generate')
                ->label('Generate Laporan')
                ->action(function (array $data) {
                    \App\Services\AnalyticsService::generate(
                        \Carbon\Carbon::parse($data['date']),
                        $data['period_type'],
                        $data['queue_id'] ?? null,
                    );

                    Notification::make()
                        ->title('Laporan berhasil digenerate')
                        ->success()
                        ->send();
                })
                ->form([
                    \Filament\Forms\Components\DatePicker::make('date')
                        ->label('Tanggal Laporan')
                        ->required(),

                    \Filament\Forms\Components\Select::make('period_type')
                        ->label('Tipe Periode')
                        ->options([
                            'daily' => 'Harian',
                            'weekly' => 'Mingguan',
                            'monthly' => 'Bulanan',
                        ])
                        ->default('daily')
                        ->required(),

                    \Filament\Forms\Components\Select::make('queue_id')
                        ->label('Tempat Layanan')
                        ->options(\App\Models\Queue::all()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->helperText('Kosongkan untuk semua tempat'),
                ])
                ->color('primary'),
        ];
    }
}