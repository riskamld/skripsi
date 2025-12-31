<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiQueryBuilder
{
    private $allowedTables = [
        'places',
        'product_prices',
        'scrape_logs',
        'api_tokens'
    ];

    private $allowedOperations = [
        'SELECT', 'COUNT', 'AVG', 'SUM', 'MIN', 'MAX',
        'GROUP BY', 'ORDER BY', 'LIMIT', 'WHERE', 'LIKE',
        'AND', 'OR', 'AS', 'DISTINCT', 'FROM', 'JOIN'
    ];

    private $forbiddenKeywords = [
        'DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE',
        'TRUNCATE', 'EXEC', 'EXECUTE', 'UNION SELECT', 'INTO',
        'LOAD_FILE', 'OUTFILE', 'DUMPFILE', 'INFORMATION_SCHEMA'
    ];

    private $queryTimeout = 5; // seconds
    private $maxRows = 1000; // maximum rows to return

    public function executeAiGeneratedQuery($sqlQuery, $userId = null)
    {
        // Validate query safety
        if (!$this->isQuerySafe($sqlQuery)) {
            Log::warning('Unsafe SQL query blocked', [
                'query' => $sqlQuery,
                'user_id' => $userId,
                'ip' => request()->ip()
            ]);
            throw new \Exception('Query tidak aman untuk dieksekusi');
        }

        // Log query for audit
        Log::info('AI Generated Query Executed', [
            'query' => $sqlQuery,
            'user_id' => $userId,
            'timestamp' => now()
        ]);

        try {
            // Set timeout and execute
            DB::statement("SET SESSION max_execution_time = " . ($this->queryTimeout * 1000));

            $results = DB::select($sqlQuery);

            // Limit results if too many
            if (count($results) > $this->maxRows) {
                $results = array_slice($results, 0, $this->maxRows);
                $results[] = ['note' => "Results limited to {$this->maxRows} rows"];
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('AI Query Execution Failed', [
                'query' => $sqlQuery,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw new \Exception('Gagal mengeksekusi query: ' . $e->getMessage());
        }
    }

    public function isQuerySafe($query)
    {
        if (empty($query)) {
            return false;
        }

        $query = strtoupper(trim($query));

        // Must start with SELECT
        if (!str_starts_with($query, 'SELECT')) {
            return false;
        }

        // Check for forbidden keywords
        foreach ($this->forbiddenKeywords as $keyword) {
            if (str_contains($query, strtoupper($keyword))) {
                return false;
            }
        }

        // Extract table names from query
        $tablesInQuery = $this->extractTablesFromQuery($query);

        // Check if all tables are allowed
        foreach ($tablesInQuery as $table) {
            if (!in_array(strtolower($table), $this->allowedTables)) {
                return false;
            }
        }

        // Additional safety checks
        if ($this->containsSuspiciousPatterns($query)) {
            return false;
        }

        return true;
    }

    private function extractTablesFromQuery($query)
    {
        $tables = [];

        // Simple regex to extract table names after FROM and JOIN
        $patterns = [
            '/\bFROM\s+([`\w]+)/i',
            '/\bJOIN\s+([`\w]+)/i',
            '/\bINTO\s+([`\w]+)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $query, $matches)) {
                $tables = array_merge($tables, $matches[1]);
            }
        }

        return array_unique($tables);
    }

    private function containsSuspiciousPatterns($query)
    {
        $suspiciousPatterns = [
            '/;\s*(SELECT|INSERT|UPDATE|DELETE)/i', // Multiple statements
            '/\/\*.+\*\//', // Comments that might hide malicious code
            '/UNION\s+SELECT/i', // Union-based attacks
            '/LOAD_FILE|OUTFILE|DUMPFILE/i', // File operations
            '/INFORMATION_SCHEMA/i', // Metadata access
            '/BENCHMARK|sleep/i', // Time-based attacks
            '/0x[0-9a-f]+/i', // Hex encoded strings
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }

    public function generateSqlFromNaturalLanguage($query, $databaseSchema = null)
    {
        // For server-side PHP, we can't directly call Puter AI
        // This method should be called from JavaScript/frontend
        // Return fallback for now, but in production this would be handled by frontend
        return $this->fallbackSqlGeneration($query);
    }

    public function getAiPromptForSqlGeneration($query, $context = [])
    {
        $schemaInfo = $this->getDatabaseSchemaInfo();

        $prompt = "You are an expert SQL query generator for a business database. Generate a safe SELECT query based on the user's natural language request.

DATABASE SCHEMA:
{$schemaInfo}

USER QUERY: \"{$query}\"

CONTEXT INFORMATION:
- This is a business directory database with places, product prices, and scrape logs
- Places table contains business information (name, category, address, rating, etc.)
- Product_prices table contains price data for various products
- Scrape_logs table contains scraping activity records

INSTRUCTIONS:
1. Generate ONLY SELECT queries (no INSERT, UPDATE, DELETE, DROP, etc.)
2. Use proper table aliases when joining
3. Include appropriate WHERE, GROUP BY, ORDER BY, LIMIT clauses as needed
4. Format numbers and dates appropriately
5. Return ONLY the SQL query, no explanations or markdown
6. Make sure the query is safe and follows SQL best practices

Generate the SQL query:";

        return $prompt;
    }

    private function fallbackSqlGeneration($query)
    {
        $query = strtolower($query);

        // Simple rule-based SQL generation for common queries
        if (str_contains($query, 'berapa jumlah place') || str_contains($query, 'berapa tempat')) {
            if (str_contains($query, 'daerah') || str_contains($query, 'lokasi')) {
                $location = $this->extractLocationFromQuery($query);
                return "SELECT COUNT(*) as total FROM places WHERE LOWER(address) LIKE '%{$location}%' OR LOWER(name) LIKE '%{$location}%'";
            }
            return "SELECT COUNT(*) as total FROM places";
        }

        if (str_contains($query, 'harga rata') || str_contains($query, 'average price')) {
            return "SELECT AVG(price) as average_price FROM product_prices";
        }

        if (str_contains($query, 'top kategori') || str_contains($query, 'kategori terbanyak')) {
            return "SELECT category, COUNT(*) as count FROM places WHERE category IS NOT NULL GROUP BY category ORDER BY count DESC LIMIT 5";
        }

        // Default fallback
        return "SELECT COUNT(*) as total_places FROM places";
    }

    private function extractLocationFromQuery($query)
    {
        // Simple location extraction
        $locations = ['semboro', 'jember', 'tanggul', 'surabaya', 'malang'];

        foreach ($locations as $location) {
            if (str_contains(strtolower($query), $location)) {
                return $location;
            }
        }

        return '';
    }

    private function getDatabaseSchemaInfo()
    {
        return "
TABLE: places
- id (int, primary key)
- name (varchar, business name)
- category (varchar, business category)
- address (text, location)
- rating (decimal, customer rating)
- review_count (int, number of reviews)
- phone (varchar)
- website (varchar)
- latitude (decimal)
- longitude (decimal)
- created_at (timestamp)
- updated_at (timestamp)

TABLE: product_prices
- id (int, primary key)
- product_name (varchar)
- price (decimal)
- place_id (int, foreign key to places)
- recorded_at (timestamp)
- source (varchar)
- created_at (timestamp)
- updated_at (timestamp)

TABLE: scrape_logs
- id (int, primary key)
- place_id (int, foreign key to places)
- status (varchar, success/error)
- message (text)
- scraped_at (timestamp)
- created_at (timestamp)
- updated_at (timestamp)

TABLE: api_tokens
- id (int, primary key)
- name (varchar)
- token (varchar, hashed)
- abilities (text)
- created_at (timestamp)
- updated_at (timestamp)
";
    }

    public function formatQueryResults($results, $originalQuery = '')
    {
        if (empty($results)) {
            return "Tidak ada data yang ditemukan untuk query tersebut.";
        }

        // Convert results to readable format
        $formatted = [];

        foreach ($results as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }

            // Format numbers and special values
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    if (str_contains(strtolower($key), 'price') || str_contains(strtolower($key), 'harga')) {
                        $row[$key] = 'Rp ' . number_format($value, 0, ',', '.');
                    } elseif (str_contains(strtolower($key), 'rating') || str_contains(strtolower($key), 'rate')) {
                        $row[$key] = number_format($value, 1) . ' ⭐';
                    } else {
                        $row[$key] = number_format($value);
                    }
                }
            }

            $formatted[] = $row;
        }

        return $formatted;
    }
}
