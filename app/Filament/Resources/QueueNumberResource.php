<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueNumberResource\Pages;
use App\Filament\Resources\QueueNumberResource\RelationManagers;
use App\Models\QueueNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Enums\Alignment;


class QueueNumberResource extends Resource
{
    protected static ?string $model = QueueNumber::class;

    protected static ?string $navigationGroup = 'Antrian';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Nomor Antrian';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Select::make('queue_id')
                ->relationship('queue', 'name')
                ->required(),
            Forms\Components\TextInput::make('queue_number')->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'menunggu' => 'Menunggu',
                    'dipanggil' => 'Dipanggil',
                    'selesai' => 'Selesai',
                    'batal' => 'Batal',
                ])
                ->default('menunggu')
                ->required(),
            Forms\Components\DateTimePicker::make('called_at')->nullable(),
            Forms\Components\DateTimePicker::make('finished_at')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('queue.name')->label('Queue'),
            Tables\Columns\TextColumn::make('queue_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('status')
            ->sortable()
            ->searchable()
            ->badge()
            ->color(fn (string $state): string => match ($state) {
            'menunggu' => 'gray',
            'dipanggil' => 'warning',
            'selesai' => 'success',
            'batal' => 'danger',
            }), 
            // Tables\Columns\TextColumn::make('called_at')->dateTime()->sortable(),
            // Tables\Columns\TextColumn::make('finished_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('queue_id')
                ->relationship('queue', 'name')
                ->label('Filter Layanan'),
                SelectFilter::make('status')
                ->options([
                    'menunggu' => 'Menunggu',
                    'dipanggil' => 'Dipanggil',
                    'selesai' => 'Selesai',
                    'batal' => 'Batal',
                ])
                ->label('Filter Status'),
            ])
            ->actionsAlignment('start')
            ->actions([
                Tables\Actions\Action::make('set_menunggu')
        ->label('Menunggu')
        ->color('gray')
        ->icon('heroicon-o-clock')
        ->action(fn ($record) => $record->update(['status' => 'menunggu']))
        ->visible(fn ($record) => $record->status !== 'menunggu'),

    Tables\Actions\Action::make('set_dipanggil')
        ->label('Dipanggil')
        ->color('warning')
        ->icon('heroicon-o-arrow-right-circle')
        ->action(fn ($record) => $record->update([
            'status' => 'dipanggil',
            'called_at' => now(),
        ]))
        ->visible(fn ($record) => $record->status !== 'dipanggil'),

    Tables\Actions\Action::make('set_selesai')
        ->label('Selesai')
        ->color('success')
        ->icon('heroicon-o-check-circle')
        ->action(fn ($record) => $record->update([
            'status' => 'selesai',
            'finished_at' => now(),
        ]))
        ->visible(fn ($record) => $record->status !== 'selesai'),

    Tables\Actions\Action::make('set_batal')
        ->label('Batal')
        ->color('danger')
        ->icon('heroicon-o-x-circle')
        ->action(fn ($record) => $record->update(['status' => 'batal']))
        ->visible(fn ($record) => $record->status !== 'batal'),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                
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
            'index' => Pages\ListQueueNumbers::route('/'),
            'create' => Pages\CreateQueueNumber::route('/create'),
            'edit' => Pages\EditQueueNumber::route('/{record}/edit'),
        ];
    }
}
