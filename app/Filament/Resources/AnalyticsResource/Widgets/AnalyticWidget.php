<?php

namespace App\Filament\Resources\AnalyticsResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class AnalyticWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pengunjung', \App\Models\QueueNumber::count())
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
            Stat::make('Total Tempat Layanan', \App\Models\Queue::count())
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
            Stat::make('Total Panitia Aktif', \App\Models\User::where('status', 'aktif')->count())
            ->description('User yang saat ini aktif')
            ->descriptionIcon('heroicon-m-check-circle')
            ->color('success')
            ->chart([5, 10, 15, 20, 25, 30, 35]),
        ];
    }
}
