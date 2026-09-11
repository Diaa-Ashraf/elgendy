<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParentNotificationResource\Pages;
use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\ParentNotification;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParentNotificationResource extends Resource
{
    protected static ?string $model = ParentNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $modelLabel = 'إشعار لولي الأمر';

    protected static ?string $pluralModelLabel = 'مركز إشعارات ورسائل أولياء الأمور';

    protected static ?string $navigationLabel = 'إشعارات أولياء الأمور';

    protected static ?string $navigationGroup = 'التواصل والرسائل';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الإشعار والتوجيه')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب المستهدف')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('نوع الإشعار')
                            ->options([
                                'general' => '📢 إشعار عام / تنويه',
                                'warning' => '⚠️ تحذير أو لفت نظر (غياب/سلوك)',
                                'exam' => '📝 نتيجة أو موعد امتحان',
                                'payment' => '💰 تذكير سداد أو اشتراك',
                                'homework' => '📚 تنبيه واجب أو مذكرة',
                            ])
                            ->default('general')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الإشعار')
                            ->placeholder('مثال: تنبيه بخصوص موعد الامتحان الشامل')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('message')
                            ->label('نص الرسالة / الإشعار')
                            ->placeholder('اكتب نص الإشعار الذي سيظهر لولي الأمر في بوابته...')
                            ->rows(4)
                            ->required()
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

                Tables\Columns\TextColumn::make('student.parent_phone')
                    ->label('هاتف ولي الأمر')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'general' => 'عام',
                        'warning' => 'تحذير ⚠️',
                        'exam' => 'امتحان 📝',
                        'payment' => 'مالي 💰',
                        'homework' => 'واجب 📚',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'warning' => 'warning',
                        'exam' => 'info',
                        'payment' => 'danger',
                        'homework' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_read')
                    ->label('مقروء في البوابة')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('send_batch_notification')
                    ->label('📢 إرسال إشعار جماعي لمجموعة كاملة')
                    ->icon('heroicon-o-megaphone')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('group_id')
                            ->label('اختر المجموعة')
                            ->options(Group::where('status', 'active')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('type')
                            ->label('نوع الإشعار')
                            ->options([
                                'general' => '📢 تنويه عام',
                                'exam' => '📝 موعد امتحان هام',
                                'homework' => '📚 تنبيه واجب',
                                'warning' => '⚠️ تحذير وإلزام بالحضور',
                            ])
                            ->default('general')
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الإشعار الجماعي')
                            ->required(),

                        Forms\Components\Textarea::make('message')
                            ->label('نص الرسالة')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $group = Group::with('students')->findOrFail($data['group_id']);
                        $count = 0;

                        foreach ($group->students as $student) {
                            ParentNotification::create([
                                'student_id' => $student->id,
                                'type' => $data['type'],
                                'title' => $data['title'],
                                'message' => $data['message'],
                            ]);
                            $count++;
                        }

                        Notification::make()
                            ->title("تم إرسال الإشعار بنجاح إلى {$count} ولي أمر في المجموعة!")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParentNotifications::route('/'),
            'create' => Pages\CreateParentNotification::route('/create'),
            'edit' => Pages\EditParentNotification::route('/{record}/edit'),
        ];
    }
}
