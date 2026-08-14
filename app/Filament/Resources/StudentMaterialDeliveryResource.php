<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentMaterialDeliveryResource\Pages;
use App\Models\StudentMaterialDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentMaterialDeliveryResource extends Resource
{
    protected static ?string $model = StudentMaterialDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $modelLabel = 'تسليم ملزمة لطالب';

    protected static ?string $pluralModelLabel = 'سجل تسليم الملازم والمدفوعات';

    protected static ?string $navigationLabel = 'سجل الملازم والمدفوعات';

    protected static ?string $navigationGroup = 'المالية والمخازن';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return (bool) \Illuminate\Support\Facades\Auth::user();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل التسليم والسداد')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('study_material_id')
                            ->label('الملزمة / الكتاب')
                            ->relationship('studyMaterial', 'title')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $material = \App\Models\StudyMaterial::find($state);
                                    if ($material) {
                                        $price = $material->price;
                                        $set('price', $price);
                                        $set('paid_amount', $price);
                                        $set('payment_status', 'paid');
                                    }
                                }
                            })
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->label('السعر (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $price = (float) ($get('price') ?? 0);
                                $paid = (float) ($get('paid_amount') ?? 0);
                                if ($paid >= $price && $price > 0) {
                                    $set('payment_status', 'paid');
                                } elseif ($paid > 0) {
                                    $set('payment_status', 'partial');
                                } else {
                                    $set('payment_status', 'unpaid');
                                }
                            }),

                        Forms\Components\TextInput::make('paid_amount')
                            ->label('المبلغ المدفوع (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $price = (float) ($get('price') ?? 0);
                                $paid = (float) ($get('paid_amount') ?? 0);
                                if ($paid >= $price && $price > 0) {
                                    $set('payment_status', 'paid');
                                } elseif ($paid > 0) {
                                    $set('payment_status', 'partial');
                                } else {
                                    $set('payment_status', 'unpaid');
                                }
                            }),

                        Forms\Components\Select::make('payment_status')
                            ->label('حالة السداد')
                            ->options([
                                'paid' => 'مسدد بالكامل ✅',
                                'partial' => 'سداد جزئي ⚠️',
                                'unpaid' => 'غير مسدد ❌',
                            ])
                            ->default('paid')
                            ->required(),

                        Forms\Components\DatePicker::make('delivered_at')
                            ->label('تاريخ التسليم')
                            ->default(now())
                            ->native(false)
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('studyMaterial.title')
                    ->label('الملزمة / الكتاب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('حالة السداد')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'مسدد بالكامل ✅',
                        'partial' => 'جزئي ⚠️',
                        'unpaid' => 'آجل ❌',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('تاريخ التسليم')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('delivered_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('حالة السداد')
                    ->options([
                        'paid' => 'مسدد بالكامل',
                        'partial' => 'جزئي',
                        'unpaid' => 'آجل',
                    ]),
            ])
            ->actions([
                // 1. تحصيل المتبقي بضغطة زر
                Tables\Actions\Action::make('collectRemaining')
                    ->label('تحصيل 💰')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (StudentMaterialDelivery $record): bool => $record->payment_status !== 'paid')
                    ->form([
                        Forms\Components\TextInput::make('additional_paid')
                            ->label('المبلغ المحصل الآن (ج.م)')
                            ->numeric()
                            ->default(fn (StudentMaterialDelivery $record) => $record->price - $record->paid_amount)
                            ->required(),
                    ])
                    ->action(function (StudentMaterialDelivery $record, array $data): void {
                        $newPaid = $record->paid_amount + (float) $data['additional_paid'];
                        $status = $newPaid >= $record->price ? 'paid' : 'partial';

                        $record->update([
                            'paid_amount' => $newPaid,
                            'payment_status' => $status,
                        ]);

                        Notification::make()
                            ->title('تم تحصيل المبلغ وتحديث الحساب بنجاح!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentMaterialDeliveries::route('/'),
            'create' => Pages\CreateStudentMaterialDelivery::route('/create'),
            'edit' => Pages\EditStudentMaterialDelivery::route('/{record}/edit'),
        ];
    }
}
