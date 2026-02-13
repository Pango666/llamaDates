<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class BackupController extends Controller
{
    /**
     * Directory where backups are stored.
     */
    protected function backupPath(): string
    {
        $path = storage_path('app/backups');
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
        return $path;
    }

    /**
     * Show the backup management page.
     */
    public function index()
    {
        $backups = $this->getBackupList();
        $cacheStatus = $this->getCacheStatus();

        return view('admin.backups.index', compact('backups', 'cacheStatus'));
    }

    /**
     * Get list of backup files.
     */
    private function getBackupList(): array
    {
        $path = $this->backupPath();
        $files = File::files($path);
        $backups = [];

        foreach ($files as $file) {
            $name = $file->getFilename();
            $backups[] = [
                'name'      => $name,
                'size'      => $this->formatBytes($file->getSize()),
                'size_raw'  => $file->getSize(),
                'date'      => Carbon::createFromTimestamp($file->getMTime())->format('d/m/Y H:i'),
                'timestamp' => $file->getMTime(),
                'type'      => str_contains($name, '_db_') ? 'database' : 'files',
            ];
        }

        // Sort by most recent first
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Get cache/system status info.
     */
    private function getCacheStatus(): array
    {
        return [
            'config_cached' => File::exists(base_path('bootstrap/cache/config.php')),
            'routes_cached' => File::exists(base_path('bootstrap/cache/routes-v7.php')),
            'views_cached'  => count(File::glob(storage_path('framework/views/*.php'))) > 0,
            'views_count'   => count(File::glob(storage_path('framework/views/*.php'))),
            'logs_size'     => $this->formatBytes($this->getLogsSize()),
            'logs_size_raw' => $this->getLogsSize(),
            'backups_size'  => $this->formatBytes($this->getDirectorySize($this->backupPath())),
            'php_version'   => phpversion(),
            'laravel_version' => app()->version(),
            'disk_free'     => $this->formatBytes(disk_free_space(base_path())),
        ];
    }

    /**
     * Create a database backup using mysqldump.
     */
    public function backupDatabase()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);

            $filename = 'backup_db_' . date('Y-m-d_His') . '.sql';
            $filepath = $this->backupPath() . DIRECTORY_SEPARATOR . $filename;

            // Try to find mysqldump in common Laragon paths
            $mysqldump = $this->findMysqldump();

            if (!$mysqldump) {
                return back()->with('error', 'No se encontró mysqldump. Verifica la instalación de MySQL.');
            }

            $passwordArg = !empty($dbPass) ? "-p\"{$dbPass}\"" : '';

            $command = "\"{$mysqldump}\" -h {$dbHost} -P {$dbPort} -u {$dbUser} {$passwordArg} {$dbName} > \"{$filepath}\" 2>&1";

            exec($command, $output, $result);

            if ($result !== 0) {
                Log::error('[Backup] Error ejecutando mysqldump', ['output' => implode("\n", $output), 'code' => $result]);

                // If exec failed, try PHP-based backup
                $this->phpDatabaseBackup($filepath, $dbName);
            }

            if (File::exists($filepath) && File::size($filepath) > 0) {
                Log::info("[Backup] Base de datos respaldada: {$filename}");
                return back()->with('ok', "Respaldo de base de datos creado: {$filename}");
            }

            return back()->with('error', 'Error al crear el respaldo de la base de datos.');
        } catch (\Exception $e) {
            Log::error('[Backup] Exception: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * PHP-based database backup fallback (without mysqldump).
     */
    private function phpDatabaseBackup(string $filepath, string $dbName): void
    {
        $pdo = \DB::connection()->getPdo();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        $sql = "-- DentalCare Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$dbName}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Structure
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $create['Create Table'] . ";\n\n";

            // Data
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $colsStr = '`' . implode('`, `', $columns) . '`';

                foreach (array_chunk($rows, 500) as $chunk) {
                    $sql .= "INSERT INTO `{$table}` ({$colsStr}) VALUES\n";
                    $values = [];
                    foreach ($chunk as $row) {
                        $vals = array_map(function ($v) use ($pdo) {
                            return $v === null ? 'NULL' : $pdo->quote($v);
                        }, array_values($row));
                        $values[] = '(' . implode(', ', $vals) . ')';
                    }
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        File::put($filepath, $sql);
    }

    /**
     * Create a backup of important files (uploads, .env, config).
     */
    public function backupFiles()
    {
        try {
            $filename = 'backup_files_' . date('Y-m-d_His') . '.zip';
            $filepath = $this->backupPath() . DIRECTORY_SEPARATOR . $filename;

            $zip = new \ZipArchive();
            if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'No se pudo crear el archivo ZIP.');
            }

            // Backup .env
            $envPath = base_path('.env');
            if (File::exists($envPath)) {
                $zip->addFile($envPath, '.env');
            }

            // Backup uploads (storage/app/public)
            $uploadsPath = storage_path('app/public');
            if (File::isDirectory($uploadsPath)) {
                $this->addDirectoryToZip($zip, $uploadsPath, 'storage/app/public');
            }

            // Backup config files
            $configPath = config_path();
            if (File::isDirectory($configPath)) {
                $this->addDirectoryToZip($zip, $configPath, 'config');
            }

            $zip->close();

            if (File::exists($filepath) && File::size($filepath) > 0) {
                Log::info("[Backup] Archivos respaldados: {$filename}");
                return back()->with('ok', "Respaldo de archivos creado: {$filename}");
            }

            return back()->with('error', 'Error al crear el respaldo de archivos.');
        } catch (\Exception $e) {
            Log::error('[Backup] Files Exception: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file.
     */
    public function download(string $filename)
    {
        $filepath = $this->backupPath() . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($filepath)) {
            return back()->with('error', 'El archivo no existe.');
        }

        return response()->download($filepath);
    }

    /**
     * Delete a backup file.
     */
    public function delete(string $filename)
    {
        $filepath = $this->backupPath() . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($filepath)) {
            File::delete($filepath);
            Log::info("[Backup] Eliminado: {$filename}");
            return back()->with('ok', "Respaldo eliminado: {$filename}");
        }

        return back()->with('error', 'El archivo no existe.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Log::info('[System] Cache limpiada');
            return back()->with('ok', 'Caché de aplicación limpiada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Clear config cache.
     */
    public function clearConfig()
    {
        try {
            Artisan::call('config:clear');
            Log::info('[System] Config cache limpiada');
            return back()->with('ok', 'Caché de configuración limpiada.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Clear compiled views.
     */
    public function clearViews()
    {
        try {
            Artisan::call('view:clear');
            Log::info('[System] Views cache limpiada');
            return back()->with('ok', 'Vistas compiladas limpiadas.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Clear route cache.
     */
    public function clearRoutes()
    {
        try {
            Artisan::call('route:clear');
            Log::info('[System] Route cache limpiada');
            return back()->with('ok', 'Caché de rutas limpiada.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Clear all caches at once (config, cache, views, routes).
     */
    public function clearAll()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Log::info('[System] Todas las cachés limpiadas');
            return back()->with('ok', 'Todas las cachés limpiadas correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Clear old log files.
     */
    public function clearLogs()
    {
        try {
            $logPath = storage_path('logs');
            $files = File::glob($logPath . '/*.log');
            $count = 0;

            foreach ($files as $file) {
                // Keep the main laravel.log but truncate it
                if (basename($file) === 'laravel.log') {
                    File::put($file, '');
                } else {
                    File::delete($file);
                }
                $count++;
            }

            Log::info("[System] Logs limpiados ({$count} archivos)");
            return back()->with('ok', "Logs limpiados ({$count} archivos procesados).");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ─── Helpers ───

    private function findMysqldump(): ?string
    {
        // Common Laragon paths
        $paths = glob('C:\\laragon\\bin\\mysql\\mysql-*\\bin\\mysqldump.exe');
        if (!empty($paths)) {
            return end($paths); // Latest version
        }

        // Try system PATH
        $which = trim(shell_exec('where mysqldump 2>NUL') ?? '');
        if ($which && File::exists($which)) {
            return $which;
        }

        return null;
    }

    private function addDirectoryToZip(\ZipArchive $zip, string $dirPath, string $zipDir): void
    {
        $files = File::allFiles($dirPath);
        foreach ($files as $file) {
            $relativePath = $zipDir . '/' . $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), str_replace('\\', '/', $relativePath));
        }
    }

    private function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    private function getLogsSize(): int
    {
        $size = 0;
        $logPath = storage_path('logs');
        if (File::isDirectory($logPath)) {
            foreach (File::files($logPath) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    private function getDirectorySize(string $path): int
    {
        $size = 0;
        if (File::isDirectory($path)) {
            foreach (File::allFiles($path) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }
}
