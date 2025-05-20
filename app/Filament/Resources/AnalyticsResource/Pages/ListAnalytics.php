<?php

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use App\Filament\Resources\AnalyticsResource\Widgets\AnalyticWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\Queue;
use Carbon\Carbon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;


class ListAnalytics extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;
    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticWidget::class,
        ];
    }
    protected function getFilters(): array
    {
        return [
            // Filter Tanggal
            Filter::make('date')
                ->form([
                    DatePicker::make('date')
                        ->label('Tanggal')
                ])
                ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                    if ($data['date']) {
                        $query->whereDate('created_at', $data['date']);
                    }
                }),

            // Filter Tempat Layanan (Queue)
            SelectFilter::make('queue_id')
                ->label('Tempat Layanan')
                ->options(
                    Queue::all()->pluck('name', 'id')
                )
                ->searchable(),

            // Filter Periode
            SelectFilter::make('period_type')
                ->label('Tipe Periode')
                ->options([
                    'daily' => 'Harian',
                    'weekly' => 'Mingguan',
                    'monthly' => 'Bulanan',
                ]),
        ];
    }

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