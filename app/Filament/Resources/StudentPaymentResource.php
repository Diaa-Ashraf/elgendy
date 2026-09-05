<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentPaymentResource\Pages;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StudentPaymentResource extends Resource
{
    protected static ?string $model = StudentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $modelLabel = 'دفعة مالية';

    protected static ?string $pluralModelLabel = 'مدفوعات الطلاب';

    protected static ?string $navigationLabel = 'مدفوعات الطلاب';

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل سداد الطالب')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->relationship('student', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('group_id', null)),

                        Forms\Components\Select::make('group_id')
                            ->label('المجموعة')
                            ->options(function (callable $get) {
                                $studentId = $get('student_id');
                                if (! $studentId) {
                                    return Group::pluck('name', 'id');
                                }
                                $student = Student::find($studentId);
                                return $student ? $student->groups->pluck('name', 'id') : [];
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $type = $get('type');
                                if ($state && $type) {
                                    $group = Group::find($state);
                                    $student = Student::with('discount')->find($get('student_id'));
                                    if ($group) {
                                        $basePrice = ($type === 'month') 
                                            ? $group->price_per_month 
                                            : ($group->price_per_session * ($get('sessions_count') ?? 1));

                                        if ($student && $student->discount) {
                                            $discount = $student->discount;
                                            $discountAmount = $discount->type === 'percentage' 
                                                ? ($basePrice * ($discount->value / 100))
                                                : $discount->value;
                                            $basePrice = max(0, $basePrice - $discountAmount);
                                        }

                                        $set('amount', $basePrice);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('type')
                            ->label('نوع السداد')
                            ->options([
                                'month' => 'اشتراك شهر كامل',
                                'session' => 'سداد بالحصة',
                            ])
                            ->default('month')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $groupId = $get('group_id');
                                if ($groupId) {
                                    $group = Group::find($groupId);
                                    if ($group) {
                                        if ($state === 'month') {
                                            $set('amount', $group->price_per_month);
                                        } elseif ($state === 'session') {
                                            $sessions = $get('sessions_count') ?? 1;
                                            $set('amount', $group->price_per_session * $sessions);
                                        }
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('sessions_count')
                            ->label('عدد الحصص المسددة')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->visible(fn(callable $get) => $get('type') === 'session')
                            ->required(fn(callable $get) => $get('type') === 'session')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $groupId = $get('group_id');
                                if ($groupId && $state) {
                                    $group = Group::find($groupId);
                                    if ($group) {
                                        $set('amount', $group->price_per_session * $state);
                                    }
                                }
                            }),

                        Forms\Components\DatePicker::make('period_month')
                            ->label('الشهر المستحق عنه')
                            ->default(now()->startOfMonth())
                            ->visible(fn(callable $get) => $get('type') === 'month')
                            ->required(fn(callable $get) => $get('type') === 'month')
                            ->native(false)
                            ->displayFormat('Y-m'),

                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ المطلوب سداده (بعد الخصم إن وجد)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required()
                            ->helperText(function (callable $get) {
                                $studentId = $get('student_id');
                                $groupId = $get('group_id');
                                if (! $studentId || ! $groupId) return null;

                                $student = Student::with('discount')->find($studentId);
                                $group = Group::find($groupId);
                                if (! $student || ! $group) return null;

                                $type = $get('type');
                                $sessions = is_numeric($get('sessions_count')) ? (int) $get('sessions_count') : 1;
                                $basePrice = $type === 'session' 
                                    ? (($group->price_per_session ?? 0) * max(1, $sessions))
                                    : ($group->price_per_month ?? 0);

                                if ($student->discount) {
                                    $discount = $student->discount;
                                    $discountAmount = $discount->type === 'percentage' 
                                        ? ($basePrice * ($discount->value / 100))
                                        : $discount->value;

                                    $finalPrice = max(0, $basePrice - $discountAmount);
                                    return "💡 المبلغ الأصلي: {$basePrice} ج.م | الخصم المطبق: {$discount->title} ({$discount->value}" . ($discount->type === 'percentage' ? '%' : ' ج.م') . ") = خصم {$discountAmount} ج.م";
                                }

                                return "💡 السعر الأساسي للمجموعة: {$basePrice} ج.م (لا يوجد خصم لهذا الطالب)";
                            }),

                        Forms\Components\DatePicker::make('paid_at')
                            ->label('تاريخ السداد')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options([
                                'cash' => 'نقداً (كاش)',
                                'vodafone_cash' => 'فودافون كاش / محفظة إلكترونية 📱',
                                'instapay' => 'انستاباي (InstaPay)',
                                'other' => 'طريقة أخرى / تحويل بنكي',
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات السداد')
                            ->columnSpanFull()
                            ->rows(2),
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

                Tables\Columns\TextColumn::make('student.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع السداد')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'month' => 'success',
                        'session' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'month' => 'شهري',
                        'session' => 'بالحصة',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('period_month')
                    ->label('عن شهر')
                    ->date('Y-m')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('sessions_count')
                    ->label('عدد الحصص')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('receivedBy.name')
                    ->label('المستلم')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('paid_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('المجموعة')
                    ->relationship('group', 'name'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع السداد')
                    ->options([
                        'month' => 'شهري',
                        'session' => 'بالحصة',
                    ]),

                Tables\Filters\Filter::make('paid_at')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('to_date')->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from_date'], fn($q, $date) => $q->whereDate('paid_at', '>=', $date))
                            ->when($data['to_date'], fn($q, $date) => $q->whereDate('paid_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
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
            'index' => Pages\ListStudentPayments::route('/'),
            'create' => Pages\CreateStudentPayment::route('/create'),
            'edit' => Pages\EditStudentPayment::route('/{record}/edit'),
        ];
    }
}
