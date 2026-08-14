<?php

namespace App\Filament\Pages;

use App\Services\BackupService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Backups extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-down';

    protected static ?string $navigationLabel = 'النسخ الاحتياطي';

    protected static ?string $title = 'إدارة النسخ الاحتياطي لقواعد البيانات';

    protected static ?string $navigationGroup = 'إدارة النظام والسلطات';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.backups';

    public array $backups = [];

    public function mount(): void
    {
        $this->loadBackups();
    }

    public function loadBackups(): void
    {
        $backupService = app(BackupService::class);
        $this->backups = $backupService->listBackups();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || $user->email === 'admin@admin.com';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createBackup')
                ->label('إنشاء نسخة احتياطية الآن (SQL Dump)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (BackupService $backupService): void {
                    $filePath = $backupService->createDatabaseBackup();
                    $this->loadBackups();

                    Notification::make()
                        ->title('تم إنشاء النسخة الاحتياطية بنجاح')
                        ->body('اسم الملف: ' . basename($filePath))
                        ->success()
                        ->send();
                }),
        ];
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $filePath = storage_path("app/backups/{$filename}");
        return response()->download($filePath);
    }

    public function deleteBackup(string $filename): void
    {
        $filePath = storage_path("app/backups/{$filename}");
        if (file_exists($filePath)) {
            unlink($filePath);
            $this->loadBackups();

            Notification::make()
                ->title('تم حذف ملف النسخة الاحتياطية')
                ->success()
                ->send();
        }
    }
}
