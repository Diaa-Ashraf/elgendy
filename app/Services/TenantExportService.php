<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class TenantExportService
{
    /**
     * تصدير كافة بيانات المدرس في ملف مضغوط ZIP يحتوي على ملفات CSV
     */
    public function exportAll(Tenant $tenant): string
    {
        $tenantDir = "tenants/{$tenant->id}/exports";
        $directoryPath = storage_path("app/{$tenantDir}");
        \Illuminate\Support\Facades\File::ensureDirectoryExists($directoryPath);

        $zipFileName = "export_tenant_{$tenant->slug}_" . now()->format('Ymd_His') . ".zip";
        $zipFullPath = $directoryPath . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("تعذر إنشاء الملف المضغوط للتصدير: " . $zipFullPath);
        }

        // 1. تصدير الطلاب
        $studentsCsv = $this->generateCsv(
            ['ID', 'الاسم', 'الهاتف', 'هاتف ولي الأمر', 'المرحلة', 'كود QR', 'تاريخ التسجيل'],
            $tenant->students()->with('educationalStage')->get()->map(function ($student) {
                return [
                    $student->id,
                    $student->name,
                    $student->phone,
                    $student->parent_phone,
                    $student->educationalStage?->name ?? '',
                    $student->qr_code,
                    $student->created_at->format('Y-m-d H:i'),
                ];
            })->toArray()
        );
        $zip->addFromString('students.csv', $studentsCsv);

        // 2. تصدير المجموعات
        $groupsCsv = $this->generateCsv(
            ['ID', 'اسم المجموعة', 'المرحلة', 'السعر الشهري', 'الحد الأقصى', 'الحالة'],
            $tenant->groups()->with('educationalStage')->get()->map(function ($group) {
                return [
                    $group->id,
                    $group->name,
                    $group->educationalStage?->name ?? '',
                    $group->price_per_month,
                    $group->capacity,
                    $group->status,
                ];
            })->toArray()
        );
        $zip->addFromString('groups.csv', $groupsCsv);

        // 3. تصدير المدفوعات
        $paymentsCsv = $this->generateCsv(
            ['ID', 'الطالب', 'المبلغ', 'شهر الاستحقاق', 'تاريخ السداد', 'طريقة الدفع'],
            \App\Models\StudentPayment::where('tenant_id', $tenant->id)->with('student')->get()->map(function ($p) {
                return [
                    $p->id,
                    $p->student?->name ?? '',
                    $p->amount,
                    $p->period_month ? $p->period_month->format('Y-m') : '',
                    $p->paid_at ? $p->paid_at->format('Y-m-d') : '',
                    $p->payment_method,
                ];
            })->toArray()
        );
        $zip->addFromString('payments.csv', $paymentsCsv);

        $zip->close();

        return "{$tenantDir}/{$zipFileName}";
    }

    private function generateCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://memory', 'r+');
        // كتابة UTF-8 BOM لدعم اللغة العربية في Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
