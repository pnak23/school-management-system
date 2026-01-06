<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class RestoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $restoreDir;
    protected $type; // 'full', 'sql', 'winra'

    /**
     * Create a new job instance.
     */
    public function __construct($restoreDir, $type = 'full')
    {
        $this->restoreDir = $restoreDir;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Cache::put('backup_progress', 0);
            
            $baseBackupDir = storage_path('app/backups');
            $fullRestorePath = $baseBackupDir . DIRECTORY_SEPARATOR . $this->restoreDir;
            
            if (!file_exists($fullRestorePath)) {
                throw new \Exception('Restore directory does not exist');
            }

            Cache::put('backup_progress', 10);

            if ($this->type === 'sql') {
                $this->restoreDatabase($fullRestorePath);
            } elseif ($this->type === 'winra') {
                $this->restoreFromWinRA($fullRestorePath);
            } else {
                $this->restoreFull($fullRestorePath);
            }

            Cache::put('backup_progress', 100);
            Log::info("Restore completed: {$this->type} from {$this->restoreDir}");
            
        } catch (\Exception $e) {
            Log::error('Restore failed: ' . $e->getMessage());
            Cache::put('backup_progress', 0);
            throw $e;
        }
    }

    /**
     * Restore database only
     */
    protected function restoreDatabase($restorePath): void
    {
        Cache::put('backup_progress', 20);
        
        // Find the latest SQL file
        $sqlFiles = glob($restorePath . DIRECTORY_SEPARATOR . "backup_full_*.sql");
        if (empty($sqlFiles)) {
            throw new \Exception('No SQL backup file found');
        }
        
        // Get the latest file
        usort($sqlFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $sqlFile = $sqlFiles[0];

        Cache::put('backup_progress', 40);

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Use mysql command to restore
        $command = sprintf(
            'mysql -h %s -u %s -p%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnVar);

        Cache::put('backup_progress', 80);

        if ($returnVar !== 0) {
            throw new \Exception('Database restore failed');
        }

        Cache::put('backup_progress', 100);
    }

    /**
     * Restore from WinRA format
     */
    protected function restoreFromWinRA($restorePath): void
    {
        Cache::put('backup_progress', 20);
        
        // Find the latest RA file
        $raFiles = glob($restorePath . DIRECTORY_SEPARATOR . "backup_full_*.ra");
        if (empty($raFiles)) {
            throw new \Exception('No WinRA backup file found');
        }
        
        // Get the latest file
        usort($raFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $raFile = $raFiles[0];
        
        // Convert RA to SQL temporarily
        $sqlFile = str_replace('.ra', '.sql', $raFile);
        File::copy($raFile, $sqlFile);
        
        Cache::put('backup_progress', 40);
        
        // Restore database
        $this->restoreDatabase($restorePath);
        
        // Clean up temporary SQL file
        if (file_exists($sqlFile)) {
            File::delete($sqlFile);
        }
        
        Cache::put('backup_progress', 100);
    }

    /**
     * Full restore (database + files)
     */
    protected function restoreFull($restorePath): void
    {
        Cache::put('backup_progress', 20);
        
        // Find the latest ZIP file
        $zipFiles = glob($restorePath . DIRECTORY_SEPARATOR . "backup_full_*.zip");
        if (empty($zipFiles)) {
            throw new \Exception('No ZIP backup file found');
        }
        
        // Get the latest file
        usort($zipFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $zipFile = $zipFiles[0];

        Cache::put('backup_progress', 30);

        // Extract ZIP file
        $extractPath = $restorePath . DIRECTORY_SEPARATOR . 'extracted';
        if (file_exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        File::makeDirectory($extractPath, 0755, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new \Exception('Failed to extract ZIP file');
        }

        Cache::put('backup_progress', 50);

        // Restore database from extracted SQL files
        $sqlFiles = glob($extractPath . DIRECTORY_SEPARATOR . "backup_full_*.sql");
        if (!empty($sqlFiles)) {
            usort($sqlFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $sqlFile = $sqlFiles[0];
            
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $command = sprintf(
                'mysql -h %s -u %s -p%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Database restore failed');
            }
        }

        Cache::put('backup_progress', 70);

        // Restore storage files
        $storageSource = $extractPath . DIRECTORY_SEPARATOR . 'storage';
        $storageDest = storage_path('app');
        if (is_dir($storageSource)) {
            File::copyDirectory($storageSource, $storageDest);
        }

        Cache::put('backup_progress', 90);

        // Restore public storage files
        $publicStorageSource = $extractPath . DIRECTORY_SEPARATOR . 'public_storage';
        $publicStorageDest = public_path('storage');
        if (is_dir($publicStorageSource)) {
            File::copyDirectory($publicStorageSource, $publicStorageDest);
        }

        // Clean up extracted files
        File::deleteDirectory($extractPath);

        Cache::put('backup_progress', 100);
    }
}







