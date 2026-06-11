<?php

declare(strict_types=1);

namespace AIArmada\FilamentEngagement\Resources;

use AIArmada\Engagement\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subscriber_type')->badge(),
                Tables\Columns\TextColumn::make('subscriber_id'),
                Tables\Columns\TextColumn::make('subscribable_type')->badge()->placeholder('-'),
                Tables\Columns\TextColumn::make('subscribable_id'),
                Tables\Columns\TextColumn::make('subscription_type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('notification_level')->badge(),
                Tables\Columns\TextColumn::make('subscribed_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_type'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('notification_level'),
            ])
            ->actions([
                Tables\Actions\Action::make('mute')
                    ->action(fn (Subscription $record) => $record->update(['status' => Subscription::STATUS_MUTED]))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unmute')
                    ->action(fn (Subscription $record) => $record->update(['status' => Subscription::STATUS_ACTIVE]))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unsubscribe')
                    ->action(fn (Subscription $record) => $record->update(['status' => Subscription::STATUS_UNSUBSCRIBED]))
                    ->requiresConfirmation(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Subscriber Information')->columns(2)->schema([
                    Infolists\Components\TextEntry::make('subscriber_type'),
                    Infolists\Components\TextEntry::make('subscriber_id'),
                ]),
                Infolists\Components\Section::make('Subscription Details')->columns(2)->schema([
                    Infolists\Components\TextEntry::make('subscribable_type'),
                    Infolists\Components\TextEntry::make('subscribable_id'),
                    Infolists\Components\TextEntry::make('subscription_type'),
                    Infolists\Components\TextEntry::make('status'),
                    Infolists\Components\TextEntry::make('subscribed_at')->dateTime(),
                    Infolists\Components\TextEntry::make('expires_at')->dateTime(),
                    Infolists\Components\TextEntry::make('muted_at')->dateTime(),
                    Infolists\Components\TextEntry::make('unsubscribed_at')->dateTime(),
                ]),
                Infolists\Components\Section::make('Criteria')
                    ->visible(fn (Subscription $record): bool => ! empty($record->criteria))
                    ->schema([
                        Infolists\Components\TextEntry::make('criteria')->json(),
                    ]),
                Infolists\Components\Section::make('Notification Preferences')->columns(2)->schema([
                    Infolists\Components\TextEntry::make('notification_level'),
                    Infolists\Components\TextEntry::make('notification_preferences')->json(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subscriber_type'),
                Forms\Components\TextInput::make('subscriber_id'),
                Forms\Components\TextInput::make('subscribable_type'),
                Forms\Components\TextInput::make('subscribable_id'),
                Forms\Components\TextInput::make('subscription_type'),
                Forms\Components\TextInput::make('status'),
                Forms\Components\TextInput::make('notification_level'),
                Forms\Components\DateTimePicker::make('subscribed_at'),
                Forms\Components\DateTimePicker::make('expires_at'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SubscriptionResource\Pages\ListSubscriptions::route('/'),
            'view' => SubscriptionResource\Pages\ViewSubscription::route('/{record}'),
        ];
    }
}
