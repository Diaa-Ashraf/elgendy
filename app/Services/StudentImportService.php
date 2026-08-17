<?php

namespace App\Services;

use App\Jobs\ProcessStudentImportJob;
use App\Models\Discount;
use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentImport;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportService
{
    /**
     * التحقق الأولي من الملف وإنشاء سجل الاستيراد ثم إرسال المهمة للـ Queue
     */
    public function validateAndCreateImport(UploadedFile|string $file, ?int $userId = null): StudentImport
    {
        if ($file instanceof UploadedFile) {
            $originalName = $file->getClientOriginalName();
            $storedPath = $file->store('imports', 'local');
            $fullPath = Storage::disk('local')->path($storedPath);
        } else {
            $originalName = basename($file);
            $storedPath = $file;
            
            // تحقق من مكان وجود الملف
            if (file_exists($file)) {
                $fullPath = $file;
            } elseif (Storage::disk('local')->exists($file)) {
                $fullPath = Storage::disk('local')->path($file);
            } elseif (file_exists(storage_path('app/' . $file))) {
                $fullPath = storage_path('app/' . $file);
            } elseif (file_exists(storage_path('app/private/' . $file))) {
                $fullPath = storage_path('app/private/' . $file);
            } else {
                $fullPath = Storage::disk('local')->path($file);
            }
        }

        if (!file_exists($fullPath)) {
            throw new \InvalidArgumentException('لم يتم العثور على الملف المرفوع في مسار التخزين: ' . $fullPath);
        }

        // قراءة الترويسة من الشيت
        try {
            $reader = IOFactory::createReaderForFile($fullPath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = (int) $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // قراءة الصف الأول كترويسة
            $headerRow = [];
            $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $val = $sheet->getCell([$col, 1])->getValue();
                $headerRow[$col - 1] = $val !== null ? trim((string)$val) : '';
            }

            $headerMap = $this->normalizeHeaders($headerRow);

            if (empty($headerMap['name']) && !isset($headerMap['name'])) {
                throw new \InvalidArgumentException('عمود [اسم الطالب] غير موجود في الملف.');
            }
            if (empty($headerMap['parent_phone']) && !isset($headerMap['parent_phone'])) {
                throw new \InvalidArgumentException('عمود [رقم ولي الأمر] غير موجود في الملف.');
            }
            if (empty($headerMap['stage']) && !isset($headerMap['stage'])) {
                throw new \InvalidArgumentException('عمود [المرحلة الدراسية] غير موجود في الملف.');
            }

            $totalRows = max(0, $highestRow - 1);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('فشل فحص ملف Excel: ' . $e->getMessage());
        }

        $import = StudentImport::create([
            'user_id' => $userId,
            'file_name' => $originalName,
            'file_path' => $storedPath,
            'status' => 'queued',
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'succeeded_rows' => 0,
            'failed_rows' => 0,
        ]);

        ProcessStudentImportJob::dispatch($import);

        return $import;
    }

    /**
     * معالجة الاستيراد في الخلفية على دفعات (Batches)
     */
    public function processImport(StudentImport $import): void
    {
        $import->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_log' => [],
        ]);

        if (file_exists($import->file_path)) {
            $fullPath = $import->file_path;
        } elseif (Storage::disk('local')->exists($import->file_path)) {
            $fullPath = Storage::disk('local')->path($import->file_path);
        } elseif (file_exists(storage_path('app/' . $import->file_path))) {
            $fullPath = storage_path('app/' . $import->file_path);
        } elseif (file_exists(storage_path('app/private/' . $import->file_path))) {
            $fullPath = storage_path('app/private/' . $import->file_path);
        } else {
            $fullPath = Storage::disk('local')->path($import->file_path);
        }

        if (!file_exists($fullPath)) {
            $import->update([
                'status' => 'failed',
                'error_message' => 'ملف الاستيراد غير موجود على السيرفر: ' . $fullPath,
                'completed_at' => now(),
            ]);
            return;
        }

        try {
            $reader = IOFactory::createReaderForFile($fullPath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = (int) $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // قراءة الصف الأول كترويسة
            $headerRow = [];
            $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $val = $sheet->getCell([$col, 1])->getValue();
                $headerRow[$col - 1] = $val !== null ? trim((string)$val) : '';
            }
            $headerMap = $this->normalizeHeaders($headerRow);

            // تحميل المراحل والخصومات والمجموعات مسبقاً لتفادي N+1
            $stages = EducationalStage::all()->keyBy(fn($s) => $this->normalizeString($s->name));
            $discounts = Discount::where('is_active', true)->get()->keyBy(fn($d) => $this->normalizeString($d->title));
            $groups = Group::all()->keyBy(fn($g) => $this->normalizeString($g->name));

            $errors = [];
            $processedCount = 0;
            $succeededCount = 0;
            $failedCount = 0;

            // معالجة بالدفعات (مثلاً 100 صف)
            $batchSize = 100;
            $batchStudents = [];
            $batchGroupRelations = [];

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, false)[0] ?? [];
                
                // تخطي الصفوف الفارغة بالكامل
                if ($this->isEmptyRow($rowData)) {
                    continue;
                }

                $processedCount++;
                $rowErrors = [];

                $name = trim((string)($rowData[$headerMap['name']] ?? ''));
                $parentPhone = trim((string)($rowData[$headerMap['parent_phone']] ?? ''));
                $phone = isset($headerMap['phone']) ? trim((string)($rowData[$headerMap['phone']] ?? '')) : null;
                $stageName = trim((string)($rowData[$headerMap['stage']] ?? ''));
                $genderRaw = isset($headerMap['gender']) ? trim((string)($rowData[$headerMap['gender']] ?? '')) : 'ذكر';
                $birthDateRaw = isset($headerMap['birth_date']) ? trim((string)($rowData[$headerMap['birth_date']] ?? '')) : null;
                $address = isset($headerMap['address']) ? trim((string)($rowData[$headerMap['address']] ?? '')) : null;
                $notes = isset($headerMap['notes']) ? trim((string)($rowData[$headerMap['notes']] ?? '')) : null;
                $discountName = isset($headerMap['discount']) ? trim((string)($rowData[$headerMap['discount']] ?? '')) : null;
                $groupsRaw = isset($headerMap['groups']) ? trim((string)($rowData[$headerMap['groups']] ?? '')) : null;

                // 1. Validation للاسم
                if (empty($name)) {
                    $rowErrors[] = 'اسم الطالب مطلوب.';
                }

                // 2. Validation لرقم ولي الأمر
                if (empty($parentPhone)) {
                    $rowErrors[] = 'رقم هاتف ولي الأمر مطلوب.';
                } else {
                    $parentPhone = $this->cleanPhone($parentPhone);
                }

                if ($phone) {
                    $phone = $this->cleanPhone($phone);
                }

                // 3. Validation للمرحلة الدراسية
                $stageKey = $this->normalizeString($stageName);
                $stage = $stages->get($stageKey);
                if (!$stage) {
                    $rowErrors[] = "المرحلة الدراسية '{$stageName}' غير مسجلة بالنظام.";
                }

                // 4. Validation للنوع
                $gender = $this->normalizeGender($genderRaw);

                // 5. Validation لتاريخ الميلاد
                $birthDate = null;
                if (!empty($birthDateRaw)) {
                    try {
                        if (is_numeric($birthDateRaw)) {
                            $birthDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($birthDateRaw)->format('Y-m-d');
                        } else {
                            $birthDate = Carbon::parse($birthDateRaw)->format('Y-m-d');
                        }
                    } catch (\Throwable) {
                        $rowErrors[] = "تاريخ الميلاد '{$birthDateRaw}' غير صالح.";
                    }
                }

                // 6. الخصم إن وجد
                $discountId = null;
                if (!empty($discountName)) {
                    $discKey = $this->normalizeString($discountName);
                    $discount = $discounts->get($discKey);
                    if ($discount) {
                        $discountId = $discount->id;
                    }
                }

                // 7. المجموعات
                $matchedGroupIds = [];
                if (!empty($groupsRaw)) {
                    $groupNames = preg_split('/[,،|]/u', $groupsRaw);
                    foreach ($groupNames as $gName) {
                        $gName = trim($gName);
                        if (!empty($gName)) {
                            $gKey = $this->normalizeString($gName);
                            $groupObj = $groups->get($gKey);
                            if ($groupObj) {
                                $matchedGroupIds[] = $groupObj->id;
                            } else {
                                $rowErrors[] = "المجموعة '{$gName}' غير موجودة بالنظام.";
                            }
                        }
                    }
                }

                // إذا وجد أي خطأ في هذا الصف
                if (!empty($rowErrors)) {
                    $failedCount++;
                    $errors[] = [
                        'row' => $row,
                        'name' => $name ?: 'غير محدد',
                        'errors' => $rowErrors,
                    ];
                    continue;
                }

                // إذا كان الطالب موجوداً بالفعل (نفس الاسم ورقم ولي الأمر)
                $existing = Student::where('name', $name)
                    ->where('parent_phone', $parentPhone)
                    ->first();

                if ($existing) {
                    // إذا طلب مجموعات جديدة نربطه بها
                    if (!empty($matchedGroupIds)) {
                        $existing->groups()->syncWithoutDetaching($matchedGroupIds);
                    }
                    $succeededCount++;
                    continue;
                }

                // تجهيز الطالب للحفظ
                try {
                    $newStudent = Student::create([
                        'name' => $name,
                        'phone' => $phone,
                        'parent_phone' => $parentPhone,
                        'stage_id' => $stage->id,
                        'discount_id' => $discountId,
                        'gender' => $gender,
                        'birth_date' => $birthDate,
                        'address' => $address,
                        'notes' => $notes,
                        'qr_code' => 'STD-' . strtoupper(Str::random(8)),
                    ]);

                    if (!empty($matchedGroupIds)) {
                        $newStudent->groups()->syncWithoutDetaching($matchedGroupIds);
                    }

                    $succeededCount++;
                } catch (\Throwable $e) {
                    $failedCount++;
                    $errors[] = [
                        'row' => $row,
                        'name' => $name,
                        'errors' => ['فشل الحفظ بقاعدة البيانات: ' . $e->getMessage()],
                    ];
                }

                // تحديث الـ progress بشكل دوري كل Batch
                if ($processedCount % $batchSize === 0) {
                    $import->update([
                        'processed_rows' => $processedCount,
                        'succeeded_rows' => $succeededCount,
                        'failed_rows' => $failedCount,
                        'error_log' => $errors,
                    ]);
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            // تحديد الحالة النهائية
            $finalStatus = 'completed';
            if ($failedCount > 0 && $succeededCount > 0) {
                $finalStatus = 'completed_with_errors';
            } elseif ($failedCount > 0 && $succeededCount === 0) {
                $finalStatus = 'failed';
            }

            $import->update([
                'status' => $finalStatus,
                'total_rows' => $processedCount,
                'processed_rows' => $processedCount,
                'succeeded_rows' => $succeededCount,
                'failed_rows' => $failedCount,
                'error_log' => $errors,
                'completed_at' => now(),
            ]);

            // إرسال إشعار للمستخدم إذا كان محدداً
            if ($import->user_id) {
                $user = \App\Models\User::find($import->user_id);
                if ($user) {
                    $title = $finalStatus === 'completed'
                        ? "اكتمل استيراد الطلاب بنجاح 🎉 ({$succeededCount} طالب)"
                        : ($finalStatus === 'completed_with_errors'
                            ? "اكتمل الاستيراد مع وجود أخطاء ({$succeededCount} ناجح / {$failedCount} مرفوض)"
                            : "فشل استيراد ملف الطلاب: {$import->file_name}");

                    \Filament\Notifications\Notification::make()
                        ->title($title)
                        ->icon($finalStatus === 'completed' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
                        ->color($finalStatus === 'completed' ? 'success' : 'warning')
                        ->sendToDatabase($user);
                }
            }

        } catch (\Throwable $e) {
            $import->update([
                'status' => 'failed',
                'error_message' => 'حدث خطأ غير متوقع أثناء المعالجة: ' . $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * إنشاء وتنزيل ملف قالب Excel جاهز
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('استيراد الطلاب');
        $sheet->setRightToLeft(true);

        $headers = [
            'A1' => 'اسم الطالب (إجباري)',
            'B1' => 'رقم ولي الأمر (إجباري)',
            'C1' => 'رقم الطالب (اختياري)',
            'D1' => 'المرحلة الدراسية (إجباري)',
            'E1' => 'النوع (ذكر / أنثى)',
            'F1' => 'تاريخ الميلاد (YYYY-MM-DD)',
            'G1' => 'الخصم (اختياري)',
            'H1' => 'المجموعات (مفصولة بفواصل)',
            'I1' => 'العنوان (اختياري)',
            'J1' => 'ملاحظات (اختياري)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // تنسيق الهيدر
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // إضافة صفين كأمثلة استرشادية
        $firstStage = EducationalStage::first()?->name ?? 'الصف الأول الثانوي';
        $firstGroup = Group::first()?->name ?? 'مجموعة أ';

        $samples = [
            ['أحمد محمود إبراهيم', '01012345678', '01098765432', $firstStage, 'ذكر', '2008-05-15', '', $firstGroup, 'القاهرة - المعادي', 'طالب متميز'],
            ['سارة علي حسن', '01123456789', '', $firstStage, 'أنثى', '2008-08-20', '', $firstGroup, 'الجيزة', ''],
        ];

        $rowIdx = 2;
        foreach ($samples as $sample) {
            $colLetter = 'A';
            foreach ($sample as $val) {
                $sheet->setCellValueExplicit($colLetter . $rowIdx, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $colLetter++;
            }
            $rowIdx++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $dir = storage_path('app/templates');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filePath = $dir . '/student_import_template.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $filePath;
    }

    private function normalizeHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $idx => $header) {
            if ($header === null || trim((string)$header) === '') {
                continue;
            }
            $h = $this->normalizeString((string)$header);

            // 1. اسم الطالب (اسم الطالب / اسم / الطالب)
            if (!isset($map['name']) && (str_contains($h, 'طالب') || str_contains($h, 'اسم')) && !str_contains($h, 'ولي') && !str_contains($h, 'هاتف') && !str_contains($h, 'رقم') && !str_contains($h, 'موبايل')) {
                $map['name'] = $idx;
            }
            // 2. هاتف ولي الأمر (رقم هاتف ولي الامر / هاتف ولي الامر / رقم ولي الامر / موبايل ولي الامر)
            elseif (!isset($map['parent_phone']) && (str_contains($h, 'ولي') || str_contains($h, 'امر') || str_contains($h, 'اب'))) {
                $map['parent_phone'] = $idx;
            }
            // 3. هاتف الطالب (رقم هاتف الطالب / هاتف الطالب / رقم الطالب / موبايل الطالب)
            elseif (!isset($map['phone']) && (str_contains($h, 'هاتف') || str_contains($h, 'رقم') || str_contains($h, 'موبايل') || str_contains($h, 'تليفون') || str_contains($h, 'phone'))) {
                $map['phone'] = $idx;
            }
            // 4. المرحلة الدراسية (المرحله الدراسيه / المرحلة الدراسية / مرحلة / الصف)
            elseif (!isset($map['stage']) && (str_contains($h, 'مرحل') || str_contains($h, 'صف') || str_contains($h, 'دراس') || str_contains($h, 'stage') || str_contains($h, 'grade'))) {
                $map['stage'] = $idx;
            }
            // 5. النوع / الجنس (النوع / الجنس / نوع / ذكر / انثى)
            elseif (!isset($map['gender']) && (str_contains($h, 'نوع') || str_contains($h, 'جنس') || str_contains($h, 'gender') || str_contains($h, 'sex'))) {
                $map['gender'] = $idx;
            }
            // 6. تاريخ الميلاد (تاريخ الميلاد / تاريخ / ميلاد)
            elseif (!isset($map['birth_date']) && (str_contains($h, 'ميلاد') || str_contains($h, 'تاريخ') || str_contains($h, 'birth'))) {
                $map['birth_date'] = $idx;
            }
            // 7. الخصم (الخصم / خصم / نسبة الخصم)
            elseif (!isset($map['discount']) && (str_contains($h, 'خصم') || str_contains($h, 'discount'))) {
                $map['discount'] = $idx;
            }
            // 8. المجموعات (المجموعات / مجموعات / المجموعه / مجموعة)
            elseif (!isset($map['groups']) && (str_contains($h, 'مجموع') || str_contains($h, 'فوج') || str_contains($h, 'group'))) {
                $map['groups'] = $idx;
            }
            // 9. العنوان (العنوان / عنوان / السكن / محل الاقامة)
            elseif (!isset($map['address']) && (str_contains($h, 'عنوان') || str_contains($h, 'سكن') || str_contains($h, 'اقام') || str_contains($h, 'address'))) {
                $map['address'] = $idx;
            }
            // 10. ملاحظات (ملاحظات / ملاحظة / Notes)
            elseif (!isset($map['notes']) && (str_contains($h, 'ملاحظ') || str_contains($h, 'notes') || str_contains($h, 'note'))) {
                $map['notes'] = $idx;
            }
        }

        // Fallback في حال لم يتعرف بالبحث النصي وكان الملف مرتباً بالترتيب الافتراضي
        if (!isset($map['name']) && isset($headers[0])) $map['name'] = 0;
        if (!isset($map['parent_phone']) && isset($headers[1])) $map['parent_phone'] = 1;
        if (!isset($map['stage']) && isset($headers[3])) $map['stage'] = 3;

        return $map;
    }

    private function normalizeString(string $str): string
    {
        $str = mb_strtolower(trim($str), 'UTF-8');
        // إزالة التشكيل العربي
        $str = preg_replace('~[\x{064B}-\x{065F}\x{0670}]~u', '', $str);
        // توحيد الألفات والهمزات
        $str = preg_replace('~[\x{0622}\x{0623}\x{0625}\x{0671}]~u', "\xD8\xA7", $str); // أ إ آ ٱ -> ا
        // توحيد الياء والألف المقصورة
        $str = preg_replace('~[\x{0649}]~u', "\xD9\x8A", $str); // ى -> ي
        // توحيد التاء المربوطة والهاء
        $str = preg_replace('~[\x{0629}]~u', "\xD9\x87", $str); // ة -> ه
        // إزالة أي مسافات أو رموز خاصة
        $str = preg_replace('/[^\p{L}\p{N}]/u', '', $str);
        return $str;
    }

    private function normalizeGender(string $genderRaw): string
    {
        $g = $this->normalizeString($genderRaw);
        if (str_contains($g, 'انث') || str_contains($g, 'بنت') || str_contains($g, 'female') || $g === 'f') {
            return 'female';
        }
        return 'male';
    }

    private function cleanPhone(string $phone): string
    {
        $clean = preg_replace('/[^\d]/', '', $phone);
        return $clean ?: $phone;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $val) {
            if ($val !== null && trim((string)$val) !== '') {
                return false;
            }
        }
        return true;
    }
}
