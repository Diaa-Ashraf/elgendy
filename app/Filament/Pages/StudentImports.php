<?php

namespace App\Filament\Pages;

use App\Models\StudentImport;
use App\Services\StudentImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentImports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'استيراد الطلاب من Excel';

    protected static ?string $title = 'استيراد الطلاب عبر ملفات Excel';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.student-imports';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') 
            || $user->email === 'admin@admin.com' 
            || $user->can('import_students') 
            || $user->can('create_students');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('تحميل قالب Excel جاهز 📥')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function (StudentImportService $service): BinaryFileResponse {
                    $path = $service->generateTemplate();
                    return response()->download($path, 'قالب_استيراد_الطلاب.xlsx');
                }),

            Action::make('uploadExcel')
                ->label('رفع ملف جديد 📤')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    FileUpload::make('file')
                        ->label('اختر ملف Excel أو CSV')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'text/plain',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->preserveFilenames()
                        ->helperText('الملف يجب أن يحتوي على الأعمدة: اسم الطالب، رقم ولي الأمر، المرحلة الدراسية.'),
                ])
                ->action(function (array $data, StudentImportService $service): void {
                    try {
                        $storedPath = $data['file'];
                        $service->validateAndCreateImport($storedPath, Auth::id());

                        Notification::make()
                            ->title('تم إرسال الملف للمعالجة بنجاح ⚡')
                            ->body('جاري استيراد الطلاب في الخلفية، يمكنك متابعة شريط التقدم أدناه.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('خطأ في ملف الاستيراد')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StudentImport::query()->latest())
            ->poll('3s')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('اسم الملف')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'completed_with_errors' => 'warning',
                        'processing', 'validating' => 'info',
                        'queued' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'queued' => 'في الانتظار ⏳',
                        'validating' => 'جاري الفحص 🔍',
                        'processing' => 'جاري الاستيراد ⚡',
                        'completed' => 'مكتمل بنجاح ✅',
                        'completed_with_errors' => 'مكتمل مع وجود أخطاء ⚠️',
                        'failed' => 'فشل الاستيراد ❌',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('progress')
                    ->label('نسبة التقدم')
                    ->state(fn(StudentImport $record): string => $record->progress_percentage . '% (' . $record->processed_rows . '/' . ($record->total_rows ?? '?') . ')')
                    ->badge()
                    ->color(fn(StudentImport $record): string => $record->progress_percentage === 100 ? 'success' : 'primary'),

                Tables\Columns\TextColumn::make('succeeded_rows')
                    ->label('تم بنجاح')
                    ->numeric()
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('failed_rows')
                    ->label('صفوف مرفوضة')
                    ->numeric()
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('retryImport')
                    ->label('إعادة المحاولة 🔄')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn(StudentImport $record): bool => in_array($record->status, ['failed', 'completed_with_errors']))
                    ->requiresConfirmation()
                    ->modalHeading('إعادة معالجة ملف الاستيراد')
                    ->modalDescription('هل أنت متأكد من إعادة تشغيل معالجة هذا الملف؟ سيتم فحص الصفوف غير المسجلة ومحاولة استيرادها مجدداً.')
                    ->action(function (StudentImport $record, StudentImportService $service): void {
                        $record->update([
                            'status' => 'queued',
                            'error_message' => null,
                        ]);

                        \App\Jobs\ProcessStudentImportJob::dispatch($record);

                        Notification::make()
                            ->title('تمت إعادة إرسال المهمة للـ Queue بنجاح ⚡')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('viewErrors')
                    ->label('تقرير الأخطاء 📋')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn(StudentImport $record): bool => !empty($record->error_log) || !empty($record->error_message))
                    ->modalHeading(fn(StudentImport $record) => "تقرير أخطاء استيراد: {$record->file_name}")
                    ->modalContent(fn(StudentImport $record) => view('filament.pages.import-errors-modal', ['import' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                Tables\Actions\DeleteAction::make()
                    ->label('حذف السجل')
                    ->after(function (StudentImport $record) {
                        if ($record->file_path) {
                            Storage::disk('local')->delete($record->file_path);
                        }
                    }),
            ]);
    }
}
