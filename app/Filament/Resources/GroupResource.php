<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'المجموعة';

    protected static ?string $pluralModelLabel = 'المجموعات الدراسية';

    protected static ?string $navigationLabel = 'المجموعات الدراسية';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('البيانات الأساسية للمجموعة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المجموعة')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: مجموعة أ - الصف الثالث الثانوي'),

                        Forms\Components\Select::make('stage_id')
                            ->label('المرحلة الدراسية')
                            ->relationship('educationalStage', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('subject_id')
                            ->label('المادة الدراسية')
                            ->relationship('subject', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('capacity')
                            ->label('السعة الطلابية')
                            ->numeric()
                            ->default(20)
                            ->required()
                            ->minValue(1),

                        Forms\Components\TextInput::make('price_per_session')
                            ->label('سعر الحصة الواحدة')
                            ->numeric()
                            ->prefix('ج.م')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('price_per_month')
                            ->label('الاشتراك الشهرى')
                            ->numeric()
                            ->prefix('ج.م')
                            ->default(0)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('حالة المجموعة')
                            ->options([
                                'active' => 'نشطة',
                                'inactive' => 'غير نشطة',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('مواعيد الحصص (جدول المجموعات)')
                    ->schema([
                        Forms\Components\Repeater::make('schedules')
                            ->relationship('schedules')
                            ->schema([
                                Forms\Components\Select::make('day_of_week')
                                    ->label('اليوم')
                                    ->options([
                                        'sat' => 'السبت',
                                        'sun' => 'الأحد',
                                        'mon' => 'الإثنين',
                                        'tue' => 'الثلاثاء',
                                        'wed' => 'الأربعاء',
                                        'thu' => 'الخميس',
                                        'fri' => 'الجمعة',
                                    ])
                                    ->required()
                                    ->native(false),

                                Forms\Components\TimePicker::make('time')
                                    ->label('توقيت الحصة')
                                    ->required()
                                    ->seconds(false),

                                Forms\Components\TextInput::make('room')
                                    ->label('القاعة / الغرفة')
                                    ->placeholder('مثال: قاعة 1')
                                    ->maxLength(255),
                            ])
                            ->columns(3)
                            ->addActionLabel('إضافة موعد جديد')
                            ->defaultItems(0)
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المجموعة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة الدراسية')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('المادة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب المقيدين')
                    ->counts('students')
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة القصوى')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_per_session')
                    ->label('سعر الحصة')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_per_month')
                    ->label('السعر الشهري')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('schedules_summary')
                    ->label('المواعيد')
                    ->state(function (Group $record): string {
                        $dayMap = [
                            'sat' => 'السبت',
                            'sun' => 'الأحد',
                            'mon' => 'الإثنين',
                            'tue' => 'الثلاثاء',
                            'wed' => 'الأربعاء',
                            'thu' => 'الخميس',
                            'fri' => 'الجمعة',
                        ];
                        return $record->schedules->map(function ($sch) use ($dayMap) {
                            $day = $dayMap[$sch->day_of_week] ?? $sch->day_of_week;
                            $time = date('h:i A', strtotime($sch->time));
                            return "{$day} ({$time})";
                        })->join(' | ') ?: 'لا يوجد مواعيد';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'نشطة',
                        'inactive' => 'غير نشطة',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage_id')
                    ->label('المرحلة الدراسية')
                    ->relationship('educationalStage', 'name'),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة الدراسية')
                    ->relationship('subject', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشطة',
                        'inactive' => 'غير نشطة',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('generateSessions')
                    ->label('توليد الحصص')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('من تاريخ')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('to_date')
                            ->label('إلى تاريخ')
                            ->default(now()->addMonth())
                            ->required(),
                    ])
                    ->action(function (Group $record, array $data, \App\Services\AttendanceService $attendanceService): void {
                        $createdCount = $attendanceService->generateSessions(
                            $record->id,
                            $data['from_date'],
                            $data['to_date']
                        );

                        \Filament\Notifications\Notification::make()
                            ->title("تم توليد {$createdCount} حصة لهذه المجموعة بنجاح")
                            ->success()
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
            GroupResource\RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
