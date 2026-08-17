<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QuestionImportService
{
    /**
     * Generate an Excel sample template for question bank import.
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->setTitle('قالب بنك الأسئلة');

        // العناوين الإرشادية الواضحة
        $headers = [
            'A1' => 'نوع السؤال * (اختيارات / صح وغلط)',
            'B1' => 'نص السؤال *',
            'C1' => 'الخيار A (أو: صح)',
            'D1' => 'الخيار B (أو: خطأ)',
            'E1' => 'الخيار C (اختياري)',
            'F1' => 'الخيار D (اختياري)',
            'G1' => 'الإجابة الصحيحة * (مثال: A أو B أو صح أو خطأ)',
            'H1' => 'الموضوع / الدرس / Topic',
            'I1' => 'مستوى الصعوبة (سهل / متوسط / صعب)',
            'J1' => 'درجة السؤال (افتراضي: 1)',
            'K1' => 'الشرح والتفسير العلمي النموذجي (يظهر للطالب)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
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
                'startColor' => ['rgb' => '4F46E5'], // Indigo Premium
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // أمثلة شاملة على (الاختيار من متعدد) و (صح وغلط)
        $rows = [
            // مثال 1: اختيار من متعدد 4 خيارات
            [
                'اختيارات',
                'ما هي وحدة قياس القوة الدافعة الكهربية (فرق الجهد) في النظام الدولي؟',
                'الفولت (Volt)',
                'الأمبير (Ampere)',
                'الأوم (Ohm)',
                'الجول (Joule)',
                'A',
                'التيار الكهربي وقانون أوم',
                'سهل',
                1.0,
                'الفولت هو وحدة قياس فرق الجهد الكهربي والقوة الدافعة الكهربية.',
            ],
            // مثال 2: اختيار من متعدد إجابتين صحيحتين
            [
                'اختيارات',
                'من العوامل التي يتوقف عليها عزم الازدواج المؤثر على ملف يمر به تيار:',
                'كثافة الفيض المغناطيسي',
                'مساحة وجه الملف',
                'نوع مادة السلك فقط',
                'الضغط الجوي',
                'A,B',
                'عزم الازدواج المغناطيسي',
                'متوسط',
                2.0,
                'يتناسب عزم الازدواج طردياً مع كل من كثافة الفيض ومساحة الملف وشدة التيار.',
            ],
            // مثال 3: سؤال صح أو خطأ (الإجابة صح)
            [
                'صح وغلط',
                'تزداد مقاومة موصل فلزي بزيادة درجة حرارته.',
                'صح',
                'خطأ',
                '',
                '',
                'صح',
                'العوامل المؤثرة على المقاومة',
                'سهل',
                1.0,
                'صحيح؛ لأن زيادة درجة الحرارة تزيد من سعة اهتزاز جزيئات الموصل مما يزيد من ممانعته للتيار.',
            ],
            // مثال 4: سؤال صح أو خطأ (الإجابة خطأ)
            [
                'صح وغلط',
                'سرعة الضوء في الماء أكبر من سرعته في الهواء والفراغ.',
                'صح',
                'خطأ',
                '',
                '',
                'خطأ',
                'انكسار الضوء والكثافة الضوئية',
                'متوسط',
                1.0,
                'خطأ؛ سرعة الضوء تكون في أقصى قيمة لها في الفراغ والهواء وتقل في الأوساط الأكبر كثافة كالماء.',
            ],
        ];

        $rowIndex = 2;
        foreach ($rows as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter . $rowIndex, $val);
                $colLetter++;
            }
            $rowIndex++;
        }

        // ضبط اتساع الأعمدة تلقائياً
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $dir = storage_path('app/temp');
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $filePath = $dir . '/question_bank_template.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * High-performance intelligent batch import of questions from Excel.
     *
     * @param string $filePath Absolute path to the uploaded Excel file
     * @param int $stageId Educational Stage ID
     * @param int $subjectId Subject ID
     * @return array Summary of import ['total' => int, 'success' => int, 'failed' => int, 'errors' => array]
     */
    public function importQuestions(string $filePath, int $stageId, int $subjectId): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // إزالة الهيدر
        array_shift($rows);

        $insertedCount = 0;
        $failedCount = 0;
        $errors = [];
        $batch = [];
        $now = now()->toDateTimeString();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 لمطابقة رقم الصف الحقيقي بالإكسيل

            // الكشف التلقائي عن الأعمدة سواء كانت بالقالب المحدث (11 عمود) أو القديم (10 أعمدة)
            $colA = trim((string)($row['A'] ?? ''));
            $isTypeColumn = in_array(mb_strtolower($colA), ['صح وغلط', 'صح وخطأ', 'صح أم خطأ', 'صح او غلط', 'true_false', 'اختيارات', 'اختيار', 'single_choice', 'multiple_choice']);

            if ($isTypeColumn) {
                $rawType = $colA;
                $questionText = trim((string)($row['B'] ?? ''));
                $optA = trim((string)($row['C'] ?? ''));
                $optB = trim((string)($row['D'] ?? ''));
                $optC = trim((string)($row['E'] ?? ''));
                $optD = trim((string)($row['F'] ?? ''));
                $rawCorrect = trim((string)($row['G'] ?? ''));
                $topic = trim((string)($row['H'] ?? '')) ?: null;
                $diffText = trim((string)($row['I'] ?? ''));
                $defaultMarks = (float) ($row['J'] ?? 1.0);
                $explanation = trim((string)($row['K'] ?? '')) ?: null;
            } else {
                // القالب التلقائي: العمود A هو نص السؤال
                $questionText = $colA;
                $optA = trim((string)($row['B'] ?? ''));
                $optB = trim((string)($row['C'] ?? ''));
                $optC = trim((string)($row['D'] ?? ''));
                $optD = trim((string)($row['E'] ?? ''));
                $rawCorrect = trim((string)($row['F'] ?? ''));
                $topic = trim((string)($row['G'] ?? '')) ?: null;
                $diffText = trim((string)($row['H'] ?? ''));
                $defaultMarks = (float) ($row['I'] ?? 1.0);
                $explanation = trim((string)($row['J'] ?? '')) ?: null;
                $rawType = null;
            }

            if (empty($questionText)) {
                continue; // تخطي الصفوف الفارغة
            }

            // 1. الكشف الذكي عما إذا كان السؤال "صح وغلط"
            $isTrueFalse = false;
            if ($rawType && in_array(mb_strtolower($rawType), ['صح وغلط', 'صح وخطأ', 'صح أم خطأ', 'صح او غلط', 'true_false'])) {
                $isTrueFalse = true;
            } elseif (in_array(mb_strtolower($optA), ['صح', 'صواب', 'true']) || in_array(mb_strtolower($optB), ['خطأ', 'غلط', 'false']) || in_array(mb_strtolower($rawCorrect), ['صح', 'صواب', 'خطأ', 'غلط', 'true', 'false'])) {
                $isTrueFalse = true;
            }

            if ($isTrueFalse) {
                $type = 'true_false';
                $options = [
                    ['key' => 'true', 'text' => 'صح ✔'],
                    ['key' => 'false', 'text' => 'خطأ ✖'],
                ];

                // استخراج الإجابة الصحيحة لسؤال صح وغلط
                $normCorrect = mb_strtolower($rawCorrect);
                if (in_array($normCorrect, ['صح', 'صواب', 'true', 'a', '1', 'نعم'])) {
                    $correctKeys = ['true'];
                } else {
                    $correctKeys = ['false'];
                }
            } else {
                // 2. سؤال اختيار من متعدد (Single or Multiple Choice)
                if (empty($optA) || empty($optB)) {
                    $failedCount++;
                    $errors[] = "الصف {$rowNumber}: يجب إدخال الخيار A والخيار B على الأقل.";
                    continue;
                }

                $options = [
                    ['key' => 'A', 'text' => $optA],
                    ['key' => 'B', 'text' => $optB],
                ];
                if (! empty($optC)) {
                    $options[] = ['key' => 'C', 'text' => $optC];
                }
                if (! empty($optD)) {
                    $options[] = ['key' => 'D', 'text' => $optD];
                }

                // استخراج الإجابة الصحيحة للخيارات
                $upperCorrect = strtoupper($rawCorrect);
                $parts = array_filter(array_map('trim', explode(',', $upperCorrect)));
                $correctKeys = [];

                foreach ($parts as $p) {
                    // معالجة الأرقام مثل 1 -> A, 2 -> B
                    $mapped = match ($p) {
                        '1', 'أ', 'A' => 'A',
                        '2', 'ب', 'B' => 'B',
                        '3', 'ج', 'C' => 'C',
                        '4', 'د', 'D' => 'D',
                        default => $p,
                    };
                    if (in_array($mapped, ['A', 'B', 'C', 'D'])) {
                        $correctKeys[] = $mapped;
                    }
                }

                if (empty($correctKeys)) {
                    $correctKeys = ['A']; // افتراضي إذا لم يحدد
                }

                $type = count($correctKeys) > 1 ? 'multiple_choice' : 'single_choice';
            }

            // مستوى الصعوبة
            $difficulty = match (mb_strtolower($diffText)) {
                'سهل', 'easy' => 'easy',
                'صعب', 'hard' => 'hard',
                default => 'medium',
            };

            if ($defaultMarks <= 0) {
                $defaultMarks = 1.0;
            }

            $batch[] = [
                'stage_id' => $stageId,
                'subject_id' => $subjectId,
                'question_text' => $questionText,
                'question_image' => null,
                'type' => $type,
                'options' => json_encode($options, JSON_UNESCAPED_UNICODE),
                'correct_answers' => json_encode(array_values(array_unique($correctKeys)), JSON_UNESCAPED_UNICODE),
                'explanation' => $explanation,
                'default_marks' => $defaultMarks,
                'difficulty' => $difficulty,
                'topic' => $topic,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // إدخال في دفعات (Chunks of 100) لأعلى أداء وسرعة
            if (count($batch) >= 100) {
                DB::table('questions')->insert($batch);
                $insertedCount += count($batch);
                $batch = [];
            }
        }

        // إدخال المتبقي
        if (! empty($batch)) {
            DB::table('questions')->insert($batch);
            $insertedCount += count($batch);
        }

        return [
            'total' => $insertedCount + $failedCount,
            'success' => $insertedCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }
}
