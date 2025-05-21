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
use Illuminate\Database\Eloquent\Builder;

class ListAnalytics extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;
    
    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticWidget::class,
        ];
    }

    // Method yang benar untuk menambahkan filter di Filament v3
    public function getTableFilters(): array
    {
        return [
            Filter::make('tanggal_laporan')
                ->form([
                    DatePicker::make('tanggal_laporan')
                        ->label('Tanggal Laporan'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['tanggal_laporan'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_laporan', $date),
                        );
                }),

            SelectFilter::make('periode')
                ->options([
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $periode): Builder => $query->where('periode', $periode),
                        );
                }),

            SelectFilter::make('tempat_layanan_id')
                ->label('Tempat Layanan')
                ->options(Queue::all()->pluck('name', 'id'))
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $id): Builder => $query->where('tempat_layanan_id', $id),
                        );
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate')
                ->label('Generate Laporan')
                ->action(function (array $data) {
                    \App\Services\AnalyticsService::generate(
                        Carbon::parse($data['date']),
                        $data['period_type'],
                        $data['queue_id'] ?? null,
                    );

                    Notification::make()
                        ->title('Laporan berhasil digenerate')
                        ->success()
                        ->send();
                })
                ->form([
                    DatePicker::make('date')
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
                        ->options(Queue::all()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->helperText('Kosongkan untuk semua tempat'),
                ])
                ->color('primary'),
        ];
    }
}