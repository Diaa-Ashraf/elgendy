<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentApplicationResource\Pages;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class StudentApplicationResource extends Resource
{
    protected static ?string $model = StudentApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $modelLabel = 'طلب تقديم أونلاين';

    protected static ?string $pluralModelLabel = 'طلبات التقديم أونلاين';

    protected static ?string $navigationLabel = 'طلبات التقديم';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return (bool) \Illuminate\Support\Facades\Auth::user();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات طالب التقديم')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الطالب')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('stage_id')
                            ->label('المرحلة الدراسية')
                            ->relationship('educationalStage', 'name')
                            ->required(),

                        Forms\Components\Select::make('group_id')
                            ->label('المجموعة المرغوبة')
                            ->relationship('group', 'name')
                            ->nullable(),

                        Forms\Components\Select::make('gender')
                            ->label('النوع')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('parent_phone')
                            ->label('رقم هاتف ولي الأمر')
                            ->tel()
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم هاتف الطالب')
                            ->tel(),

                        Forms\Components\Select::make('status')
                            ->label('حالة الطلب')
                            ->options([
                                'pending' => 'تحت المراجعة (معلق)',
                                'approved' => 'مقبول (تم القيد)',
                                'rejected' => 'مرفوض',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('interview_scheduled_at')
                            ->label('موعد المقابلة / اختبار القبول')
                            ->native(false),

                        Forms\Components\Textarea::make('admin_response')
                            ->label('الرد / ملاحظة الإدارة للطالب')
                            ->placeholder('مثال: يرجى الحضور بمقر السنتر يوم الأحد الساعة 4 ظهراً لإجراء اختبار القبول...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('address')
                            ->label('العنوان السكني')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات المتقدم')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة الدراسية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة المرغوبة')
                    ->placeholder('غير محددة'),

                Tables\Columns\TextColumn::make('parent_phone')
                    ->label('هاتف ولي الأمر')
                    ->searchable(),

                Tables\Columns\TextColumn::make('interview_scheduled_at')
                    ->label('موعد المقابلة')
                    ->dateTime('Y-m-d h:i A')
                    ->placeholder('لم يحدد بعد')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد المراجعة ⏳',
                        'approved' => 'تم القبول ✅',
                        'rejected' => 'مرفوض ❌',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('scheduleInterview')
                    ->label('تحديد مقابلة / رد 📅')
                    ->icon('heroicon-o-calendar-days')
                    ->color('warning')
                    ->form([
                        Forms\Components\DateTimePicker::make('interview_scheduled_at')
                            ->label('تاريخ ووقت المقابلة')
                            ->required()
                            ->native(false)
                            ->default(now()->addDays(1)->setHour(16)->setMinute(0)),

                        Forms\Components\Textarea::make('admin_response')
                            ->label('رسالة الرد لولي الأمر (الواتساب)')
                            ->default('مرحباً بك.. تم تحديد موعد المقابلة واختبار القبول بمقر السنتر.')
                            ->required(),
                    ])
                    ->action(function (StudentApplication $record, array $data): void {
                        $record->update([
                            'interview_scheduled_at' => $data['interview_scheduled_at'],
                            'admin_response' => $data['admin_response'],
                        ]);

                        NotificationService::notifyInterviewScheduled($record->name, $data['interview_scheduled_at']);

                        Notification::make()
                            ->title('تم جدولة موعد المقابلة بنجاح')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('whatsappReply')
                    ->label('💬 رد واتساب')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(function (StudentApplication $record): string {
                        $phone = preg_replace('/[^0-9]/', '', $record->parent_phone);
                        if (str_starts_with($phone, '01')) {
                            $phone = '2' . $phone;
                        }
                        
                        $dateText = $record->interview_scheduled_at 
                            ? \Carbon\Carbon::parse($record->interview_scheduled_at)->format('Y-m-d h:i A')
                            : 'سيتم تحديده قريباً';

                        $msg = rawurlencode("مرحباً بك، بشأن طلب التقديم أونلاين للطالب/ة {$record->name}.\nموعد المقابلة: {$dateText}\nملاحظات الإدارة: " . ($record->admin_response ?? 'يرجى الحضور لمقر السنتر.'));

                        return "https://wa.me/{$phone}?text={$msg}";
                    })
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('approve')
                    ->label('قبول وقيد كطالب رسمي 🎓')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (StudentApplication $record): bool => $record->status !== 'approved')
                    ->action(function (StudentApplication $record): void {
                        DB::transaction(function () use ($record) {
                            $student = Student::create([
                                'name' => $record->name,
                                'stage_id' => $record->stage_id,
                                'gender' => $record->gender,
                                'parent_phone' => $record->parent_phone,
                                'phone' => $record->phone,
                                'address' => $record->address,
                                'notes' => 'تم التقديم أونلاين وقبوله رسمياً.',
                            ]);

                            if ($record->group_id) {
                                $student->groups()->attach($record->group_id, [
                                    'joined_at' => now(),
                                    'status' => 'active',
                                ]);
                            }

                            $record->update(['status' => 'approved']);

                            NotificationService::notifyNewStudent($student->name);
                        });

                        Notification::make()
                            ->title('تم قبول الطالب وقيده بنجاح!')
                            ->body('تم إنشاء حساب رسمي للطالب وتوليد كود الـ QR الخاص به.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('رفض الطلب ❌')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (StudentApplication $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_response')
                            ->label('سبب الاعتذار / الرفض')
                            ->default('نعتذر عن عدم قبول الطلب لعدم توفر أماكن شاغرة بالمجموعة حالياً.')
                            ->required(),
                    ])
                    ->action(function (StudentApplication $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'admin_response' => $data['admin_response'],
                        ]);

                        Notification::make()
                            ->title('تم تسجيل رفض الطلب')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentApplications::route('/'),
            'create' => Pages\CreateStudentApplication::route('/create'),
            'edit' => Pages\EditStudentApplication::route('/{record}/edit'),
        ];
    }
}
