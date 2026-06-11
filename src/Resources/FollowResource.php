<?php

declare(strict_types=1);

namespace AIArmada\FilamentEngagement\Resources;

use AIArmada\Engagement\Models\Follow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class FollowResource extends Resource
{
    protected static ?string $model = Follow::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest('followed_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('follower_type')->label('Follower Type')->badge(),
                Tables\Columns\TextColumn::make('follower_id')->label('Follower ID'),
                Tables\Columns\TextColumn::make('followable_type')->label('Followable Type')->badge(),
                Tables\Columns\TextColumn::make('followable_id')->label('Followable ID'),
                Tables\Columns\TextColumn::make('status')->badge()->colors([
                    'success' => 'active',
                    'warning' => 'muted',
                    'danger' => 'unfollowed',
                    'gray' => 'blocked',
                ]),
                Tables\Columns\TextColumn::make('notification_level')->badge(),
                Tables\Columns\TextColumn::make('followed_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('unfollowed_at')->dateTime()->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'muted' => 'Muted',
                    'unfollowed' => 'Unfollowed',
                    'blocked' => 'Blocked',
                ]),
                Tables\Filters\SelectFilter::make('follower_type')->options(['App\Models\User' => 'User'])->label('Follower Type'),
                Tables\Filters\SelectFilter::make('followable_type')->options(['App\Models\User' => 'User'])->label('Followable Type'),
            ])
            ->actions([
                Tables\Actions\Action::make('mute')
                    ->action(fn (Follow $record) => $record->update(['status' => 'muted']))
                    ->requiresConfirmation()
                    ->visible(fn (Follow $record) => $record->isActive()),
                Tables\Actions\Action::make('unmute')
                    ->action(fn (Follow $record) => $record->update(['status' => 'active']))
                    ->requiresConfirmation()
                    ->visible(fn (Follow $record) => $record->isMuted()),
                Tables\Actions\Action::make('unfollow')
                    ->action(fn (Follow $record) => $record->update(['status' => 'unfollowed']))
                    ->requiresConfirmation()
                    ->visible(fn (Follow $record) => $record->isActive()),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mute')
                    ->action(fn ($records) => $records->each->update(['status' => 'muted'])),
                Tables\Actions\BulkAction::make('unfollow')
                    ->action(fn ($records) => $records->each->update(['status' => 'unfollowed'])),
            ])
            ->defaultSort('followed_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('follower_type'),
                Infolists\Components\TextEntry::make('follower_id'),
                Infolists\Components\TextEntry::make('followable_type'),
                Infolists\Components\TextEntry::make('followable_id'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('notification_level'),
                Infolists\Components\TextEntry::make('followed_at')->dateTime(),
                Infolists\Components\TextEntry::make('muted_at')->dateTime(),
                Infolists\Components\TextEntry::make('unfollowed_at')->dateTime(),
                Infolists\Components\TextEntry::make('metadata')->json()
                    ->visible(fn (?array $state): bool => ! empty($state)),
            ])->columns(2),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => FollowResource\Pages\ListFollows::route('/'),
        ];
    }
}
