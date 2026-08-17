<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\EducationalStage;
use App\Models\Subject;
use App\Services\QuestionImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadTemplate')
                ->label('تحميل قالب Excel جاهز 📥')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function (QuestionImportService $service): BinaryFileResponse {
                    $path = $service->generateTemplate();
                    return response()->download($path, 'قالب_استيراد_بنك_الأسئلة.xlsx');
                }),

            Actions\Action::make('importQuestions')
                ->label('استيراد أسئلة من Excel ⚡')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('stage_id')
                        ->label('المرحلة الدراسية الموجه إليها الأسئلة')
                        ->options(EducationalStage::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('subject_id')
                        ->label('المادة الدراسية')
                        ->options(Subject::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\FileUpload::make('excel_file')
                        ->label('ملف الأسئلة (Excel .xlsx / .xls / .csv)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('temp-imports')
                        ->helperText('يمكنك استيراد مئات الأسئلة دفعة واحدة، تأكد من مطابقة الملف لقالب Excel الخاص بالنظام.'),
                ])
                ->action(function (array $data, QuestionImportService $service): void {
                    $filePath = Storage::disk('local')->path($data['excel_file']);

                    try {
                        $result = $service->importQuestions(
                            $filePath,
                            (int) $data['stage_id'],
                            (int) $data['subject_id']
                        );

                        // حذف الملف المؤقت بعد الاستيراد
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }

                        if ($result['success'] > 0) {
                            Notification::make()
                                ->title("تم استيراد {$result['success']} سؤال بنجاح 🚀")
                                ->body($result['failed'] > 0 ? "تنبيه: تعذر استيراد {$result['failed']} صف لوجود بيانات ناقصة." : null)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('لم يتم استيراد أي سؤال!')
                                ->body(implode("\n", array_slice($result['errors'], 0, 3)))
                                ->danger()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('حدث خطأ أثناء قراءة ملف Excel')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('إضافة سؤال يدوياً ➕'),
        ];
    }
}
