<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BackupService
{
    /**
     * Create a clean SQL database dump backup file.
     */
    public function createDatabaseBackup(): string
    {
        $backupDir = storage_path('app/backups');
        if (! file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_database_{$timestamp}.sql";
        $filePath = "{$backupDir}/{$filename}";

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $tableKey = "Tables_in_{$dbName}";

        $sqlContent = "-- Laravel Database Backup\n";
        $sqlContent .= "-- Generated at: " . now()->toDateTimeString() . "\n\n";
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$tableKey;

            // Get Create Table SQL
            $createSql = DB::select("SHOW CREATE TABLE `{$table}`");
            $sqlContent .= "-- Table structure for `{$table}`\n";
            $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlContent .= $createSql[0]->{'Create Table'} . ";\n\n";

            // Get Data
            $rows = DB::table($table)->get();
            if ($rows->isNotEmpty()) {
                $sqlContent .= "-- Dumping data for `{$table}`\n";
                foreach ($rows as $row) {
                    $array = (array) $row;
                    $keys = array_map(fn ($k) => "`{$k}`", array_keys($array));
                    $values = array_map(function ($val) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return "'" . addslashes($val) . "'";
                    }, array_values($array));

                    $sqlContent .= "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }

        $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($filePath, $sqlContent);

        return $filePath;
    }

    /**
     * List all available database backup files.
     */
    public function listBackups(): array
    {
        $backupDir = storage_path('app/backups');
        if (! file_exists($backupDir)) {
            return [];
        }

        $files = glob("{$backupDir}/*.sql");
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => round(filesize($file) / 1024, 2) . ' KB',
                'created_at' => Carbon::createFromTimestamp(filemtime($file))->toDateTimeString(),
            ];
        }

        // Sort latest first
        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }
}
