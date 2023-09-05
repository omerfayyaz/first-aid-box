<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Filament\Resources\MedicineResource\RelationManagers;
use App\Filament\Resources\MedicineResource\RelationManagers\TagsRelationManager;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    protected static array $statuses = [
        'available' => 'available',
        'run out' => 'run out',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('price')
                    ->rule('numeric'),

                Radio::make('status')
                    ->options(self::$statuses),

                Select::make('category')
                    ->relationship('category', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->sortable()
                    ->money('pkr'),

                TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        return match($state) {
                            'available' => 'success',
                            'run out' => 'danger',
                        };
                    }),

                TextColumn::make('category.name'),

                TextColumn::make('tags.name')
                    ->badge(),

            ])

            ->defaultSort('name', 'ASC')

            ->filters([
                SelectFilter::make('status')
                    ->options(self::$statuses),

                SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'edit' => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
