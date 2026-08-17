<?php

namespace App\Jobs;

use App\Models\StudentImport;
use App\Services\StudentImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStudentImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600; // 10 minutes max

    public function __construct(
        public StudentImport $import
    ) {}

    public function handle(StudentImportService $importService): void
    {
        $importService->processImport($this->import);
    }

    public function failed(\Throwable $exception): void
    {
        $this->import->update([
            'status' => 'failed',
            'error_message' => 'فشلت المهمة: ' . $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}
