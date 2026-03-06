<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a MySQL database backup using mysqldump and clean up old backups';

    /**
     * Number of days to keep old backups before automatic deletion.
     *
     * @var int
     */
    protected $retentionDays = 30;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        try {
            // 1. Read DB credentials from config (sourced from .env)
            $host     = config('database.connections.mysql.host', '127.0.0.1');
            $port     = config('database.connections.mysql.port', '3306');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            if (empty($database)) {
                $this->error('Database name is not configured. Check your .env file (DB_DATABASE).');
                Log::error('Database backup failed: DB_DATABASE is empty.');
                return Command::FAILURE;
            }

            // 2. Ensure the backup directory exists
            $backupDir = storage_path('app/backups');

            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
                $this->info("Created backup directory: {$backupDir}");
            }

            // 3. Build the filename with date-time
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $fileName  = "backup-{$timestamp}.sql";
            $filePath  = $backupDir . DIRECTORY_SEPARATOR . $fileName;

            // 4. Locate mysqldump – check common XAMPP paths on Windows, fallback to PATH
            $mysqldump = $this->findMysqldump();

            // 5. Build the mysqldump command
            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s --single-transaction --routines --triggers --quick "%s" > "%s" 2>&1',
                $mysqldump,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password ? '--password=' . escapeshellarg($password) : '',
                $database,
                $filePath
            );

            // 6. Execute the dump
            $output = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $errorMsg = implode("\n", $output);
                $this->error("mysqldump failed (exit code {$exitCode}):");
                $this->error($errorMsg);
                Log::error("Database backup failed. Exit code: {$exitCode}. Output: {$errorMsg}");

                // Remove empty/corrupt file if created
                if (file_exists($filePath) && filesize($filePath) === 0) {
                    unlink($filePath);
                }

                return Command::FAILURE;
            }

            // Verify the file was actually created and is not empty
            if (!file_exists($filePath) || filesize($filePath) === 0) {
                $this->error('Backup file was not created or is empty.');
                Log::error('Database backup failed: output file is missing or empty.');
                return Command::FAILURE;
            }

            $sizeMB = round(filesize($filePath) / 1024 / 1024, 2);
            $this->info("Backup created successfully: {$fileName} ({$sizeMB} MB)");
            Log::info("Database backup created: {$fileName} ({$sizeMB} MB)");

            // 7. Clean up old backups
            $this->deleteOldBackups($backupDir);

            // 8. Clean up old changelog files
            $this->deleteOldChangelogs(storage_path('app/db-changelog'));

            $this->info('Database backup completed.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            Log::error('Database backup exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Locate the mysqldump executable.
     *
     * Checks common XAMPP installation paths on Windows first,
     * then falls back to expecting mysqldump in the system PATH.
     *
     * @return string
     */
    protected function findMysqldump(): string
    {
        // Common XAMPP paths (Windows)
        $possiblePaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'D:\\xampp\\mysql\\bin\\mysqldump.exe',
            'E:\\xampp\\mysql\\bin\\mysqldump.exe',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Fallback: assume mysqldump is in system PATH
        return 'mysqldump';
    }

    /**
     * Delete backup files older than the retention period.
     *
     * @param string $backupDir
     * @return void
     */
    protected function deleteOldBackups(string $backupDir): void
    {
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup-*.sql');
        $cutoff = Carbon::now()->subDays($this->retentionDays)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
                $this->line("  Deleted old backup: " . basename($file));
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old backup(s) (older than {$this->retentionDays} days).");
            Log::info("Deleted {$deleted} old database backup(s).");
        } else {
            $this->info('No old backups to clean up.');
        }
    }

    /**
     * Delete changelog files older than the retention period.
     *
     * @param string $changelogDir
     * @return void
     */
    protected function deleteOldChangelogs(string $changelogDir): void
    {
        if (!is_dir($changelogDir)) {
            return;
        }

        $files = glob($changelogDir . DIRECTORY_SEPARATOR . 'changelog-*.sql');
        $cutoff = Carbon::now()->subDays($this->retentionDays)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
                $this->line("  Deleted old changelog: " . basename($file));
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old changelog(s).");
            Log::info("Deleted {$deleted} old changelog file(s).");
        }
    }
}
