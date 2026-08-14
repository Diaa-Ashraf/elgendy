<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudyMaterialResource\Pages;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentMaterialDelivery;
use App\Models\StudyMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class StudyMaterialResource extends Resource
{
    protected static ?string $model = StudyMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $modelLabel = 'ملزمة / كتاب';

    protected static ?string $pluralModelLabel = 'الملازم والمطبوعات';

    protected static ?string $navigationLabel = 'الملازم والمطبوعات';

    protected static ?string $navigationGroup = 'المالية والمخازن';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return (bool) \Illuminate\Support\Facades\Auth::user();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الملزمة / الكتاب')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الملزمة / الكتاب')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('stage_id')
                            ->label('المرحلة الدراسية')
                            ->relationship('educationalStage', 'name')
                            ->nullable(),

                        Forms\Components\Select::make('subject_id')
                            ->label('المادة الدراسية')
                            ->relationship('subject', 'name')
                            ->nullable(),

                        Forms\Components\Select::make('term')
                            ->label('الفصل الدراسي / النصف')
                            ->options([
                                'الترم الأول' => 'الترم الأول',
                                'الترم الثاني' => 'الترم الثاني',
                                'المراجعة النهائية' => 'المراجعة النهائية',
                                'عام' => 'عام',
                            ])
                            ->default('الترم الأول'),

                        Forms\Components\TextInput::make('cost_price')
                            ->label('تكلفة الطباعة / النسخة (ج.م)')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('sale_price')
                            ->label('سعر البيع للطالب (ج.م)')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('الكمية المتاحة بالمخزن')
                            ->numeric()
                            ->default(0)
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
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الملزمة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('المادة'),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('سعر البيع')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('المخزون المتاح')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (int $state): string => "{$state} نسخة"),

                Tables\Columns\TextColumn::make('deliveries_count')
                    ->label('النسخ المسلمة')
                    ->counts('deliveries')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage_id')
                    ->label('المرحلة الدراسية')
                    ->relationship('educationalStage', 'name'),
            ])
            ->actions([
                // 1. تسليم ملزمة لطالب فردي
                Tables\Actions\Action::make('deliverToStudent')
                    ->label('تسليم لطالب 👤')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('student_id')
                            ->label('اختر الطالب')
                            ->options(Student::pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('paid_amount')
                            ->label('المبلغ المدفوع (ج.م)')
                            ->numeric()
                            ->default(fn (StudyMaterial $record) => $record->sale_price)
                            ->required(),
                    ])
                    ->action(function (StudyMaterial $record, array $data): void {
                        if ($record->stock_quantity <= 0) {
                            Notification::make()
                                ->title('عذراً! الكمية بالمخزن غير كافية.')
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $data) {
                            $paid = (float) $data['paid_amount'];
                            $price = (float) $record->sale_price;

                            $status = 'paid';
                            if ($paid <= 0) {
                                $status = 'unpaid';
                            } elseif ($paid < $price) {
                                $status = 'partial';
                            }

                            StudentMaterialDelivery::create([
                                'student_id' => $data['student_id'],
                                'study_material_id' => $record->id,
                                'quantity' => 1,
                                'price' => $price,
                                'paid_amount' => $paid,
                                'payment_status' => $status,
                                'delivered_at' => now(),
                            ]);

                            $record->decrement('stock_quantity', 1);
                        });

                        Notification::make()
                            ->title('تم تسليم الملزمة وتسجيل السداد بنجاح!')
                            ->success()
                            ->send();
                    }),

                // 2. تسليم ملزمة لمجموعة كاملة بضغطة زر
                Tables\Actions\Action::make('deliverToGroup')
                    ->label('تسليم لمجموعة كاملة 👥')
                    ->icon('heroicon-o-users')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('group_id')
                            ->label('اختر المجموعة')
                            ->options(Group::pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Toggle::make('mark_as_paid')
                            ->label('اعتبار المبلغ مدفوع للكل تلقائياً')
                            ->default(true),
                    ])
                    ->action(function (StudyMaterial $record, array $data): void {
                        $group = Group::with('students')->find($data['group_id']);
                        $students = $group->students;

                        if ($students->isEmpty()) {
                            Notification::make()
                                ->title('هذه المجموعة لا تحتوي على طلاب!')
                                ->warning()
                                ->send();
                            return;
                        }

                        if ($record->stock_quantity < $students->count()) {
                            Notification::make()
                                ->title("عذراً! المخزون ({$record->stock_quantity}) أقل من عدد طلاب المجموعة ({$students->count()}).")
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $students, $data) {
                            $count = 0;
                            foreach ($students as $student) {
                                // تجنب التكرار لو الطالب أخذ الملزمة من قبل
                                $exists = StudentMaterialDelivery::where('student_id', $student->id)
                                    ->where('study_material_id', $record->id)
                                    ->exists();

                                if (!$exists) {
                                    $price = (float) $record->sale_price;
                                    $paid = $data['mark_as_paid'] ? $price : 0;

                                    StudentMaterialDelivery::create([
                                        'student_id' => $student->id,
                                        'study_material_id' => $record->id,
                                        'quantity' => 1,
                                        'price' => $price,
                                        'paid_amount' => $paid,
                                        'payment_status' => $data['mark_as_paid'] ? 'paid' : 'unpaid',
                                        'delivered_at' => now(),
                                    ]);
                                    $count++;
                                }
                            }

                            $record->decrement('stock_quantity', $count);
                        });

                        Notification::make()
                            ->title('تم تسليم الملزمة لطلاب المجموعة بنجاح!')
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
            'index' => Pages\ListStudyMaterials::route('/'),
            'create' => Pages\CreateStudyMaterial::route('/create'),
            'edit' => Pages\EditStudyMaterial::route('/{record}/edit'),
        ];
    }
}
