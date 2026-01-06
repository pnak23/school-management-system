<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutes

    protected $backupDir;
    protected $type; // 'full', 'sql', 'winra'

    /**
     * Create a new job instance.
     */
    public function __construct($backupDir, $type = 'full')
    {
        $this->backupDir = $backupDir;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if backup is already in progress (prevent duplicates)
            $backupLockKey = "backup_in_progress_{$this->backupDir}_{$this->type}";
            if (Cache::has($backupLockKey)) {
                Log::warning("Backup already in progress: {$this->type} in {$this->backupDir}. Skipping duplicate job.");
                return;
            }
            
            // Set lock to prevent duplicate backups
            Cache::put($backupLockKey, true, now()->addHours(1));
            
            // Set initial progress
            Cache::put('backup_progress', 0, now()->addHours(2));
            Log::info("Backup job started: {$this->type} in {$this->backupDir}");
            
            $baseBackupDir = storage_path('app/backups');
            $fullBackupPath = $baseBackupDir . DIRECTORY_SEPARATOR . $this->backupDir;
            
            // Create directory if it doesn't exist
            if (!file_exists($fullBackupPath)) {
                File::makeDirectory($fullBackupPath, 0755, true);
            }

            Cache::put('backup_progress', 10, now()->addHours(2));

            if ($this->type === 'sql') {
                $this->backupDatabase($fullBackupPath);
            } elseif ($this->type === 'winra') {
                $this->backupAsWinRA($fullBackupPath);
            } else {
                $this->backupFull($fullBackupPath);
            }

            // Ensure progress reaches 100%
            Cache::put('backup_progress', 100, now()->addHours(2));
            Log::info("Backup completed successfully: {$this->type} in {$this->backupDir}");
            
            // Release lock
            $backupLockKey = "backup_in_progress_{$this->backupDir}_{$this->type}";
            Cache::forget($backupLockKey);
            
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            Cache::put('backup_progress', 0, now()->addHours(2));
            Cache::put('backup_error', $e->getMessage(), now()->addHours(2));
            
            // Release lock on error
            $backupLockKey = "backup_in_progress_{$this->backupDir}_{$this->type}";
            Cache::forget($backupLockKey);
            
            throw $e;
        }
    }

    /**
     * Backup database only
     */
    protected function backupDatabase($backupPath): void
    {
        Cache::put('backup_progress', 20, now()->addHours(2));
        
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        
        // Use microtime to ensure unique filenames even if called within the same second
        $timestamp = date('Y-m-d_His') . '_' . substr(str_replace('.', '', microtime(true)), -6);
        $filename = "backup_full_{$timestamp}.sql";
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        Cache::put('backup_progress', 40, now()->addHours(2));
        usleep(100000); // Small delay to allow progress update to be visible

        // Try to use Laravel's DB connection for backup if mysqldump is not available
        // First, try mysqldump command
        $mysqldumpPath = $this->findMysqldumpPath();
        
        if ($mysqldumpPath) {
            // Use mysqldump command
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: Use password file or environment variable for security
                $command = sprintf(
                    '"%s" -h %s -P %s -u %s -p%s %s > "%s" 2>&1',
                    $mysqldumpPath,
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($filepath)
                );
            } else {
                // Unix-like: Use password file
                $command = sprintf(
                    '%s -h %s -P %s -u %s -p%s %s > %s 2>&1',
                    $mysqldumpPath,
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($filepath)
                );
            }

            Cache::put('backup_progress', 50, now()->addHours(2));
            usleep(100000);
            
            exec($command, $output, $returnVar);
            
            Cache::put('backup_progress', 80, now()->addHours(2));
            usleep(100000);

            if ($returnVar !== 0) {
                $errorMsg = 'Database backup failed. Return code: ' . $returnVar;
                if (!empty($output)) {
                    $errorMsg .= ' | Output: ' . implode("\n", $output);
                }
                Log::error('Mysqldump failed: ' . $errorMsg);
                // Fall through to Laravel DB backup method
            } else {
                // Verify file was created
                if (file_exists($filepath) && filesize($filepath) > 0) {
                    Cache::put('backup_progress', 100, now()->addHours(2));
                    Log::info("Database backup completed using mysqldump: {$filename} (" . filesize($filepath) . " bytes)");
                    return;
                }
            }
        }
        
        // Fallback: Use Laravel's DB connection to export data
        Log::info('Using Laravel DB connection for backup (mysqldump not available or failed)');
        Cache::put('backup_progress', 50, now()->addHours(2));
        usleep(100000);
        
        $this->backupDatabaseUsingLaravel($filepath, $database);
        
        Cache::put('backup_progress', 100, now()->addHours(2));
        Log::info("Database backup completed using Laravel: {$filename} (" . filesize($filepath) . " bytes)");
    }
    
    /**
     * Find mysqldump executable path
     */
    protected function findMysqldumpPath()
    {
        // Common paths for mysqldump
        $paths = [
            'mysqldump', // In PATH
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql' . PHP_VERSION_ID . '\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
        ];
        
        foreach ($paths as $path) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                if (file_exists($path)) {
                    return $path;
                }
            } else {
                $result = shell_exec("which $path 2>/dev/null");
                if ($result && trim($result)) {
                    return trim($result);
                }
            }
        }
        
        return null;
    }
    
    /**
     * Backup database using Laravel's DB connection
     */
    protected function backupDatabaseUsingLaravel($filepath, $database): void
    {
        $handle = fopen($filepath, 'w');
        if (!$handle) {
            throw new \Exception('Cannot create backup file: ' . $filepath);
        }
        
        Cache::put('backup_progress', 55, now()->addHours(2));
        usleep(200000); // Allow progress update to be visible
        
        // Write header
        fwrite($handle, "-- MySQL Dump\n");
        fwrite($handle, "-- Database: {$database}\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
        fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n\n");
        fwrite($handle, "USE `{$database}`;\n\n");
        
        Cache::put('backup_progress', 60, now()->addHours(2));
        usleep(200000);
        
        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;
        
        $tableCount = count($tables);
        $currentTable = 0;
        
        Cache::put('backup_progress', 65, now()->addHours(2));
        usleep(200000);
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            $currentTable++;
            
            $progress = 65 + (int)(($currentTable / $tableCount) * 30);
            Cache::put('backup_progress', $progress, now()->addHours(2));
            
            // Update progress every 5 tables or on last table
            if ($currentTable % 5 == 0 || $currentTable == $tableCount) {
                usleep(100000); // Small delay to allow progress update
            }
            
            // Drop table
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            
            // Create table
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($createTable)) {
                $createTableSql = $createTable[0]->{'Create Table'};
                fwrite($handle, $createTableSql . ";\n\n");
            }
            
            // Get table data
            $rows = DB::table($tableName)->get();
            if (count($rows) > 0) {
                fwrite($handle, "LOCK TABLES `{$tableName}` WRITE;\n");
                fwrite($handle, "/*!40000 ALTER TABLE `{$tableName}` DISABLE KEYS */;\n");
                
                $chunkSize = 100;
                $chunks = array_chunk($rows->toArray(), $chunkSize);
                
                foreach ($chunks as $chunk) {
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowArray = (array)$row;
                        $escapedValues = array_map(function($value) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return "'" . addslashes($value) . "'";
                        }, array_values($rowArray));
                        $values[] = '(' . implode(',', $escapedValues) . ')';
                    }
                    
                    $columns = array_keys((array)$chunk[0]);
                    $columnsStr = '`' . implode('`,`', $columns) . '`';
                    $valuesStr = implode(",\n", $values);
                    
                    fwrite($handle, "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n{$valuesStr};\n");
                }
                
                fwrite($handle, "/*!40000 ALTER TABLE `{$tableName}` ENABLE KEYS */;\n");
                fwrite($handle, "UNLOCK TABLES;\n\n");
            }
        }
        
        fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");
        
        fclose($handle);
        
        // Verify file was created
        if (!file_exists($filepath) || filesize($filepath) === 0) {
            throw new \Exception('Database backup file was not created or is empty');
        }
    }

    /**
     * Backup as WinRA format
     */
    protected function backupAsWinRA($backupPath): void
    {
        Cache::put('backup_progress', 20, now()->addHours(2));
        
        // Backup database
        $this->backupDatabase($backupPath);
        
        Cache::put('backup_progress', 50, now()->addHours(2));
        
        // Create WinRA file (similar to SQL but with .ra extension)
        // Find the most recent SQL file (should be the one just created)
        $sqlFiles = glob($backupPath . DIRECTORY_SEPARATOR . "backup_full_*.sql");
        
        if (!empty($sqlFiles)) {
            $sqlFile = $sqlFiles[0]; // Get the most recent SQL file
            $raFile = str_replace('.sql', '.ra', $sqlFile);
            
            if (file_exists($sqlFile)) {
                File::copy($sqlFile, $raFile);
                File::delete($sqlFile);
                Log::info("WinRA backup file created: " . basename($raFile));
            }
        }
        
        Cache::put('backup_progress', 100, now()->addHours(2));
    }

    /**
     * Full backup (database + files)
     */
    protected function backupFull($backupPath): void
    {
        Cache::put('backup_progress', 20, now()->addHours(2));
        
        // Backup database
        $this->backupDatabase($backupPath);
        
        Cache::put('backup_progress', 50, now()->addHours(2));
        
        // Backup storage files
        $storagePath = storage_path('app');
        $publicPath = public_path('storage');
        
        // Use the same timestamp format as backupDatabase for consistency
        $timestamp = date('Y-m-d_His') . '_' . substr(str_replace('.', '', microtime(true)), -6);
        
        // Create zip archive
        $zipFile = $backupPath . DIRECTORY_SEPARATOR . "backup_full_{$timestamp}.zip";
        $zip = new \ZipArchive();
        
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            // Add database backup
            $sqlFiles = glob($backupPath . DIRECTORY_SEPARATOR . "backup_full_*.sql");
            foreach ($sqlFiles as $sqlFile) {
                if (file_exists($sqlFile)) {
                    $zip->addFile($sqlFile, basename($sqlFile));
                }
            }
            
            Cache::put('backup_progress', 60, now()->addHours(2));
            
            // Add storage files
            if (is_dir($storagePath)) {
                $this->addDirectoryToZip($storagePath, $zip, 'storage/');
            }
            
            Cache::put('backup_progress', 80, now()->addHours(2));
            
            // Add public storage files
            if (is_dir($publicPath)) {
                $this->addDirectoryToZip($publicPath, $zip, 'public_storage/');
            }
            
            $zip->close();
            
            // Verify zip file was created
            if (!file_exists($zipFile) || filesize($zipFile) === 0) {
                throw new \Exception('Zip backup file was not created or is empty');
            }
            
            Log::info("Full backup completed: " . basename($zipFile) . " (" . filesize($zipFile) . " bytes)");
        } else {
            throw new \Exception('Failed to create zip archive');
        }
        
        Cache::put('backup_progress', 100, now()->addHours(2));
    }

    /**
     * Add directory to zip recursively
     */
    protected function addDirectoryToZip($dir, $zip, $zipPath = ''): void
    {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'backups') {
                continue;
            }
            
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $zipFilePath = $zipPath . $file;
            
            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipFilePath);
                $this->addDirectoryToZip($filePath, $zip, $zipFilePath . '/');
            } else {
                $zip->addFile($filePath, $zipFilePath);
            }
        }
    }
}




