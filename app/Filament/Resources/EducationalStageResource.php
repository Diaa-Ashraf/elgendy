<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EducationalStageResource\Pages;
use App\Models\EducationalStage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EducationalStageResource extends Resource
{
    protected static ?string $model = EducationalStage::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $modelLabel = 'المرحلة الدراسية';

    protected static ?string $pluralModelLabel = 'المراحل الدراسية';

    protected static ?string $navigationLabel = 'المراحل الدراسية';

    protected static ?string $navigationGroup = 'الإعدادات الأكاديمية';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المرحلة الدراسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المرحلة')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: المرحلة الابتدائية'),

                        Forms\Components\TextInput::make('order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->placeholder('0'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width(60),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المرحلة')
                    ->searchable()
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->icon('heroicon-m-academic-cap'),

                Tables\Columns\TextColumn::make('order')
                    ->label('الترتيب')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('groups_count')
                    ->label('عدد المجموعات')
                    ->counts('groups')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->striped()
            ->defaultPaginationPageOption(15)
            ->paginationPageOptions([10, 15, 25, 50])
            ->defaultSort('order')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل')->icon('heroicon-m-pencil-square'),
                Tables\Actions\DeleteAction::make()->label('حذف')->icon('heroicon-m-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
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
            'index' => Pages\ListEducationalStages::route('/'),
            'create' => Pages\CreateEducationalStage::route('/create'),
            'edit' => Pages\EditEducationalStage::route('/{record}/edit'),
        ];
    }
}
