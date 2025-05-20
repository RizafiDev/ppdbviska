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
           
        ];
    }
}
