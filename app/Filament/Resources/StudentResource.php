<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $modelLabel = 'الطالب';

    protected static ?string $pluralModelLabel = 'الطلاب';

    protected static ?string $navigationLabel = 'الطلاب';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('البيانات الشخصية للطالب')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الطالب الثلاثي / الرباعي')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: أحمد محمد علي'),

                        Forms\Components\Select::make('stage_id')
                            ->label('المرحلة الدراسية')
                            ->relationship('educationalStage', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('gender')
                            ->label('النوع')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد')
                            ->native(false)
                            ->displayFormat('Y-m-d'),

                        Forms\Components\Select::make('discount_id')
                            ->label('الخصم المطبق على الطالب (إن وجد)')
                            ->relationship('discount', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('بيانات التواصل')
                    ->schema([
                        Forms\Components\TextInput::make('parent_phone')
                            ->label('رقم هاتف ولي الأمر')
                            ->required()
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('01xxxxxxxx'),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم هاتف الطالب (إن وجد)')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('01xxxxxxxx'),

                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(2)
                            ->columnSpanFull(),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('qr_code')
                    ->label('كود QR')
                    ->badge()
                    ->color('warning')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة الدراسية')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('parent_phone')
                    ->label('هاتف ولي الأمر')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('هاتف الطالب')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('gender')
                    ->label('النوع')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('groups_count')
                    ->label('عدد المجموعات')
                    ->counts('groups')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage_id')
                    ->label('المرحلة الدراسية')
                    ->relationship('educationalStage', 'name'),

                Tables\Filters\SelectFilter::make('gender')
                    ->label('النوع')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ]),

                Tables\Filters\TrashedFilter::make()
                    ->label('الطلاب المحذوفين'),
            ])
            ->actions([
                Tables\Actions\Action::make('printCard')
                    ->label('كارنيه الطالب 🎴')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->url(fn (Student $record): string => route('student.card.print', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('ledger')
                    ->label('كشف الحساب')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->color('info')
                    ->url(fn (Student $record): string => static::getUrl('ledger', ['record' => $record])),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                    Tables\Actions\ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    Tables\Actions\RestoreBulkAction::make()->label('استعادة'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StudentResource\RelationManagers\GroupsRelationManager::class,
            StudentResource\RelationManagers\PaymentsRelationManager::class,
            StudentResource\RelationManagers\ExamResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'ledger' => Pages\StudentLedger::route('/{record}/ledger'),
        ];
    }
}
