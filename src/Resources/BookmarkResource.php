<?php

declare(strict_types=1);

namespace AIArmada\FilamentEngagement\Resources;

use AIArmada\Engagement\Models\Bookmark;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class BookmarkResource extends Resource
{
    protected static ?string $model = Bookmark::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest('bookmarked_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bookmarker_type')->label('Bookmarker Type')->badge(),
                Tables\Columns\TextColumn::make('bookmarker_id')->label('Bookmarker ID'),
                Tables\Columns\TextColumn::make('bookmarkable_type')->label('Bookmarkable Type')->badge(),
                Tables\Columns\TextColumn::make('bookmarkable_id')->label('Bookmarkable ID'),
                Tables\Columns\TextColumn::make('status')->badge()->colors([
                    'success' => 'active',
                    'danger' => 'removed',
                    'warning' => 'archived',
                ]),
                Tables\Columns\TextColumn::make('notes')->limit(50),
                Tables\Columns\TextColumn::make('bookmarked_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('removed_at')->dateTime()->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'removed' => 'Removed',
                    'archived' => 'Archived',
                ]),
                Tables\Filters\SelectFilter::make('bookmarker_type')->options(['App\Models\User' => 'User'])->label('Bookmarker Type'),
                Tables\Filters\SelectFilter::make('bookmarkable_type')->options(['App\Models\User' => 'User'])->label('Bookmarkable Type'),
            ])
            ->actions([
                Tables\Actions\Action::make('remove')
                    ->action(fn (Bookmark $record) => $record->update(['status' => 'removed']))
                    ->requiresConfirmation()
                    ->visible(fn (Bookmark $record) => $record->isActive()),
                Tables\Actions\Action::make('restore')
                    ->action(fn (Bookmark $record) => $record->update(['status' => 'active']))
                    ->requiresConfirmation()
                    ->visible(fn (Bookmark $record) => $record->isRemoved()),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('remove')
                    ->action(fn ($records) => $records->each->update(['status' => 'removed'])),
            ])
            ->defaultSort('bookmarked_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('bookmarker_type'),
                Infolists\Components\TextEntry::make('bookmarker_id'),
                Infolists\Components\TextEntry::make('bookmarkable_type'),
                Infolists\Components\TextEntry::make('bookmarkable_id'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('notes'),
                Infolists\Components\TextEntry::make('bookmarked_at')->dateTime(),
                Infolists\Components\TextEntry::make('removed_at')->dateTime(),
                Infolists\Components\TextEntry::make('archived_at')->dateTime(),
                Infolists\Components\TextEntry::make('source'),
                Infolists\Components\TextEntry::make('metadata')->json()
                    ->visible(fn (?array $state): bool => ! empty($state)),
            ])->columns(2),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => BookmarkResource\Pages\ListBookmarks::route('/'),
            'view' => BookmarkResource\Pages\ViewBookmark::route('/{record}'),
        ];
    }
}
