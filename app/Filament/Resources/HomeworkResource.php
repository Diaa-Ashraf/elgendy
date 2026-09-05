<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeworkResource\Pages;
use App\Filament\Resources\HomeworkResource\RelationManagers;
use App\Models\Homework;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeworkResource extends Resource
{
    protected static ?string $model = Homework::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $modelLabel = 'واجب منزلي';

    protected static ?string $pluralModelLabel = 'الواجبات والتكليفات';

    protected static ?string $navigationLabel = 'الواجبات والتكليفات';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الواجب والتكليف')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الواجب')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: واجب الدرس الأول - الحركة في خط مستقيم'),

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

                        Forms\Components\Select::make('group_id')
                            ->label('مجموعة محددة (اختياري)')
                            ->relationship('group', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('اتركه فارغاً إذا كان الواجب لجميع طلاب المرحلة'),

                        Forms\Components\Select::make('type')
                            ->label('طبيعة حل الواجب')
                            ->options([
                                'questions' => 'أسئلة إلكترونية من بنك الأسئلة (تصحيح فوري)',
                                'file_upload' => 'رفع ملف إجابة يدوي (PDF أو صورة حل)',
                                'mixed' => 'مختلط (أسئلة إلكترونية + رفع ملف حل)',
                            ])
                            ->default('questions')
                            ->required()
                            ->reactive()
                            ->native(false),

                        Forms\Components\TextInput::make('total_marks')
                            ->label('الدرجة الكلية للواجب')
                            ->numeric()
                            ->default(10.00)
                            ->minValue(1)
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('تفاصيل أو تعليمات الواجب')
                            ->rows(3)
                            ->placeholder('اكتب التعليمات والصفحات المطلوبة للطلاب...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('attachment')
                            ->label('ملف الواجب المرفق (PDF أو ورقة الأسئلة إن وجدت)')
                            ->directory('homework-attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->helperText('يمكنك إرفاق ملف PDF للواجب أو صورة الشيت (حد أقصى 10 ميجابايت)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('إعدادات المواعيد والنشر')
                    ->schema([
                        Forms\Components\DateTimePicker::make('due_date')
                            ->label('آخر موعد للتسليم (DeadLine)')
                            ->required()
                            ->default(now()->addDays(3)->setHour(23)->setMinute(59))
                            ->native(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('تاريخ ووقت النشر')
                            ->default(now())
                            ->native(false)
                            ->helperText('اتركه فارغاً ليبقى كمسودة، أو حدد موعد النشر التلقائي.'),

                        Forms\Components\Select::make('status')
                            ->label('حالة الواجب')
                            ->options([
                                'draft' => 'مسودة 📋',
                                'published' => 'منشور ✅',
                                'closed' => 'مُغلق 🔒',
                            ])
                            ->default('published')
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('allow_late_submission')
                            ->label('السماح بالتسليم المتأخر بعد الموعد')
                            ->default(false),

                        Forms\Components\TextInput::make('max_attempts')
                            ->label('عدد المحاولات المسموحة')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('أسئلة الواجب 📝')
                    ->description('تنبيه: يتم إدارة وإضافة أسئلة الواجب من تبويب "أسئلة الواجب" بالأسفل بعد إنشاء وحفظ الواجب.')
                    ->schema([])
                    ->collapsed()
                    ->collapsible()
                    ->visible(fn ($get) => in_array($get('type'), ['questions', 'mixed'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الواجب')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('المادة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->default('جميع المرحلة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'questions' => 'info',
                        'file_upload' => 'warning',
                        'mixed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'questions' => 'أسئلة 📝',
                        'file_upload' => 'رفع ملف 📎',
                        'mixed' => 'مختلط',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('موعد التسليم')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable()
                    ->color(fn (Homework $record): string => $record->isOverdue() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'مسودة 📋',
                        'published' => 'منشور ✅',
                        'closed' => 'مُغلق 🔒',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total_marks')
                    ->label('الدرجة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->label('التسليمات')
                    ->counts('submissions')
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('created_at', 'desc')
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
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'closed' => 'مُغلق',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('نشر الآن')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Homework $record): bool => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('نشر الواجب للطلاب')
                    ->modalDescription('سيتم نشر الواجب فوراً وسيظهر للطلاب في البوابة. هل أنت متأكد؟')
                    ->action(function (Homework $record): void {
                        $record->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ]);

                        Notification::make()
                            ->title('تم نشر الواجب بنجاح! 🚀')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('close')
                    ->label('إغلاق')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (Homework $record): bool => $record->status === 'published')
                    ->requiresConfirmation()
                    ->action(function (Homework $record): void {
                        $record->update(['status' => 'closed']);

                        Notification::make()
                            ->title('تم إغلاق الواجب 🔒')
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
            RelationManagers\QuestionsRelationManager::class,
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeworks::route('/'),
            'create' => Pages\CreateHomework::route('/create'),
            'edit' => Pages\EditHomework::route('/{record}/edit'),
        ];
    }
}
