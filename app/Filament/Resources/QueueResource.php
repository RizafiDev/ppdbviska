<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueResource\Pages;
use App\Filament\Resources\QueueResource\RelationManagers;
use App\Models\Queue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Antrian';
    protected static ?string $navigationLabel = 'Tempat Layanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
           Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('status')
                ->options(Queue::STATUSES)
                ->required(),

            Forms\Components\Select::make('current_queue_id')
                ->relationship('currentQueue', 'queue_number')
                ->label('Nomor Antrian Saat Ini')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\TextInput::make('total_queues')
                ->numeric()
                ->disabled()
                ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'melayani' => 'success',
                    'istirahat' => 'warning',
                    'libur' => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('currentQueue.queue_number')
                ->label('Antrian Saat Ini'),
            Tables\Columns\TextColumn::make('total_queues')
                ->label('Total Antrian')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Dibuat'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('set_melayani')
        ->label('Melayani')
        ->color('success')
        ->icon('heroicon-o-check-circle')
        ->action(function ($record) {
            $record->update(['status' => 'melayani']);
        })
        ->visible(fn ($record) => $record->status !== 'melayani'),

    Tables\Actions\Action::make('set_istirahat')
        ->label('Istirahat')
        ->color('warning')
        ->icon('heroicon-o-pause')
        ->action(function ($record) {
            $record->update(['status' => 'istirahat']);
        })
        ->visible(fn ($record) => $record->status !== 'istirahat'),

    Tables\Actions\Action::make('set_libur')
        ->label('Libur')
        ->color('danger')
        ->icon('heroicon-o-x-circle')
        ->action(function ($record) {
            $record->update(['status' => 'libur']);
        })
        ->visible(fn ($record) => $record->status !== 'libur'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'edit' => Pages\EditQueue::route('/{record}/edit'),
        ];
    }
}
