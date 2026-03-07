<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class DatabaseChangeLogger
{
    /**
     * Directory where daily change-log SQL files are stored.
     *
     * @var string
     */
    protected string $logDir;

    /**
     * SQL keywords that indicate a write operation.
     *
     * @var array
     */
    protected array $writeKeywords = ['INSERT', 'UPDATE', 'DELETE', 'ALTER', 'DROP', 'CREATE', 'TRUNCATE', 'REPLACE'];

    public function __construct()
    {
        $this->logDir = storage_path('app/db-changelog');
    }

    /**
     * Start listening for database write queries and log them to a daily SQL file.
     *
     * @return void
     */
    public function listen(): void
    {
        // Ensure the log directory exists
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        DB::listen(function ($query) {
            try {
                // Only log write operations (INSERT, UPDATE, DELETE, etc.)
                if (!$this->isWriteQuery($query->sql)) {
                    return;
                }

                // Skip logging changes to the sessions / jobs / cache tables (noise)
                if ($this->isExcludedTable($query->sql)) {
                    return;
                }

                // Build the full SQL with bindings substituted
                $fullSql = $this->buildFullSql($query->sql, $query->bindings);

                // Write to today's changelog file
                $this->writeToFile($fullSql, $query->time);
            } catch (\Exception $e) {
                Log::warning('DatabaseChangeLogger failed to log query: ' . $e->getMessage());
            }
        });
    }

    /**
     * Determine if a query is a write operation.
     *
     * @param string $sql
     * @return bool
     */
    protected function isWriteQuery(string $sql): bool
    {
        $trimmed = strtoupper(trim($sql));

        foreach ($this->writeKeywords as $keyword) {
            if (str_starts_with($trimmed, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Exclude noisy framework tables from logging.
     *
     * @param string $sql
     * @return bool
     */
    protected function isExcludedTable(string $sql): bool
    {
        $excludedTables = ['sessions', 'jobs', 'failed_jobs', 'cache', 'cache_locks', 'password_resets'];

        $lowerSql = strtolower($sql);

        foreach ($excludedTables as $table) {
            // Match table names in backticks or plain
            if (str_contains($lowerSql, "`{$table}`") || preg_match("/\b{$table}\b/", $lowerSql)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Substitute bindings into the SQL query to produce a complete statement.
     *
     * @param string $sql
     * @param array  $bindings
     * @return string
     */
    protected function buildFullSql(string $sql, array $bindings): string
    {
        $fullSql = $sql;

        foreach ($bindings as $binding) {
            $value = $this->formatBinding($binding);
            // Replace the first ? placeholder
            $fullSql = preg_replace('/\?/', $value, $fullSql, 1);
        }

        return $fullSql;
    }

    /**
     * Format a binding value for inclusion in a SQL string.
     *
     * @param mixed $binding
     * @return string
     */
    protected function formatBinding($binding): string
    {
        if (is_null($binding)) {
            return 'NULL';
        }

        if (is_bool($binding)) {
            return $binding ? '1' : '0';
        }

        if (is_int($binding) || is_float($binding)) {
            return (string) $binding;
        }

        if ($binding instanceof \DateTimeInterface) {
            return "'" . $binding->format('Y-m-d H:i:s') . "'";
        }

        // Escape single quotes in string values
        return "'" . addslashes((string) $binding) . "'";
    }

    /**
     * Append the SQL statement to today's changelog file.
     *
     * @param string $sql
     * @param float  $queryTimeMs
     * @return void
     */
    protected function writeToFile(string $sql, float $queryTimeMs): void
    {
        $date     = Carbon::now()->format('Y-m-d');
        $time     = Carbon::now()->format('H:i:s');
        $fileName = "changelog-{$date}.sql";
        $filePath = $this->logDir . DIRECTORY_SEPARATOR . $fileName;

        $entry = "-- [{$time}] ({$queryTimeMs}ms)\n{$sql};\n\n";

        file_put_contents($filePath, $entry, FILE_APPEND | LOCK_EX);
    }
}
