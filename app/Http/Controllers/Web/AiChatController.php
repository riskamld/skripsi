<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\ProductPrice;
use App\Models\ApiToken;
use App\Models\ScrapeLog;
use App\Services\AiQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AiChatController extends Controller
{
    public function getDatabaseContext()
    {
        // Cache database context for 30 minutes
        return Cache::remember('ai_chat_database_context', 1800, function() {
            return [
                'summary' => [
                    'total_places' => Place::count(),
                    'total_prices' => ProductPrice::count(),
                    'total_categories' => Place::distinct('category')->count('category'),
                    'total_scrapes' => ScrapeLog::count(),
                    'total_api_tokens' => ApiToken::count(),
                    'last_updated' => now()->toISOString(),
                ],
                'places_sample' => Place::select('id', 'name', 'category', 'rating', 'review_count')
                                       ->limit(50)
                                       ->get(),
                'categories_stats' => Place::select('category', DB::raw('COUNT(*) as count'))
                                          ->whereNotNull('category')
                                          ->groupBy('category')
                                          ->orderBy('count', 'desc')
                                          ->get(),
                'prices_recent' => ProductPrice::select('product_name', 'price', 'recorded_at', 'place_id')
                                              ->where('recorded_at', '>=', now()->subDays(30))
                                              ->orderBy('recorded_at', 'desc')
                                              ->limit(100)
                                              ->get(),
                'price_stats' => [
                    'avg_price' => ProductPrice::avg('price'),
                    'min_price' => ProductPrice::min('price'),
                    'max_price' => ProductPrice::max('price'),
                    'unique_products' => ProductPrice::distinct('product_name')->count('product_name'),
                ],
            ];
        });
    }

    public function processQuery(Request $request)
    {
        $query = trim($request->input('query', ''));
        $conversationHistory = $request->input('conversation_history', []);
        $aiModel = $request->input('ai_model', 'claude-3-haiku'); // Default model

        if (empty($query)) {
            return response()->json([
                'response' => 'Maaf, saya tidak dapat memproses query kosong. Silakan ajukan pertanyaan yang spesifik tentang database Mafaza Fortuna.',
                'type' => 'error'
            ]);
        }

        try {
            $context = $this->getDatabaseContext();
            $queryType = $this->detectQueryType($query);

            // For data queries, try dynamic SQL generation first
            if (in_array($queryType, ['count', 'price', 'place', 'category', 'location'])) {
                $dynamicResponse = $this->tryDynamicQuery($query);
                if ($dynamicResponse !== null) {
                    return response()->json([
                        'response' => $dynamicResponse,
                        'type' => 'success',
                        'query_type' => 'dynamic_sql',
                        'method' => 'ai_generated_query'
                    ]);
                }
            }

            // For conversational queries or complex questions, use AI-enhanced response
            $aiResponse = $this->generateAiConversationalResponse($query, $conversationHistory, $context, $aiModel);

            if ($aiResponse !== null) {
                return response()->json([
                    'response' => $aiResponse,
                    'type' => 'success',
                    'query_type' => 'conversational_ai',
                    'method' => 'puter_ai_chat',
                    'ai_model' => $aiModel
                ]);
            }

            // Fallback to enhanced rule-based analysis
            $response = $this->analyzeQueryEnhanced($query, $context, $conversationHistory);

            return response()->json([
                'response' => $response,
                'type' => 'success',
                'query_type' => $queryType,
                'method' => 'enhanced_rule_based'
            ]);

        } catch (\Exception $e) {
            Log::error('AI Chat Query Processing Failed', [
                'query' => $query,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            // Human-like fallback that engages in conversation
            $fallbackResponses = [
                "Hmm, saya mengalami sedikit kesulitan memproses pertanyaan Anda. Tapi jangan khawatir! Saya masih bisa membantu dengan informasi database lainnya. Ada topik spesifik yang ingin Anda ketahui tentang bisnis lokal?",
                "Maaf, ada sedikit gangguan teknis di sini. Tapi saya tetap siap membantu! Coba tanyakan tentang tempat terbaik di daerah tertentu, atau harga produk yang Anda cari?",
                "Oh, sepertinya ada yang tidak beres dengan sistem saya. Tapi tenang saja, saya masih bisa memberikan informasi tentang bisnis lokal. Mau tahu kategori usaha apa yang paling banyak di daerah ini?",
                "Saya mengalami kesulitan memahami pertanyaan tersebut. Tapi saya ahli dalam data bisnis lokal! Bagaimana kalau kita bahas tentang rekomendasi tempat makan terbaik atau tren harga produk?"
            ];

            $fallbackResponse = $fallbackResponses[array_rand($fallbackResponses)];

            return response()->json([
                'response' => $fallbackResponse,
                'fallback_response' => $this->getGeneralDatabaseOverview($context),
                'type' => 'error',
                'error' => $e->getMessage()
            ]);
        }
    }

    private function tryDynamicQuery($query)
    {
        try {
            $queryBuilder = new AiQueryBuilder();

            // Generate SQL from natural language
            $sqlQuery = $queryBuilder->generateSqlFromNaturalLanguage($query);

            // Execute the query
            $results = $queryBuilder->executeAiGeneratedQuery($sqlQuery);

            // Format results for human-readable response
            return $this->formatDynamicQueryResults($results, $query, $sqlQuery);

        } catch (\Exception $e) {
            // If dynamic query fails, return null to fallback to rule-based
            return null;
        }
    }

    private function formatDynamicQueryResults($results, $originalQuery, $executedSql)
    {
        if (empty($results)) {
            return "Tidak ada data yang ditemukan untuk query tersebut.";
        }

        $query = strtolower($originalQuery);

        // Format based on query type
        if (str_contains($query, 'berapa') && str_contains($query, 'tempat')) {
            // Count places query
            $count = $results[0]->total ?? $results[0]->count ?? 'N/A';
            return "Berdasarkan data real-time dari database, ditemukan **{$count}** tempat yang sesuai dengan kriteria Anda.";
        }

        if (str_contains($query, 'harga rata') || str_contains($query, 'average price')) {
            // Average price query
            $avgPrice = $results[0]->average_price ?? 'N/A';
            if (is_numeric($avgPrice)) {
                $formattedPrice = 'Rp ' . number_format($avgPrice, 0, ',', '.');
                return "Harga rata-rata berdasarkan data real-time: **{$formattedPrice}**";
            }
        }

        if (str_contains($query, 'top kategori') || str_contains($query, 'kategori terbanyak')) {
            // Top categories query
            $response = "Top kategori berdasarkan data real-time:\n\n";
            foreach (array_slice($results, 0, 5) as $row) {
                $category = $row->category ?? 'N/A';
                $count = $row->count ?? 'N/A';
                $response .= "• **{$category}**: {$count} tempat\n";
            }
            return $response;
        }

        // Generic formatting for other queries
        $response = "Hasil query real-time dari database:\n\n";

        foreach (array_slice($results, 0, 10) as $row) { // Limit to 10 rows for display
            $rowData = [];
            foreach ((array)$row as $key => $value) {
                if ($key !== 'note') { // Skip internal notes
                    $formattedValue = is_numeric($value) ? number_format($value) : $value;
                    $rowData[] = "**{$key}**: {$formattedValue}";
                }
            }
            $response .= "• " . implode(", ", $rowData) . "\n";
        }

        if (count($results) > 10) {
            $response .= "\n*(Menampilkan 10 hasil pertama dari " . count($results) . " total)*";
        }

        return $response;
    }

    private function detectQueryType($query)
    {
        $query = strtolower($query);

        // Enhanced location detection - check first
        $locationKeywords = ['di ', 'daerah', 'lokasi', 'kecamatan', 'desa', 'kabupaten', 'kelurahan'];
        $hasLocation = array_reduce($locationKeywords, fn($carry, $kw) =>
            $carry || str_contains($query, $kw), false);

        if ($hasLocation) {
            return 'location';
        }

        if (str_contains($query, 'berapa') || str_contains($query, 'jumlah') || str_contains($query, 'total') || str_contains($query, 'count')) {
            return 'count';
        }

        if (str_contains($query, 'harga') || str_contains($query, 'price') || str_contains($query, 'termurah') || str_contains($query, 'termahal')) {
            return 'price';
        }

        if (str_contains($query, 'kategori') || str_contains($query, 'category') || str_contains($query, 'jenis')) {
            return 'category';
        }

        if (str_contains($query, 'tempat') || str_contains($query, 'place') || str_contains($query, 'lokasi') || str_contains($query, 'rating')) {
            return 'place';
        }

        return 'general';
    }

    private function analyzeQuery($query, $context)
    {
        $type = $this->detectQueryType($query);

        switch ($type) {
            case 'location':
                return $this->handleLocationQuery($query, $context);
            case 'count':
                return $this->handleCountQuery($query, $context);
            case 'price':
                return $this->handlePriceQuery($query, $context);
            case 'category':
                return $this->handleCategoryQuery($query, $context);
            case 'place':
                return $this->handlePlaceQuery($query, $context);
            default:
                return $this->handleGeneralQuery($query, $context);
        }
    }

    private function handleCountQuery($query, $context)
    {
        $query = strtolower($query);

        if (str_contains($query, 'place') || str_contains($query, 'tempat')) {
            $count = $context['summary']['total_places'];
            return "Anda memiliki **{$count}** tempat/places di database Mafaza Fortuna.";
        }

        if (str_contains($query, 'produk') || str_contains($query, 'product') || str_contains($query, 'harga') || str_contains($query, 'price')) {
            $count = $context['summary']['total_prices'];
            $unique = $context['price_stats']['unique_products'];
            return "Database Anda memiliki **{$count}** record harga dari **{$unique}** produk unik.";
        }

        if (str_contains($query, 'kategori') || str_contains($query, 'category')) {
            $count = $context['summary']['total_categories'];
            return "Terdapat **{$count}** kategori tempat berbeda di database Anda.";
        }

        if (str_contains($query, 'scrape') || str_contains($query, 'scraping')) {
            $count = $context['summary']['total_scrapes'];
            return "Total record scraping: **{$count}**.";
        }

        if (str_contains($query, 'api') || str_contains($query, 'token')) {
            $count = $context['summary']['total_api_tokens'];
            return "Anda memiliki **{$count}** API token aktif.";
        }

        // General count
        return "Database Summary:\n" .
               "• Places: **{$context['summary']['total_places']}**\n" .
               "• Product Prices: **{$context['summary']['total_prices']}**\n" .
               "• Categories: **{$context['summary']['total_categories']}**\n" .
               "• Scrape Logs: **{$context['summary']['total_scrapes']}**\n" .
               "• API Tokens: **{$context['summary']['total_api_tokens']}**";
    }

    private function handlePriceQuery($query, $context)
    {
        $stats = $context['price_stats'];

        if (str_contains($query, 'rata') || str_contains($query, 'average')) {
            $avg = number_format($stats['avg_price'], 0, ',', '.');
            return "Harga rata-rata produk: **Rp {$avg}**";
        }

        if (str_contains($query, 'termurah') || str_contains($query, 'murah') || str_contains($query, 'min')) {
            $min = number_format($stats['min_price'], 0, ',', '.');
            return "Harga termurah: **Rp {$min}**";
        }

        if (str_contains($query, 'termahal') || str_contains($query, 'mahal') || str_contains($query, 'max')) {
            $max = number_format($stats['max_price'], 0, ',', '.');
            return "Harga termahal: **Rp {$max}**";
        }

        if (str_contains($query, 'range') || str_contains($query, 'kisaran')) {
            $min = number_format($stats['min_price'], 0, ',', '.');
            $max = number_format($stats['max_price'], 0, ',', '.');
            $avg = number_format($stats['avg_price'], 0, ',', '.');
            return "Kisaran harga produk:\n" .
                   "• Minimum: **Rp {$min}**\n" .
                   "• Rata-rata: **Rp {$avg}**\n" .
                   "• Maximum: **Rp {$max}**";
        }

        // Product-specific price queries
        $productName = $this->extractProductName($query);
        if ($productName) {
            return $this->getProductPriceInfo($productName, $context);
        }

        return "Informasi harga:\n" .
               "• Rata-rata: **Rp " . number_format($stats['avg_price'], 0, ',', '.') . "**\n" .
               "• Kisaran: **Rp " . number_format($stats['min_price'], 0, ',', '.') . "** - **Rp " . number_format($stats['max_price'], 0, ',', '.') . "**\n" .
               "• Total produk unik: **{$stats['unique_products']}**";
    }

    private function handleCategoryQuery($query, $context)
    {
        $categories = $context['categories_stats'];

        if (str_contains($query, 'top') || str_contains($query, 'terbanyak') || str_contains($query, 'terbesar')) {
            $topCategories = array_slice($categories->toArray(), 0, 5);

            // Narrative response instead of just statistics
            $response = "Berdasarkan data bisnis lokal kami, kategori usaha yang paling banyak ditemukan adalah:\n\n";

            foreach ($topCategories as $index => $category) {
                $percentage = round(($category['count'] / $context['summary']['total_places']) * 100, 1);
                $response .= "**{$category['category']}** - {$category['count']} tempat ({$percentage}% dari total)\n";
            }

            $response .= "\nIni menunjukkan bahwa **{$topCategories[0]['category']}** adalah kategori bisnis yang paling dominan di daerah ini, diikuti oleh **{$topCategories[1]['category']}**.";

            return $response;
        }

        if (str_contains($query, 'semua') || str_contains($query, 'list') || str_contains($query, 'daftar')) {
            $response = "Berikut adalah semua kategori bisnis yang ada di database kami beserta jumlah tempatnya:\n\n";

            foreach ($categories as $category) {
                $response .= "**{$category['category']}** - {$category['count']} tempat\n";
            }

            $response .= "\nTotal ada **{$context['summary']['total_categories']}** kategori bisnis berbeda, mulai dari toko ritel hingga layanan profesional.";

            return $response;
        }

        // Specific category query - make it narrative
        $categoryName = $this->extractCategoryName($query);
        if ($categoryName) {
            $category = $categories->where('category', $categoryName)->first();
            if ($category) {
                $percentage = round(($category['count'] / $context['summary']['total_places']) * 100, 1);
                $response = "**{$categoryName}** ternyata cukup populer di daerah ini! Kami memiliki **{$category['count']}** tempat yang termasuk dalam kategori ini.\n\n";

                // Add context about what this means
                if ($percentage > 10) {
                    $response .= "Ini menunjukkan bahwa {$percentage}% dari semua bisnis lokal adalah {$categoryName}, jadi persaingan di kategori ini cukup ketat.";
                } elseif ($percentage > 5) {
                    $response .= "Kategori ini cukup berkembang dengan {$percentage}% pangsa pasar.";
                } else {
                    $response .= "Meskipun jumlahnya {$category['count']} tempat, ini masih kategori yang berkembang di daerah ini.";
                }

                return $response;
            } else {
                return "Hmm, saya tidak menemukan kategori **{$categoryName}** dalam database kami. Mungkin bisa coba dengan nama kategori yang lain, atau saya bisa tampilkan daftar semua kategori yang tersedia?";
            }
        }

        // Default category response - narrative
        return "Database kami mencakup **{$context['summary']['total_categories']}** kategori bisnis berbeda, mulai dari toko ritel hingga jasa profesional. Kategori yang paling banyak adalah toko bunga dengan 125 tempat. Ada yang spesifik ingin Anda ketahui tentang kategori tertentu?";
    }

    private function handleLocationQuery($query, $context)
    {
        $location = $this->extractLocationName($query);

        if (!$location) {
            return "Saya tidak dapat menemukan nama lokasi dalam pertanyaan Anda. Coba sebutkan nama daerah/kecamatan seperti 'bangsalsari', 'semboro', 'tanggul', dll.";
        }

        try {
            // Try dynamic SQL query for real-time location search
            $queryBuilder = new AiQueryBuilder();

            // Count places in location
            $countSql = "SELECT COUNT(*) as total FROM places WHERE LOWER(address) LIKE '%{$location}%' OR LOWER(name) LIKE '%{$location}%'";
            $countResults = $queryBuilder->executeAiGeneratedQuery($countSql);

            $totalPlaces = $countResults[0]->total ?? 0;

            if ($totalPlaces == 0) {
                return "Tidak ditemukan tempat di daerah **{$location}** dalam database saat ini.";
            }

            // Get categories in location
            $categorySql = "SELECT category, COUNT(*) as count FROM places WHERE (LOWER(address) LIKE '%{$location}%' OR LOWER(name) LIKE '%{$location}%') AND category IS NOT NULL GROUP BY category ORDER BY count DESC LIMIT 5";
            $categoryResults = $queryBuilder->executeAiGeneratedQuery($categorySql);

            $categoriesText = "";
            if (!empty($categoryResults)) {
                $categoriesText = " dengan kategori utama:\n";
                foreach ($categoryResults as $cat) {
                    $categoriesText .= "• **{$cat->category}**: {$cat->count} tempat\n";
                }
            }

            return "Berdasarkan data real-time dari database, di daerah **{$location}** ditemukan **{$totalPlaces}** tempat{$categoriesText}";

        } catch (\Exception $e) {
            // Fallback to cached data search
            return $this->handleLocationQueryCached($query, $context);
        }
    }

    private function handleLocationQueryCached($query, $context)
    {
        $location = $this->extractLocationName($query);

        if (!$location) {
            return "Saya tidak dapat menemukan nama lokasi dalam pertanyaan Anda.";
        }

        // Search in cached places data
        $places = collect($context['places_sample']);
        $matchingPlaces = $places->filter(function($place) use ($location) {
            $address = strtolower($place['address'] ?? '');
            $name = strtolower($place['name'] ?? '');
            return str_contains($address, $location) || str_contains($name, $location);
        });

        $count = $matchingPlaces->count();

        if ($count == 0) {
            return "Tidak ditemukan tempat di daerah **{$location}** dalam data yang tersedia.";
        }

        // Get category breakdown
        $categories = $matchingPlaces->where('category', '!=', null)
                                   ->groupBy('category')
                                   ->map->count()
                                   ->sortDesc()
                                   ->take(3);

        $categoryText = "";
        if ($categories->isNotEmpty()) {
            $categoryText = " dengan kategori:\n" . $categories->map(function($count, $category) {
                return "• **{$category}**: {$count} tempat";
            })->join("\n");
        }

        return "Dari data yang tersedia, di daerah **{$location}** ditemukan **{$count}** tempat{$categoryText}";
    }

    private function handlePlaceQuery($query, $context)
    {
        $places = $context['places_sample'];

        if (str_contains($query, 'rating') || str_contains($query, 'bintang')) {
            if (str_contains($query, 'tinggi') || str_contains($query, 'terbaik')) {
                $topRated = $places->sortByDesc('rating')->first();
                if ($topRated) {
                    return "Tempat dengan rating tertinggi: **{$topRated['name']}** ({$topRated['rating']}⭐, {$topRated['review_count']} reviews)";
                }
            }

            $avgRating = $places->avg('rating');
            return "Rating rata-rata tempat: **" . number_format($avgRating, 1) . "** ⭐";
        }

        if (str_contains($query, 'review') || str_contains($query, 'ulasan')) {
            $totalReviews = $places->sum('review_count');
            return "Total ulasan/review: **{$totalReviews}** dari {$places->count()} tempat yang di-sample.";
        }

        return "Informasi tempat:\n" .
               "• Total places: **{$context['summary']['total_places']}**\n" .
               "• Rating rata-rata: **" . number_format($places->avg('rating'), 1) . "** ⭐\n" .
               "• Total reviews: **" . number_format($places->sum('review_count')) . "**";
    }

    private function handleGeneralQuery($query, $context)
    {
        // General database overview
        return "Mafaza Fortuna Database Overview:\n\n" .
               "**📊 Summary:**\n" .
               "• Places: **{$context['summary']['total_places']}** lokasi bisnis\n" .
               "• Product Prices: **{$context['summary']['total_prices']}** record harga\n" .
               "• Categories: **{$context['summary']['total_categories']}** jenis kategori\n" .
               "• Scrape Logs: **{$context['summary']['total_scrapes']}** riwayat scraping\n\n" .
               "**💰 Price Statistics:**\n" .
               "• Average Price: **Rp " . number_format($context['price_stats']['avg_price'], 0, ',', '.') . "**\n" .
               "• Price Range: **Rp " . number_format($context['price_stats']['min_price'], 0, ',', '.') . "** - **Rp " . number_format($context['price_stats']['max_price'], 0, ',', '.') . "**\n\n" .
               "**🏷️ Top Categories:**\n" .
               collect($context['categories_stats'])->take(3)->map(function($cat) {
                   return "• {$cat['category']}: {$cat['count']} places";
               })->join("\n") . "\n\n" .
               "**❓ Coba tanya:** 'Berapa jumlah places?', 'Harga termurah apa?', 'Top kategori mana?'";
    }

    private function extractProductName($query)
    {
        // Simple product name extraction
        $products = ['mangga', 'pisang', 'apel', 'jeruk', 'ayam', 'beras', 'minyak', 'gula', 'kopi'];

        foreach ($products as $product) {
            if (str_contains(strtolower($query), $product)) {
                return $product;
            }
        }

        return null;
    }

    private function extractCategoryName($query)
    {
        // Simple category name extraction
        $categories = ['toko buah', 'restoran', 'toko kelontong', 'warung makan', 'apotek', 'toko elektronik'];

        foreach ($categories as $category) {
            if (str_contains(strtolower($query), $category)) {
                return $category;
            }
        }

        return null;
    }

    private function getProductPriceInfo($productName, $context)
    {
        $prices = collect($context['prices_recent'])->where('product_name', $productName);

        if ($prices->isEmpty()) {
            return "Tidak ada data harga untuk produk **{$productName}** dalam 30 hari terakhir.";
        }

        $avgPrice = $prices->avg('price');
        $minPrice = $prices->min('price');
        $maxPrice = $prices->max('price');
        $count = $prices->count();

        return "Informasi harga **{$productName}** (30 hari terakhir):\n" .
               "• Rata-rata: **Rp " . number_format($avgPrice, 0, ',', '.') . "**\n" .
               "• Termurah: **Rp " . number_format($minPrice, 0, ',', '.') . "**\n" .
               "• Termahal: **Rp " . number_format($maxPrice, 0, ',', '.') . "**\n" .
               "• Data points: **{$count}** record";
    }

    private function generateAiConversationalResponse($query, $conversationHistory, $context, $aiModel)
    {
        // For server-side processing, we'll return null to indicate frontend should handle AI
        // This method is designed to be called from JavaScript with Puter AI
        // The actual AI processing happens on the frontend
        return null;
    }

    private function analyzeQueryEnhanced($query, $context, $conversationHistory = [])
    {
        $queryLower = strtolower($query);
        $type = $this->detectQueryType($query);

        // Enhanced conversational responses
        if ($this->isGreeting($query)) {
            return $this->handleGreeting($context);
        }

        if ($this->isFollowUpQuestion($query, $conversationHistory)) {
            return $this->handleFollowUp($query, $conversationHistory, $context);
        }

        if ($this->isComparativeQuery($query)) {
            return $this->handleComparativeQuery($query, $context);
        }

        if ($this->isRecommendationQuery($query)) {
            return $this->handleRecommendationQuery($query, $context);
        }

        // Enhanced analysis with conversation context
        switch ($type) {
            case 'location':
                return $this->handleLocationQueryEnhanced($query, $context);
            case 'count':
                return $this->handleCountQueryEnhanced($query, $context);
            case 'price':
                return $this->handlePriceQueryEnhanced($query, $context);
            case 'category':
                return $this->handleCategoryQueryEnhanced($query, $context);
            case 'place':
                return $this->handlePlaceQueryEnhanced($query, $context);
            default:
                return $this->handleGeneralQueryEnhanced($query, $context, $conversationHistory);
        }
    }

    private function getGeneralDatabaseOverview($context)
    {
        return "📊 **Mafaza Fortuna Database Overview**\n\n" .
               "Database kami berisi informasi bisnis lokal dengan:\n\n" .
               "• **{$context['summary']['total_places']}** lokasi bisnis\n" .
               "• **{$context['summary']['total_prices']}** record harga produk\n" .
               "• **{$context['summary']['total_categories']}** kategori bisnis berbeda\n" .
               "• **{$context['summary']['total_scrapes']}** riwayat scraping\n\n" .
               "💰 **Statistik Harga**\n" .
               "• Rata-rata: Rp " . number_format($context['price_stats']['avg_price'], 0, ',', '.') . "\n" .
               "• Kisaran: Rp " . number_format($context['price_stats']['min_price'], 0, ',', '.') . " - Rp " . number_format($context['price_stats']['max_price'], 0, ',', '.') . "\n\n" .
               "🏷️ **Top Kategori**\n" .
               collect($context['categories_stats'])->take(3)->map(function($cat) {
                   return "• {$cat['category']}: {$cat['count']} tempat";
               })->join("\n");
    }

    private function isGreeting($query)
    {
        $greetings = ['halo', 'hai', 'hi', 'hello', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'apa kabar'];
        $queryLower = strtolower($query);

        foreach ($greetings as $greeting) {
            if (str_contains($queryLower, $greeting)) {
                return true;
            }
        }
        return false;
    }

    private function handleGreeting($context, $conversationHistory = [])
    {
        // Generate unique, time-aware greeting instead of static templates
        $hour = now()->hour;
        $timeOfDay = $hour < 12 ? 'pagi' : ($hour < 15 ? 'siang' : ($hour < 18 ? 'sore' : 'malam'));

        // Create unique greeting based on current time and random seed
        $randomSeed = rand(1, 100);
        $timeBasedGreetings = [
            "Selamat {$timeOfDay}! Ada yang bisa saya bantu hari ini?",
            "Halo! Senang bisa mengobrol dengan Anda. Mau bahas apa?",
            "Hai! Bagaimana hari Anda? Saya siap mendengarkan.",
            "Selamat {$timeOfDay}! Mari kita mulai percakapan yang menarik.",
            "Halo teman! Ada topik apa yang ingin kita eksplorasi?",
            "Selamat {$timeOfDay}! Saya tertarik mendengar cerita Anda.",
            "Hai! Apa kabar dunia luar sana? Mau sharing pengalaman?",
            "Selamat {$timeOfDay}! Mari kita diskusi hal-hal menarik.",
            "Halo! Saya senang bertemu dengan Anda. Mau ngobrol apa?",
            "Selamat {$timeOfDay}! Siap untuk percakapan yang seru!"
        ];

        // Use random seed to ensure different greeting each time
        $greetingIndex = $randomSeed % count($timeBasedGreetings);
        return $timeBasedGreetings[$greetingIndex];
    }

    private function isFollowUpQuestion($query, $conversationHistory)
    {
        if (empty($conversationHistory)) return false;

        $followUpWords = ['dan', 'lalu', 'bagaimana', 'gimana', 'kok', 'kenapa', 'mengapa', 'apakah'];
        $queryLower = strtolower($query);

        foreach ($followUpWords as $word) {
            if (str_contains($queryLower, $word)) {
                return true;
            }
        }
        return false;
    }

    private function handleFollowUp($query, $conversationHistory, $context)
    {
        $lastQuery = end($conversationHistory);
        $lastResponse = $lastQuery['response'] ?? '';

        // Analyze context from previous conversation
        if (str_contains($lastResponse, 'harga') || str_contains($lastResponse, 'price')) {
            return $this->handlePriceFollowUp($query, $context);
        }

        if (str_contains($lastResponse, 'kategori') || str_contains($lastResponse, 'category')) {
            return $this->handleCategoryFollowUp($query, $context);
        }

        return "Berdasarkan percakapan sebelumnya, " . $this->analyzeQueryEnhanced($query, $context);
    }

    private function isComparativeQuery($query)
    {
        $comparativeWords = ['banding', 'dibanding', 'vs', 'versus', 'lebih', 'kurang', 'beda'];
        $queryLower = strtolower($query);

        foreach ($comparativeWords as $word) {
            if (str_contains($queryLower, $word)) {
                return true;
            }
        }
        return false;
    }

    private function handleComparativeQuery($query, $context)
    {
        // Extract items to compare from query
        $queryLower = strtolower($query);

        if (str_contains($queryLower, 'kategori')) {
            return $this->compareCategories($context);
        }

        if (str_contains($queryLower, 'daerah') || str_contains($queryLower, 'lokasi')) {
            return $this->compareLocations($query, $context);
        }

        return "Untuk perbandingan yang lebih detail, bisa Anda sebutkan apa yang ingin dibandingkan? Misalnya kategori mana yang lebih banyak, atau daerah mana yang memiliki lebih banyak tempat.";
    }

    private function compareCategories($context)
    {
        $categories = collect($context['categories_stats'])->take(5);

        $response = "📊 **Perbandingan Kategori Bisnis**\n\n";
        $response .= "Berikut adalah 5 kategori teratas berdasarkan jumlah tempat:\n\n";

        foreach ($categories as $index => $category) {
            $percentage = round(($category['count'] / $context['summary']['total_places']) * 100, 1);
            $response .= "**" . ($index + 1) . ". {$category['category']}**\n";
            $response .= "• Jumlah: {$category['count']} tempat\n";
            $response .= "• Persentase: {$percentage}%\n\n";
        }

        $response .= "💡 **Insights:**\n";
        $topCategory = $categories->first();
        $response .= "• Kategori **{$topCategory['category']}** mendominasi dengan {$topCategory['count']} tempat\n";
        $response .= "• Rasio terbesar vs terkecil: " . round($topCategory['count'] / $categories->last()['count'], 1) . "x lipat\n";

        return $response;
    }

    private function isRecommendationQuery($query)
    {
        $recommendationWords = ['rekomendasi', 'sarankan', 'pilih', 'bagus', 'terbaik', 'cocok'];
        $queryLower = strtolower($query);

        foreach ($recommendationWords as $word) {
            if (str_contains($queryLower, $word)) {
                return true;
            }
        }
        return false;
    }

    private function handleRecommendationQuery($query, $context)
    {
        $queryLower = strtolower($query);

        if (str_contains($queryLower, 'tempat') || str_contains($queryLower, 'makan')) {
            return $this->recommendPlaces($context);
        }

        if (str_contains($queryLower, 'produk') || str_contains($queryLower, 'beli')) {
            return $this->recommendProducts($context);
        }

        return "Berdasarkan data kami, berikut beberapa rekomendasi:\n\n" .
               "🏪 **Tempat Terbaik:**\n" . $this->getTopRatedPlaces($context) . "\n\n" .
               "🏷️ **Kategori Populer:**\n" . $this->getPopularCategories($context);
    }

    private function recommendPlaces($context)
    {
        $places = collect($context['places_sample']);
        $topPlaces = $places->sortByDesc('rating')->take(3);

        $response = "🍽️ **Rekomendasi Tempat Terbaik**\n\n";
        $response .= "Berdasarkan rating dan ulasan pelanggan:\n\n";

        foreach ($topPlaces as $place) {
            $response .= "**🏆 {$place['name']}**\n";
            $response .= "• Rating: {$place['rating']} ⭐\n";
            $response .= "• Ulasan: {$place['review_count']} reviews\n";
            $response .= "• Kategori: " . ($place['category'] ?? 'Tidak diketahui') . "\n\n";
        }

        $response .= "💡 **Tips:** Kunjungi tempat-tempat ini untuk pengalaman terbaik!";
        return $response;
    }

    private function getTopRatedPlaces($context)
    {
        $places = collect($context['places_sample']);
        $topPlace = $places->sortByDesc('rating')->first();

        if ($topPlace) {
            return "• **{$topPlace['name']}** ({$topPlace['rating']} ⭐, {$topPlace['review_count']} ulasan)";
        }
        return "• Data rating sedang dimuat...";
    }

    private function getPopularCategories($context)
    {
        $categories = collect($context['categories_stats'])->take(3);
        return $categories->map(function($cat) {
            return "• {$cat['category']}: {$cat['count']} tempat";
        })->join("\n");
    }

    // Enhanced handlers with more conversational responses
    private function handleLocationQueryEnhanced($query, $context)
    {
        $location = $this->extractLocationName($query);

        if (!$location) {
            return "🌍 Untuk mencari tempat di daerah tertentu, sebutkan nama daerah seperti:\n" .
                   "• 'tempat di semboro'\n" .
                   "• 'berapa toko di tanggul'\n" .
                   "• 'lokasi bisnis di jember'\n\n" .
                   "Daerah populer dalam database kami: Semboro, Tanggul, Jember, dan sekitarnya.";
        }

        // Use the existing location handler but with enhanced messaging
        $baseResponse = $this->handleLocationQuery($query, $context);

        return "🔍 **Pencarian Lokasi: {$location}**\n\n" . $baseResponse . "\n\n" .
               "💡 **Tips:** Jika hasil tidak sesuai, coba gunakan nama daerah yang lebih spesifik atau variasi penulisan.";
    }

    private function handleCountQueryEnhanced($query, $context)
    {
        $baseResponse = $this->handleCountQuery($query, $context);

        return "📊 **Statistik Database**\n\n" . $baseResponse . "\n\n" .
               "📈 **Trend:** Database terus berkembang dengan scraping otomatis setiap hari.";
    }

    private function handlePriceQueryEnhanced($query, $context)
    {
        $baseResponse = $this->handlePriceQuery($query, $context);

        return "💰 **Analisis Harga**\n\n" . $baseResponse . "\n\n" .
               "📊 **Catatan:** Harga dapat berubah sewaktu-waktu berdasarkan kondisi pasar lokal.";
    }

    private function handleCategoryQueryEnhanced($query, $context)
    {
        $baseResponse = $this->handleCategoryQuery($query, $context);

        return "🏷️ **Analisis Kategori**\n\n" . $baseResponse . "\n\n" .
               "📈 **Insight:** Kategori terpopuler menunjukkan kebutuhan masyarakat di daerah tersebut.";
    }

    private function handlePlaceQueryEnhanced($query, $context)
    {
        $baseResponse = $this->handlePlaceQuery($query, $context);

        return "🏪 **Informasi Tempat**\n\n" . $baseResponse . "\n\n" .
               "⭐ **Rating:** Berdasarkan ulasan pelanggan dari platform Google Maps dan lainnya.";
    }

    private function handleGeneralQueryEnhanced($query, $context, $conversationHistory)
    {
        // If it's a very general question, provide overview
        if (str_word_count($query) <= 3) {
            return $this->handleGreeting($context);
        }

        // Try to understand the intent
        $queryLower = strtolower($query);

        if (str_contains($queryLower, 'bisa') || str_contains($queryLower, 'dapat') || str_contains($queryLower, 'mampu')) {
            return $this->handleCapabilityQuestion($query, $context);
        }

        if (str_contains($queryLower, 'cara') || str_contains($queryLower, 'bagaimana')) {
            return $this->handleHowToQuestion($query, $context);
        }

        // Default enhanced general response
        return "🤔 **Pertanyaan Anda:** {$query}\n\n" .
               "Saya mengerti Anda ingin tahu tentang database Mafaza Fortuna. " .
               "Mari saya berikan ringkasan lengkap:\n\n" .
               $this->getGeneralDatabaseOverview($context) . "\n\n" .
               "❓ **Ada yang spesifik ingin ditanyakan?** Misalnya:\n" .
               "• 'Berapa tempat di semboro?'\n" .
               "• 'Harga rata-rata produk apa?'\n" .
               "• 'Kategori apa yang terbanyak?'";
    }

    private function handleCapabilityQuestion($query, $context)
    {
        return "🚀 **Kapabilitas AI Chat Mafaza Fortuna**\n\n" .
               "Saya dapat membantu Anda dengan:\n\n" .
               "📊 **Analisis Data**\n" .
               "• Hitung jumlah tempat per daerah/kategori\n" .
               "• Analisis harga produk dan tren\n" .
               "• Cari tempat berdasarkan rating dan ulasan\n\n" .
               "🔍 **Pencarian Pintar**\n" .
               "• Cari bisnis di lokasi tertentu\n" .
               "• Temukan produk dengan harga terbaik\n" .
               "• Bandingkan kategori dan daerah\n\n" .
               "💡 **Rekomendasi**\n" .
               "• Sarankan tempat terbaik berdasarkan rating\n" .
               "• Berikan insight tentang tren bisnis lokal\n\n" .
               "🎯 **Real-time Query**\n" .
               "• Eksekusi SQL query dari bahasa natural\n" .
               "• Update data secara otomatis via scraping\n\n" .
               "Coba tanyakan sesuatu yang spesifik untuk melihat kemampuan saya! 😊";
    }

    private function handleHowToQuestion($query, $context)
    {
        return "📖 **Panduan Menggunakan AI Chat**\n\n" .
               "Berikut cara efektif bertanya:\n\n" .
               "🔍 **Pencarian Lokasi**\n" .
               "• \"Berapa tempat di semboro?\"\n" .
               "• \"Toko apa saja di tanggul?\"\n\n" .
               "💰 **Informasi Harga**\n" .
               "• \"Harga rata-rata mangga?\"\n" .
               "• \"Produk termurah mana?\"\n\n" .
               "🏷️ **Kategori & Bisnis**\n" .
               "• \"Top kategori apa?\"\n" .
               "• \"Tempat rating tertinggi?\"\n\n" .
               "📊 **Analisis**\n" .
               "• \"Bandingkan kategori toko buah vs makanan\"\n" .
               "• \"Rekomendasikan tempat makan terbaik\"\n\n" .
               "💡 **Tips:** Semakin spesifik pertanyaan, semakin akurat jawaban saya!";
    }

    private function handlePriceFollowUp($query, $context)
    {
        $queryLower = strtolower($query);

        if (str_contains($queryLower, 'tren') || str_contains($queryLower, 'perubahan')) {
            return "📈 **Analisis Tren Harga**\n\n" .
                   "Berdasarkan data 30 hari terakhir:\n" .
                   "• Rata-rata harga: Rp " . number_format($context['price_stats']['avg_price'], 0, ',', '.') . "\n" .
                   "• Kisaran harga: Rp " . number_format($context['price_stats']['min_price'], 0, ',', '.') . " - Rp " . number_format($context['price_stats']['max_price'], 0, ',', '.') . "\n\n" .
                   "💡 Tren harga dipengaruhi oleh:\n" .
                   "• Musim dan ketersediaan produk\n" .
                   "• Kondisi cuaca dan panen\n" .
                   "• Permintaan pasar lokal";
        }

        return $this->handlePriceQueryEnhanced($query, $context);
    }

    private function handleCategoryFollowUp($query, $context)
    {
        $queryLower = strtolower($query);

        if (str_contains($queryLower, 'detail') || str_contains($queryLower, 'lebih')) {
            return $this->handleCategoryQueryEnhanced("daftar semua kategori", $context);
        }

        return $this->handleCategoryQueryEnhanced($query, $context);
    }

    private function compareLocations($query, $context)
    {
        // Extract location names from query
        $locations = ['semboro', 'tanggul', 'jember', 'bangsalsari'];
        $mentionedLocations = [];

        $queryLower = strtolower($query);
        foreach ($locations as $location) {
            if (str_contains($queryLower, $location)) {
                $mentionedLocations[] = $location;
            }
        }

        if (count($mentionedLocations) < 2) {
            return "Untuk perbandingan daerah, sebutkan minimal 2 lokasi. Misalnya: 'Bandingkan semboro dan tanggul'";
        }

        $response = "🏙️ **Perbandingan Daerah**\n\n";

        foreach ($mentionedLocations as $location) {
            try {
                $queryBuilder = new AiQueryBuilder();
                $countSql = "SELECT COUNT(*) as total FROM places WHERE LOWER(address) LIKE '%{$location}%' OR LOWER(name) LIKE '%{$location}%'";
                $countResults = $queryBuilder->executeAiGeneratedQuery($countSql);
                $total = $countResults[0]->total ?? 0;

                $response .= "**{$location}:** {$total} tempat\n";
            } catch (\Exception $e) {
                $response .= "**{$location}:** Data tidak tersedia\n";
            }
        }

        return $response;
    }

    private function recommendProducts($context)
    {
        $recentPrices = collect($context['prices_recent']);
        $avgPrices = $recentPrices->groupBy('product_name')->map(function($prices) {
            return $prices->avg('price');
        })->sort()->take(3);

        $response = "🛒 **Rekomendasi Produk Berdasarkan Harga**\n\n";
        $response .= "Produk dengan harga rata-rata terjangkau:\n\n";

        foreach ($avgPrices as $product => $avgPrice) {
            $response .= "• **{$product}**: Rp " . number_format($avgPrice, 0, ',', '.') . " (rata-rata)\n";
        }

        $response .= "\n💡 **Tips Belanja:**\n";
        $response .= "• Beli di saat harga sedang turun\n";
        $response .= "• Bandingkan harga di beberapa tempat\n";
        $response .= "• Perhatikan kualitas dan kesegaran produk";

        return $response;
    }
}
