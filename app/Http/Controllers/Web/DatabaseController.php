<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    protected $exportPath = 'exports';
    protected $maxFileSize = 100 * 1024 * 1024; // 100MB

    /**
     * Display the database tools dashboard
     */
    public function index()
    {
        // Get database information
        $databaseInfo = $this->getDatabaseInfo();

        // Get recent export files
        $exportFiles = $this->getExportFiles();

        // Get available tables
        $tables = $this->getAvailableTables();

        return view('database.index', compact('databaseInfo', 'exportFiles', 'tables'));
    }

    /**
     * Export database in SQL format
     */
    public function exportSql(Request $request)
    {
        $request->validate([
            'tables' => 'nullable|array',
            'tables.*' => 'string',
            'include_data' => 'boolean',
            'compress' => 'boolean'
        ]);

        $tables = $request->input('tables', []);
        $includeData = $request->boolean('include_data', true);
        $compress = $request->boolean('compress', false);

        try {
            $filename = 'database_export_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = storage_path('app/' . $this->exportPath . '/' . $filename);

            // Ensure directory exists
            $exportDir = dirname($filepath);
            if (!File::exists($exportDir)) {
                File::makeDirectory($exportDir, 0755, true);
            }

            // Generate SQL dump
            $sql = $this->generateSqlDump($tables, $includeData);

            // Save file
            File::put($filepath, $sql);

            // Compress if requested
            if ($compress) {
                $zipFilename = str_replace('.sql', '.zip', $filename);
                $zipPath = $this->createZip([$filepath], $zipFilename);
                File::delete($filepath); // Remove original SQL file
                $filepath = $zipPath;
                $filename = $zipFilename;
            }

            return response()->download($filepath)->deleteFileAfterSend();

        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export database in CSV format
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'tables' => 'required|array|min:1',
            'tables.*' => 'string',
            'compress' => 'boolean'
        ]);

        $tables = $request->tables;
        $compress = $request->boolean('compress', false);

        try {
            $files = [];

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $data = DB::table($table)->get();
                $filename = $table . '_' . date('Y-m-d_H-i-s') . '.csv';
                $filepath = storage_path('app/' . $this->exportPath . '/' . $filename);

                $csv = $this->arrayToCsv($data->toArray());
                File::put($filepath, $csv);

                $files[] = $filepath;
            }

            if (count($files) === 1) {
                return response()->download($files[0])->deleteFileAfterSend();
            } else {
                $zipFilename = 'tables_export_' . date('Y-m-d_H-i-s') . '.zip';
                $zipPath = $this->createZip($files, $zipFilename);

                // Clean up individual CSV files
                foreach ($files as $file) {
                    File::delete($file);
                }

                return response()->download($zipPath)->deleteFileAfterSend();
            }

        } catch (\Exception $e) {
            return back()->with('error', 'CSV export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export database in JSON format
     */
    public function exportJson(Request $request)
    {
        $request->validate([
            'tables' => 'required|array|min:1',
            'tables.*' => 'string'
        ]);

        $tables = $request->tables;

        try {
            $exportData = [];

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $data = DB::table($table)->get()->toArray();
                $exportData[$table] = $data;
            }

            $filename = 'database_export_' . date('Y-m-d_H-i-s') . '.json';
            $filepath = storage_path('app/' . $this->exportPath . '/' . $filename);

            File::put($filepath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->download($filepath)->deleteFileAfterSend();

        } catch (\Exception $e) {
            return back()->with('error', 'JSON export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import database from SQL file
     */
    public function importSql(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:51200', // 50MB max
            'backup_first' => 'boolean'
        ]);

        $file = $request->file('sql_file');

        try {
            // Create backup if requested
            if ($request->boolean('backup_first')) {
                $this->createBackup('pre_import_backup_' . date('Y-m-d_H-i-s'));
            }

            // Read and execute SQL file
            $sql = File::get($file->getRealPath());

            // Split SQL into individual statements
            $statements = $this->splitSqlStatements($sql);

            DB::beginTransaction();

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !str_starts_with(strtoupper($statement), 'SELECT')) {
                    DB::statement($statement);
                }
            }

            DB::commit();

            return back()->with('success', 'SQL import completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'SQL import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import database from CSV file
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:51200',
            'table' => 'required|string',
            'has_headers' => 'boolean',
            'backup_first' => 'boolean'
        ]);

        $file = $request->file('csv_file');
        $table = $request->table;
        $hasHeaders = $request->boolean('has_headers', true);

        try {
            // Create backup if requested
            if ($request->boolean('backup_first')) {
                $this->createBackup('pre_csv_import_backup_' . date('Y-m-d_H-i-s'));
            }

            // Read CSV data
            $data = $this->csvToArray($file->getRealPath(), $hasHeaders);

            if (empty($data)) {
                return back()->with('error', 'CSV file is empty or invalid');
            }

            DB::beginTransaction();

            // Clear table if requested
            if ($request->boolean('clear_table')) {
                DB::table($table)->truncate();
            }

            // Insert data in chunks
            $chunks = array_chunk($data, 100);
            foreach ($chunks as $chunk) {
                DB::table($table)->insert($chunk);
            }

            DB::commit();

            return back()->with('success', 'CSV import completed! ' . count($data) . ' records imported.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'CSV import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download export file
     */
    public function download($filename)
    {
        $filepath = storage_path('app/' . $this->exportPath . '/' . $filename);

        if (!File::exists($filepath)) {
            abort(404);
        }

        return response()->download($filepath);
    }

    /**
     * Delete export file
     */
    public function deleteFile($filename)
    {
        $filepath = storage_path('app/' . $this->exportPath . '/' . $filename);

        if (File::exists($filepath)) {
            File::delete($filepath);
        }

        return back()->with('success', 'File deleted successfully!');
    }

    // Helper methods

    private function getDatabaseInfo()
    {
        $database = config('database.connections.mysql.database');
        $tables = DB::select("SHOW TABLES FROM `{$database}`");
        $tableNames = array_column($tables, "Tables_in_{$database}");
        $totalRecords = 0;

        foreach ($tableNames as $table) {
            $totalRecords += DB::table($table)->count();
        }

        return [
            'name' => $database,
            'tables_count' => count($tableNames),
            'total_records' => $totalRecords,
            'size' => $this->getDatabaseSize()
        ];
    }

    private function getDatabaseSize()
    {
        $dbName = config('database.connections.mysql.database');
        $result = DB::select("SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = ?", [$dbName]);

        return $result[0]->size_mb ?? 0;
    }

    private function getExportFiles()
    {
        $exportDir = storage_path('app/' . $this->exportPath);

        if (!File::exists($exportDir)) {
            return [];
        }

        $files = File::files($exportDir);

        return collect($files)->map(function ($file) {
            return [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'date' => $file->getMTime(),
                'size_human' => $this->formatBytes($file->getSize())
            ];
        })->sortByDesc('date')->take(10)->values()->all();
    }

    private function getAvailableTables()
    {
        $database = config('database.connections.mysql.database');
        $tables = DB::select("SHOW TABLES FROM `{$database}`");
        $tableNames = array_column($tables, "Tables_in_{$database}");

        return collect($tableNames)->map(function ($table) {
            return [
                'name' => $table,
                'records' => DB::table($table)->count(),
                'size' => $this->getTableSize($table)
            ];
        })->sortBy('name')->values()->all();
    }

    private function getTableSize($table)
    {
        $dbName = config('database.connections.mysql.database');
        $result = DB::select("SELECT
            ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = ? AND table_name = ?", [$dbName, $table]);

        return $result[0]->size_mb ?? 0;
    }

    private function generateSqlDump($tables = [], $includeData = true)
    {
        $dbName = config('database.connections.mysql.database');

        // Get all tables if none specified
        if (empty($tables)) {
            $tableList = DB::select("SHOW TABLES FROM `{$dbName}`");
            $tables = array_column($tableList, "Tables_in_{$dbName}");
        }

        $sql = "-- Mafaza Fortuna Database Export\n";
        $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$dbName}\n\n";

        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            // Create table structure
            $createTableSql = DB::select("SHOW CREATE TABLE `{$table}`");
            if (isset($createTableSql[0]->{'Create Table'})) {
                $sql .= "-- Table structure for `{$table}`\n";
                $sql .= $createTableSql[0]->{'Create Table'} . ";\n\n";

                // Insert data if requested
                if ($includeData) {
                    $data = DB::table($table)->get();
                    if ($data->count() > 0) {
                        $sql .= "-- Data for `{$table}`\n";
                        foreach ($data as $row) {
                            $values = array_map(function ($value) {
                                return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                            }, (array) $row);

                            $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                        }
                        $sql .= "\n";
                    }
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        return $sql;
    }

    private function arrayToCsv($data)
    {
        if (empty($data)) return '';

        $csv = '';
        $headers = array_keys((array) $data[0]);

        // Add headers
        $csv .= implode(',', array_map(function ($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers)) . "\n";

        // Add data rows
        foreach ($data as $row) {
            $row = (array) $row;
            $csv .= implode(',', array_map(function ($value) {
                return '"' . str_replace('"', '""', $value ?? '') . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }

    private function csvToArray($filepath, $hasHeaders = true)
    {
        $data = [];
        $handle = fopen($filepath, 'r');

        if ($handle !== false) {
            $headers = [];
            $rowNumber = 0;

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rowNumber++;

                if ($hasHeaders && $rowNumber === 1) {
                    $headers = $row;
                    continue;
                }

                if ($hasHeaders && !empty($headers)) {
                    $data[] = array_combine($headers, $row);
                } else {
                    $data[] = $row;
                }
            }

            fclose($handle);
        }

        return $data;
    }

    private function createZip($files, $zipFilename)
    {
        $zipPath = storage_path('app/' . $this->exportPath . '/' . $zipFilename);
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                if (File::exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        }

        return $zipPath;
    }

    private function splitSqlStatements($sql)
    {
        $statements = [];
        $sql = str_replace("\r\n", "\n", $sql);
        $sql = str_replace("\r", "\n", $sql);

        $lines = explode("\n", $sql);
        $currentStatement = '';
        $inString = false;
        $stringChar = '';

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (str_starts_with($line, '--') || str_starts_with($line, '#') || empty($line)) {
                continue;
            }

            // Handle string literals
            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];

                if (!$inString && ($char === '"' || $char === "'")) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($inString && $char === $stringChar && $line[$i - 1] !== '\\') {
                    $inString = false;
                    $stringChar = '';
                }
            }

            $currentStatement .= $line . "\n";

            // Check for statement end (semicolon not in string)
            if (!$inString && str_contains($line, ';')) {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            }
        }

        // Add remaining statement if any
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        return $statements;
    }

    private function createBackup($filename)
    {
        $backupFile = storage_path('app/' . $this->exportPath . '/' . $filename . '.sql');
        $sql = $this->generateSqlDump([], true);
        File::put($backupFile, $sql);
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
