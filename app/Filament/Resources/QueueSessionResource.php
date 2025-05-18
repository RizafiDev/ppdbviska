<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueSessionResource\Pages;
use App\Filament\Resources\QueueSessionResource\RelationManagers;
use App\Models\QueueSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueSessionResource extends Resource
{
    protected static ?string $model = QueueSession::class;

    protected static ?string $navigationGroup = 'Antrian';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Sesi Antrian';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Select::make('queue_id')
            ->relationship('queue', 'name')
            ->required(),
            Forms\Components\DatePicker::make('date')->required(),
            Forms\Components\DateTimePicker::make('start_time')->nullable(),
            Forms\Components\DateTimePicker::make('end_time')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('queue.name')->label('Queue'),
            Tables\Columns\TextColumn::make('date')->date(),
            Tables\Columns\TextColumn::make('start_time')->dateTime(),
            Tables\Columns\TextColumn::make('end_time')->dateTime(),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
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
            'index' => Pages\ListQueueSessions::route('/'),
            'create' => Pages\CreateQueueSession::route('/create'),
            'edit' => Pages\EditQueueSession::route('/{record}/edit'),
        ];
    }
}
