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
                        $total = $record->attendances()->count();
                        if ($total === 0) {
                            return 'لم يسجل بعد';
                        }
                        $present = $record->attendances()->where('status', 'present')->count();
                        return "حضر {$present} من أصل {$total}";
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

                Tables\Actions\Action::make('viewAbsentees')
                    ->label('عرض الغائبين ❌')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->modalHeading(fn (GroupSession $record) => "قائمة الطلاب الغائبين في حصة: {$record->group?->name}")
                    ->modalContent(function (GroupSession $record) {
                        $absentAttendances = Attendance::where('group_session_id', $record->id)
                            ->where('status', 'absent')
                            ->with('student')
                            ->get();

                        return view('filament.modals.absentees-list', [
                            'absentees' => $absentAttendances,
                            'session' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

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
                    ->action(function (GroupSession $record, array $data, Tables\Actions\Action $action): void {
                        $newDate = $data['new_date'];

                        // التحقق من عدم وجود حصة أخرى مسجلة بالفعل لنفس المجموعة في هذا التاريخ
                        $exists = GroupSession::withoutGlobalScope('tenant')
                            ->where('group_id', $record->group_id)
                            ->where('date', $newDate)
                            ->where('id', '!=', $record->id)
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('يوجد حصة مسجلة بالفعل لهذه المجموعة في نفس التاريخ المختار!')
                                ->body("التاريخ {$newDate} محجوز بحصة أخرى، يرجى اختيار موعد بديل.")
                                ->danger()
                                ->send();

                            $action->halt();
                            return;
                        }

                        $record->update([
                            'date' => $newDate,
                            'status' => 'postponed',
                            'topic' => ($record->topic ? $record->topic . ' - ' : '') . 'تم التأجيل: ' . ($data['reason'] ?? 'بدون سبب'),
                        ]);

                        Notification::make()
                            ->title('تم تأجيل موعد الحصة بنجاح')
                            ->body("الموعد الجديد: {$newDate}")
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
