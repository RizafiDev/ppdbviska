<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsResource\Pages;
use App\Filament\Resources\AnalyticsResource\RelationManagers;
use App\Models\Analytics;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Queue;
use Carbon\Carbon;

class AnalyticsResource extends Resource
{
    protected static ?string $model = Analytics::class;

    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal Laporan')
                    ->required(),

                Forms\Components\Select::make('period_type')
                    ->label('Tipe Periode')
                    ->options([
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                    ])
                    ->default('daily')
                    ->required(),

                Forms\Components\Select::make('queue_id')
                    ->label('Tempat Layanan')
                    ->options(Queue::all()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->helperText('Kosongkan untuk semua tempat'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->label('Tanggal Laporan'),
                Tables\Columns\TextColumn::make('queue.name')->label('Tempat Layanan'),
                Tables\Columns\TextColumn::make('period_type')->label('Tipe Periode'),
                Tables\Columns\TextColumn::make('total_queue_created')->label('Total Antrian Dibuat'),
                Tables\Columns\TextColumn::make('avg_service_time')->label('Rata-rata Waktu Layanan')->suffix(' menit'),
                
            ])
            ->filters([
                //
            ])
            
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalytics::route('/'),
            'create' => Pages\CreateAnalytics::route('/create'),
            'edit' => Pages\EditAnalytics::route('/{record}/edit'),
        ];
    }
}