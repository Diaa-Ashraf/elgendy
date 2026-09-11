<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupSessionResource\Pages;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\GroupSession;
use App\Services\AttendanceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GroupSessionResource extends Resource
{
    protected static ?string $model = GroupSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $modelLabel = 'جلسة (حصة)';

    protected static ?string $pluralModelLabel = 'سجل الحصص والجلسات';

    protected static ?string $navigationLabel = 'سجل الحصص والجلسات';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل الجلسة')
                    ->schema([
                        Forms\Components\Select::make('group_id')
                            ->label('المجموعة')
                            ->relationship('group', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('date')
                            ->label('تاريخ الجلسة')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('topic')
                            ->label('عنوان / موضوع الحصة')
                            ->placeholder('مثال: مراجعة الفصل الأول')
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->label('حالة الجلسة')
                            ->options([
                                'scheduled' => 'مجدولة',
                                'held' => 'تمت (تم الحضور)',
                                'postponed' => 'مؤجلة (تأجيل موعد)',
                                'cancelled' => 'ملغاة',
                            ])
                            ->default('scheduled')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات الحصة والشرح لولي الأمر')
                            ->placeholder('اكتب ما تم شرحه أو تنبيهات للطلاب...')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('homework_notes')
                            ->label('المطلوب والتكليفات للحصة القادمة')
                            ->placeholder('مثال: حل صفحة 20 إلى 25 من المذكرة وتجهيز امتحان الفصل...')
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
                Tables\Columns\TextColumn::make('group.name')
                    ->label('اسم المجموعة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('تاريخ الجلسة')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('topic')
                    ->label('موضوع الحصة')
                    ->placeholder('غير محدد')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'held' => 'success',
                        'postponed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'مجدولة',
                        'held' => 'تمت الحصة',
                        'postponed' => 'مؤجلة ⏰',
                        'cancelled' => 'ملغاة',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('attendances_summary')
                    ->label('ملخص الحضور')
                    ->state(function (GroupSession $record): string {
                        $totalGroupStudents = $record->group?->students()->wherePivot('status', 'active')->count() 
                            ?? $record->group?->students()->count() 
                            ?? 0;

                        if ($totalGroupStudents === 0) {
                            return 'لا يوجد طلاب بالمجموعة';
                        }

                        $present = $record->attendances()->whereIn('status', ['present', 'late'])->count();

                        if ($record->attendances()->count() === 0 && $record->status === 'scheduled') {
                            return 'لم يسجل بعد';
                        }

                        return "حضر {$present} من أصل {$totalGroupStudents}";
                    })
                    ->badge()
                    ->color(function (GroupSession $record): string {
                        $totalGroupStudents = $record->group?->students()->wherePivot('status', 'active')->count() 
                            ?? $record->group?->students()->count() 
                            ?? 0;

                        if ($totalGroupStudents === 0) {
                            return 'gray';
                        }

                        if ($record->attendances()->count() === 0 && $record->status === 'scheduled') {
                            return 'gray';
                        }

                        $present = $record->attendances()->whereIn('status', ['present', 'late'])->count();

                        if ($present === $totalGroupStudents && $totalGroupStudents > 0) {
                            return 'success';
                        }

                        if ($present > 0) {
                            return 'warning';
                        }

                        return 'danger';
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('المجموعة')
                    ->relationship('group', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الجلسة')
                    ->options([
                        'scheduled' => 'مجدولة',
                        'held' => 'تمت الحصة',
                        'postponed' => 'مؤجلة',
                        'cancelled' => 'ملغاة',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('markAttendance')
                    ->label('تسجيل الحضور')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->disabled(fn (GroupSession $record): bool => \Carbon\Carbon::parse($record->date)->startOfDay()->isFuture())
                    ->tooltip(fn (GroupSession $record): ?string => \Carbon\Carbon::parse($record->date)->startOfDay()->isFuture() ? 'لا يمكن تسجيل الحضور لحصة في تاريخ مستقبلي' : null)
                    ->before(function (GroupSession $record, Tables\Actions\Action $action): void {
                        if (\Carbon\Carbon::parse($record->date)->startOfDay()->isFuture()) {
                            Notification::make()
                                ->title('تنبيه!')
                                ->body("لا يمكن تسجيل الحضور لحصة مجدولة في تاريخ مستقبلي ({$record->date}).")
                                ->warning()
                                ->send();
                            $action->halt();
                        }
                    })
                    ->fillForm(function (GroupSession $record): array {
                        // Fetch all active students in the group
                        $activeStudents = $record->group->students()
                            ->wherePivot('status', 'active')
                            ->get();

                        // Fetch existing attendance records
                        $existingAttendance = Attendance::where('group_session_id', $record->id)
                            ->get()
                            ->keyBy('student_id');

                        $studentsData = [];
                        foreach ($activeStudents as $student) {
                            $att = $existingAttendance->get($student->id);
                            $studentsData[] = [
                                'student_id' => $student->id,
                                'student_name' => $student->name,
                                'status' => $att?->status ?? 'present',
                                'notes' => $att?->notes ?? null,
                            ];
                        }

                        return [
                            'attendances' => $studentsData,
                        ];
                    })
                    ->form([
                        Forms\Components\Repeater::make('attendances')
                            ->label('قائمة حضور الطلاب')
                            ->schema([
                                Forms\Components\Hidden::make('student_id'),
                                Forms\Components\TextInput::make('student_name')
                                    ->label('اسم الطالب')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\Select::make('status')
                                    ->label('الحالة')
                                    ->options([
                                        'present' => 'حاضر',
                                        'absent' => 'غائب',
                                        'late' => 'متأخر',
                                        'excused' => 'معذور',
                                    ])
                                    ->required()
                                    ->native(false),
                                Forms\Components\TextInput::make('notes')
                                    ->label('ملاحظات')
                                    ->placeholder('ملاحظة اختيارية...'),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->action(function (GroupSession $record, array $data, AttendanceService $attendanceService): void {
                        $attendanceService->markBulkAttendance($record->id, $data['attendances'] ?? []);

                        Notification::make()
                            ->title('تم تسجيل الحضور والغياب بنجاح')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('viewAttendees')
                    ->label('عرض الحاضرين 👥')
                    ->icon('heroicon-o-users')
                    ->color('success')
                    ->modalHeading(fn (GroupSession $record) => "قائمة الطلاب الحاضرين في حصة: {$record->group?->name}")
                    ->modalContent(function (GroupSession $record, AttendanceService $attendanceService) {
                        $attendees = $attendanceService->getAttendeesForSession($record->id);

                        return view('filament.modals.attendees-list', [
                            'attendees' => $attendees,
                            'session' => $record,
                        ]);
                    })
                    ->extraModalFooterActions(function (Tables\Actions\Action $action): array {
                        $actions = [
                            $action->makeModalSubmitAction('notifyAttendeesPortal', ['target' => 'attendees_portal'])
                                ->label('📢 إشعار بوابة ولي الأمر للحاضرين')
                                ->color('primary'),
                        ];

                        if (\App\Services\WhatsAppNotificationService::isBulkEnabled()) {
                            $actions[] = $action->makeModalSubmitAction('sendBulkWhatsAppAttendees', ['target' => 'attendees_whatsapp'])
                                ->label('💬 إرسال واتساب لجميع الحاضرين')
                                ->color('success');
                        }

                        return $actions;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->action(function (GroupSession $record, array $arguments, AttendanceService $attendanceService): void {
                        $target = $arguments['target'] ?? null;
                        if ($target === 'attendees_portal') {
                            $count = $attendanceService->notifyBulkParentPortal($record->id, 'present');
                            Notification::make()
                                ->title("تم إرسال {$count} إشعار للحاضرين عبر بوابة ولي الأمر")
                                ->success()
                                ->send();
                        } elseif ($target === 'attendees_whatsapp') {
                            $result = $attendanceService->sendBulkWhatsApp($record->id, 'present');
                            Notification::make()
                                ->title("تم إرسال {$result['success']} رسالة واتساب للحاضرين بنجاح")
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('viewAbsentees')
                    ->label('عرض الغائبين ❌')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->modalHeading(fn (GroupSession $record) => "قائمة الطلاب الغائبين في حصة: {$record->group?->name}")
                    ->modalContent(function (GroupSession $record, AttendanceService $attendanceService) {
                        $absentees = $attendanceService->getAbsenteesForSession($record->id);

                        return view('filament.modals.absentees-list', [
                            'absentees' => $absentees,
                            'session' => $record,
                        ]);
                    })
                    ->extraModalFooterActions(function (Tables\Actions\Action $action): array {
                        $actions = [
                            $action->makeModalSubmitAction('notifyAbsenteesPortal', ['target' => 'absentees_portal'])
                                ->label('📢 إشعار بوابة ولي الأمر للغائبين')
                                ->color('danger'),
                        ];

                        if (\App\Services\WhatsAppNotificationService::isBulkEnabled()) {
                            $actions[] = $action->makeModalSubmitAction('sendBulkWhatsAppAbsentees', ['target' => 'absentees_whatsapp'])
                                ->label('💬 إرسال واتساب لجميع الغائبين')
                                ->color('success');
                        }

                        return $actions;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->action(function (GroupSession $record, array $arguments, AttendanceService $attendanceService): void {
                        $target = $arguments['target'] ?? null;
                        if ($target === 'absentees_portal') {
                            $count = $attendanceService->notifyBulkParentPortal($record->id, 'absent');
                            Notification::make()
                                ->title("تم إرسال {$count} إشعار غياب عبر بوابة ولي الأمر")
                                ->success()
                                ->send();
                        } elseif ($target === 'absentees_whatsapp') {
                            $result = $attendanceService->sendBulkWhatsApp($record->id, 'absent');
                            Notification::make()
                                ->title("تم إرسال {$result['success']} رسالة واتساب للغائبين بنجاح")
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('postponeSession')
                    ->label('تأجيل الحصة ⏰')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->form([
                        Forms\Components\DatePicker::make('new_date')
                            ->label('التاريخ الجديد للحصة')
                            ->required()
                            ->native(false)
                            ->default(now()->addDays(1)),
                        Forms\Components\TextInput::make('reason')
                            ->label('سبب التأجيل (اختياري)')
                            ->placeholder('مثال: ظروف طارئة للمدرس...'),
                    ])
                    ->action(function (GroupSession $record, array $data): void {
                        $record->update([
                            'date' => $data['new_date'],
                            'status' => 'postponed',
                            'topic' => ($record->topic ? $record->topic . ' - ' : '') . 'تم التأجيل: ' . ($data['reason'] ?? 'بدون سبب'),
                        ]);

                        Notification::make()
                            ->title('تم تأجيل موعد الحصة بنجاح')
                            ->body("الموعد الجديد: {$data['new_date']}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('cancelSession')
                    ->label('إلغاء الحصة 🚫')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد إلغاء الحصة الدراسية')
                    ->modalDescription('هل أنت تأكد من إغلاق وإلغاء هذه الحصة؟')
                    ->form([
                        Forms\Components\TextInput::make('reason')
                            ->label('سبب الإلغاء (اختياري)')
                            ->placeholder('مثال: إجازة رسمية...'),
                    ])
                    ->action(function (GroupSession $record, array $data): void {
                        $record->update([
                            'status' => 'cancelled',
                            'topic' => ($record->topic ? $record->topic . ' - ' : '') . 'ملغاة: ' . ($data['reason'] ?? 'بدون سبب'),
                        ]);

                        Notification::make()
                            ->title('تم إلغاء الحصة')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('printAttendance')
                    ->label('طباعة كشف الحضور')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (GroupSession $record) => route('session.attendance.print', $record->id))
                    ->openUrlInNewTab(),

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
            'index' => Pages\ListGroupSessions::route('/'),
            'create' => Pages\CreateGroupSession::route('/create'),
            'edit' => Pages\EditGroupSession::route('/{record}/edit'),
        ];
    }
}
