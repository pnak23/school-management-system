<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\BackupJob;
use App\Jobs\RestoreJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Policies\QueueWorkerPolicy;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class Backup_Modern_Controller extends Controller
{
    use LogsActivity;
    
    // Note: Access control is handled by route middleware (role:admin)
    // No need for constructor middleware in Laravel 11
    /**
     * Show the backup settings form with modern layout.
     */
    public function index()
    {
        // Retrieve the current schedule settings from the database
        $schedule = DB::table('backup_schedules')->first();

        // Define the base backup directory
        $baseBackupDir = storage_path('app/backups');

        // Get list of existing backup directories
        $backupDirectories = [];
        if (file_exists($baseBackupDir)) {
            $directories = scandir($baseBackupDir);
            foreach ($directories as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($baseBackupDir . DIRECTORY_SEPARATOR . $dir)) {
                    $backupDirectories[] = $dir;
                }
            }
        }

        // Check if queue worker is running
        $queueWorkerRunning = $this->isQueueWorkerRunning();

        return view('backup_view.backup_modern', compact('schedule', 'backupDirectories', 'queueWorkerRunning'));
    }

    /**
     * Run a backup operation.
     */
    public function backup(Request $request)
    {
        $backupDir = $request->input('backup_directory');

        if (!$backupDir) {
            return response()->json(['status' => 'error', 'message' => 'Backup directory is required.'], 400);
        }

        // Clear previous progress and set initial progress
        Cache::forget('backup_progress');
        Cache::put('backup_progress', 0, now()->addHours(2));

        // Dispatch the backup job
        BackupJob::dispatch($backupDir, 'full');

        // Log the activity
        $this->logActivity(
            'Backup ត្រូវបានចាប់ផ្តើម',
            null,
            [
                'directory' => $backupDir,
                'type' => 'full'
            ],
            'backup'
        );

        return response()->json(['status' => 'success', 'message' => 'Backup started.']);
    }

    /**
     * Run backup as SQL only.
     */
    public function backup_as_sql(Request $request)
    {
        $backupDir = $request->input('backup_directory');

        if (!$backupDir) {
            return response()->json(['status' => 'error', 'message' => 'Backup directory is required.'], 400);
        }

        // Clear previous progress and set initial progress
        Cache::forget('backup_progress');
        Cache::put('backup_progress', 0, now()->addHours(2));

        // Dispatch the backup job
        BackupJob::dispatch($backupDir, 'sql');

        // Log the activity
        $this->logActivity(
            'SQL Backup ត្រូវបានចាប់ផ្តើម',
            null,
            [
                'directory' => $backupDir,
                'type' => 'sql'
            ],
            'backup'
        );

        return response()->json(['status' => 'success', 'message' => 'SQL backup started.']);
    }

    /**
     * Run backup as WinRA format.
     */
    public function backup_as_winra(Request $request)
    {
        $backupDir = $request->input('backup_directory');

        if (!$backupDir) {
            return response()->json(['status' => 'error', 'message' => 'Backup directory is required.'], 400);
        }

        // Clear previous progress and set initial progress
        Cache::forget('backup_progress');
        Cache::put('backup_progress', 0, now()->addHours(2));

        // Dispatch the backup job
        BackupJob::dispatch($backupDir, 'winra');

        // Log the activity
        $this->logActivity(
            'WinRA Backup ត្រូវបានចាប់ផ្តើម',
            null,
            [
                'directory' => $backupDir,
                'type' => 'winra'
            ],
            'backup'
        );

        return response()->json(['status' => 'success', 'message' => 'WinRA backup started.']);
    }

    /**
     * Restore from backup.
     */
    public function restore(Request $request)
    {
        $restoreDir = $request->input('restore_directory');

        if (!$restoreDir) {
            return response()->json(['status' => 'error', 'message' => 'Restore directory is required.'], 400);
        }

        // Clear previous progress
        Cache::forget('backup_progress');

        // Dispatch the restore job
        RestoreJob::dispatch($restoreDir, 'full');

        // Log the activity
        $this->logActivity(
            'Restore ត្រូវបានចាប់ផ្តើម',
            null,
            [
                'directory' => $restoreDir,
                'type' => 'full'
            ],
            'restore'
        );

        return response()->json(['status' => 'success', 'message' => 'Restore started.']);
    }

    /**
     * Restore from SQL backup.
     */
    public function restore_as_sql(Request $request)
    {
        $restoreDir = $request->input('restore_directory');

        if (!$restoreDir) {
            return response()->json(['status' => 'error', 'message' => 'Restore directory is required.'], 400);
        }

        // Clear previous progress
        Cache::forget('backup_progress');

        // Dispatch the restore job
        RestoreJob::dispatch($restoreDir, 'sql');

        // Log the activity
        $this->logActivity(
            'SQL Restore ត្រូវបានចាប់ផ្តើម',
            null,
            [
                'directory' => $restoreDir,
                'type' => 'sql'
            ],
            'restore'
        );

        return response()->json(['status' => 'success', 'message' => 'SQL restore started.']);
    }

    /**
     * Restore from WinRA backup.
     */
    public function restore_as_winra(Request $request)
    {
        $restoreDir = $request->input('restore_directory');

        if (!$restoreDir) {
            return response()->json(['status' => 'error', 'message' => 'Restore directory is required.'], 400);
        }

        // Clear previous progress
        Cache::forget('backup_progress');

        // Dispatch the restore job
        RestoreJob::dispatch($restoreDir, 'winra');

        // Log the activity
        $this->logActivity(
            'WinRA Restore ត្រូវបានចាប់ផ្តើម',
            null,
            [
                'directory' => $restoreDir,
                'type' => 'winra'
            ],
            'restore'
        );

        return response()->json(['status' => 'success', 'message' => 'WinRA restore started.']);
    }

    /**
     * Endpoint to get backup progress.
     */
    public function getBackupProgress()
    {
        try {
            $progress = Cache::get('backup_progress', 0);
            return response()->json([
                'progress' => (float) $progress,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting backup progress: ' . $e->getMessage());
            return response()->json([
                'progress' => 0,
                'status' => 'error',
                'message' => 'Failed to get progress'
            ], 500);
        }
    }

    /**
     * Clean old backups based on type.
     */
    public function clean(Request $request)
    {
        $type = $request->input('type'); // 'zip', 'sql', 'winra'

        if (!$type || !in_array($type, ['zip', 'sql', 'winra'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid backup type specified.'], 400);
        }

        try {
            // Define the base backup directory
            $baseBackupDir = storage_path('app/backups');

            // Get list of backup directories
            $directories = scandir($baseBackupDir);

            foreach ($directories as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($baseBackupDir . DIRECTORY_SEPARATOR . $dir)) {
                    $backupDirPath = $baseBackupDir . DIRECTORY_SEPARATOR . $dir;

                    // Determine the file extension based on type
                    $extension = '';
                    if ($type === 'zip') {
                        $extension = 'zip';
                    } elseif ($type === 'sql') {
                        $extension = 'sql';
                    } elseif ($type === 'winra') {
                        $extension = 'ra';
                    }

                    // Find all backup files of the specified type
                    $backupFiles = glob("$backupDirPath/backup_full_*.$extension");

                    // Sort files by modification time in descending order
                    usort($backupFiles, function ($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });

                    // Keep the newest file and delete the rest
                    if (count($backupFiles) > 1) {
                        $filesToDelete = array_slice($backupFiles, 1);
                        foreach ($filesToDelete as $file) {
                            @unlink($file);
                        }
                    }
                }
            }

            // Log the activity
            $this->logActivity(
                "ការសម្អាត Backup ចាស់ៗប្រភេទ $type ត្រូវបានបញ្ចប់",
                null,
                ['type' => $type],
                'backup'
            );

            Log::info("Old $type backups cleaned successfully.");
            return response()->json(['status' => 'success', 'message' => "Old $type backups cleaned successfully."]);

        } catch (\Exception $e) {
            Log::error('Clean Failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Clean failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Start the Laravel queue worker.
     */
    public function startQueueWorker()
    {
        try {
            // If queue worker is already running, stop it first, then start a new one
            if ($this->isQueueWorkerRunning()) {
                Log::info('Queue worker is already running. Stopping it first...');
                $this->stopQueueWorkerInternal();
                // Wait a moment for process to stop
                sleep(2);
            }

            $artisan = base_path('artisan');
            $phpPath = PHP_BINARY;

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows OS - Use better method to start process
                $pidFile = storage_path('app/queue_worker.pid');
                $logFile = storage_path('logs/queue_worker.log');
                
                // Create a batch file to start the queue worker with continuous loop
                $batchFile = storage_path('app/start_queue_worker.bat');
                $batchContent = "@echo off\n";
                $batchContent .= "cd /d \"" . base_path() . "\"\n";
                $batchContent .= ":loop\n";
                $batchContent .= "\"" . $phpPath . "\" artisan queue:work --sleep=3 --tries=3 --timeout=300 >> \"" . $logFile . "\" 2>&1\n";
                $batchContent .= "if %errorlevel% neq 0 (\n";
                $batchContent .= "    timeout /t 5 /nobreak >nul\n";
                $batchContent .= "    goto loop\n";
                $batchContent .= ")\n";
                file_put_contents($batchFile, $batchContent);

                // Start the process using start command with window hidden
                // Use PowerShell to start process in background properly
                $psCommand = 'powershell -Command "Start-Process -FilePath \'' . $batchFile . '\' -WindowStyle Hidden"';
                exec($psCommand, $psOutput, $psReturn);

                // Wait a moment for process to start
                sleep(3);
                
                // Find the process ID
                $pids = $this->findQueueWorkerPids();
                if (!empty($pids)) {
                    // Save the first PID found
                    file_put_contents($pidFile, $pids[0]);
                    Cache::put('queue_worker_running', true, now()->addDays(1));
                    Log::info('Queue worker started with PID: ' . $pids[0]);
                } else {
                    // Try alternative method using WScript
                    $vbsFile = storage_path('app/start_queue_worker.vbs');
                    $vbsContent = 'Set WshShell = CreateObject("WScript.Shell")\n';
                    $vbsContent .= 'WshShell.Run "cmd /c \"' . str_replace('\\', '\\\\', $batchFile) . '\"", 0, False\n';
                    file_put_contents($vbsFile, $vbsContent);
                    
                    exec('cscript //nologo "' . $vbsFile . '"', $vbsOutput, $vbsReturn);
                    sleep(3);
                    
                    $pids = $this->findQueueWorkerPids();
                    if (!empty($pids)) {
                        file_put_contents($pidFile, $pids[0]);
                        Cache::put('queue_worker_running', true, now()->addDays(1));
                        Log::info('Queue worker started with PID (VBS method): ' . $pids[0]);
                    } else {
                        // Set cache flag anyway - process might be starting
                Cache::put('queue_worker_running', true, now()->addDays(1));
                        Log::warning('Queue worker started but PID not found. Process may be starting.');
                    }
                }
            } else {
                // Unix-like OS
                $logFile = storage_path('logs/queue_worker.log');
                $command = sprintf(
                    'nohup %s "%s" queue:work --sleep=3 --tries=3 --timeout=300 >> "%s" 2>&1 & echo $!',
                    $phpPath,
                    $artisan,
                    $logFile
                );
                $output = [];
                exec($command, $output);

                // Save the process ID (PID) to a file for later use
                $pid = (int)($output[0] ?? 0);
                if ($pid > 0) {
                file_put_contents(storage_path('app/queue_worker.pid'), $pid);
                    Cache::put('queue_worker_running', true, now()->addDays(1));
                    Log::info('Queue worker started with PID: ' . $pid);
                } else {
                    throw new \Exception('Failed to get process ID.');
                }
            }

            // Log activity
            $this->logActivity(
                "Queue Worker ត្រូវបានចាប់ផ្តើម",
                null,
                ['os' => PHP_OS, 'php_version' => PHP_VERSION],
                'system'
            );

            Log::info('Queue worker started via web interface.');
            return response()->json([
                'status' => 'success',
                'message' => 'Queue worker started successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start queue worker: ' . $e->getMessage());
            Cache::forget('queue_worker_running');
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start queue worker: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop the Laravel queue worker (internal method, no response).
     */
    protected function stopQueueWorkerInternal()
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows OS
                $pids = $this->findQueueWorkerPids();
                
                // Also check PID file
                $pidFile = storage_path('app/queue_worker.pid');
                if (file_exists($pidFile)) {
                    $savedPid = trim(file_get_contents($pidFile));
                    if ($savedPid && !in_array($savedPid, $pids)) {
                        $pids[] = $savedPid;
                    }
                }

                if (!empty($pids)) {
                    foreach ($pids as $pid) {
                        if ($pid) {
                            exec("taskkill /F /PID $pid 2>nul");
                    }
                    }
                }
                
                // Remove PID file
                if (file_exists($pidFile)) {
                    @unlink($pidFile);
                }
                
                    // Remove the running flag
                    Cache::forget('queue_worker_running');
                Log::info('Queue worker stopped internally on Windows. PIDs: ' . implode(', ', $pids));
            } else {
                // Unix-like OS
                $pidFile = storage_path('app/queue_worker.pid');
                if (file_exists($pidFile)) {
                    $pid = (int)file_get_contents($pidFile);
                    if ($pid) {
                        exec("kill $pid 2>/dev/null");
                        unlink($pidFile);
                    }
                }
                Cache::forget('queue_worker_running');
                Log::info('Queue worker stopped internally.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to stop queue worker internally: ' . $e->getMessage());
        }
    }

    /**
     * Stop the Laravel queue worker.
     */
    public function stopQueueWorker()
    {
        try {
            $this->stopQueueWorkerInternal();
                        return response()->json(['status' => 'success', 'message' => 'Queue worker stopped successfully.']);
        } catch (\Exception $e) {
            Log::error('Failed to stop queue worker: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to stop queue worker: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Find queue worker process IDs (Windows only).
     */
    protected function findQueueWorkerPids()
    {
        $pids = [];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Use WMIC to find the queue worker process
            $command = 'wmic process where (CommandLine like "%php%" and CommandLine like "%artisan%queue:work%") get ProcessId';
            exec($command, $output);
            
            foreach ($output as $line) {
                if (preg_match('/\d+/', $line, $matches)) {
                    $pids[] = $matches[0];
                }
            }
        }
        return $pids;
    }

    /**
     * Check if the queue worker is running.
     */
    protected function isQueueWorkerRunning()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows OS - Check both cache and actual process
            $cacheRunning = Cache::get('queue_worker_running', false);
            
            // Also verify the process is actually running
            $pids = $this->findQueueWorkerPids();
            $processRunning = !empty($pids);
            
            // If cache says running but process is not, update cache
            if ($cacheRunning && !$processRunning) {
                Cache::forget('queue_worker_running');
                return false;
            }
            
            // If process is running but cache is not, update cache
            if ($processRunning && !$cacheRunning) {
                Cache::put('queue_worker_running', true, now()->addDays(1));
            }
            
            return $processRunning || $cacheRunning;
        } else {
            // Unix-like OS
            $pidFile = storage_path('app/queue_worker.pid');
            if (file_exists($pidFile)) {
                $pid = (int)file_get_contents($pidFile);
                if ($pid) {
                    $result = shell_exec(sprintf("ps -p %d", $pid));
                    if ($result && count(preg_split("/\n/", $result)) > 2) {
                        return true;
                    } else {
                        // Process not running, remove PID file
                        @unlink($pidFile);
                    }
                }
            }
            return false;
        }
    }
} 