<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketAnalysisController extends Controller
{
    public function index()
    {
        // Overall market statistics
        $stats = [
            'total_places' => Place::count(),
            'active_categories' => Place::distinct('category')->count('category'),
            'avg_rating' => Place::avg('rating'),
            'total_reviews' => Place::sum('review_count'),
            'places_with_high_rating' => Place::where('rating', '>=', 4.0)->count(),
        ];

        // Top categories by supply
        $topCategories = Place::select('category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        // Market saturation analysis
        $saturationData = $this->calculateMarketSaturation();

        return view('market-analysis.index', compact('stats', 'topCategories', 'saturationData'));
    }

    public function supplyDemand()
    {
        // Supply vs Demand Analysis
        $categories = Place::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $supplyDemandData = [];

        foreach ($categories as $category) {
            $places = Place::where('category', $category)->get();

            $supply = $places->count();
            $demand = $places->avg('rating') * $places->avg('review_count') * 10; // Simple demand calculation

            $supplyDemandData[] = [
                'category' => $category,
                'supply' => $supply,
                'demand' => round($demand, 2),
                'ratio' => $supply > 0 ? round($demand / $supply, 2) : 0,
                'status' => $this->getSupplyDemandStatus($supply, $demand),
            ];
        }

        // Sort by ratio descending
        usort($supplyDemandData, function($a, $b) {
            return $b['ratio'] <=> $a['ratio'];
        });

        return view('market-analysis.supply-demand', compact('supplyDemandData'));
    }

    public function categoryInsights()
    {
        // Category performance analysis
        $categoryInsights = Place::select(
                'category',
                DB::raw('COUNT(*) as total_places'),
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('SUM(review_count) as total_reviews'),
                DB::raw('MAX(rating) as max_rating'),
                DB::raw('MIN(rating) as min_rating')
            )
            ->whereNotNull('category')
            ->groupBy('category')
            ->having('total_places', '>=', 3) // Only categories with meaningful data
            ->orderBy('avg_rating', 'desc')
            ->get()
            ->map(function($category) {
                return [
                    'category' => $category->category,
                    'total_places' => $category->total_places,
                    'avg_rating' => round($category->avg_rating, 1),
                    'total_reviews' => $category->total_reviews,
                    'max_rating' => $category->max_rating,
                    'min_rating' => $category->min_rating,
                    'rating_range' => round($category->max_rating - $category->min_rating, 1),
                    'performance_score' => $this->calculatePerformanceScore($category),
                    'recommended_products' => $this->getRecommendedProducts($category->category),
                ];
            })
            ->toArray();

        return view('market-analysis.category-insights', compact('categoryInsights'));
    }

    public function geographic()
    {
        // Geographic analysis - simplified for now
        $locationData = Place::select(
                DB::raw('ROUND(lat, 1) as lat_group'),
                DB::raw('ROUND(lng, 1) as lng_group'),
                DB::raw('COUNT(*) as place_count'),
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('SUM(review_count) as total_reviews')
            )
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->groupBy('lat_group', 'lng_group')
            ->orderBy('place_count', 'desc')
            ->limit(20)
            ->get()
            ->map(function($location) {
                return [
                    'coordinates' => $location->lat_group . ', ' . $location->lng_group,
                    'place_count' => $location->place_count,
                    'avg_rating' => round($location->avg_rating, 1),
                    'total_reviews' => $location->total_reviews,
                    'density_score' => $this->calculateDensityScore($location),
                ];
            })
            ->toArray();

        return view('market-analysis.geographic', compact('locationData'));
    }

    private function calculateMarketSaturation()
    {
        $totalPlaces = Place::count();
        $categories = Place::distinct('category')->count('category');
        $avgPlacesPerCategory = $totalPlaces / max($categories, 1);

        if ($avgPlacesPerCategory < 5) {
            return ['level' => 'low', 'message' => 'Market belum saturated, banyak peluang bisnis'];
        } elseif ($avgPlacesPerCategory < 15) {
            return ['level' => 'medium', 'message' => 'Market moderately saturated, perlu diferensiasi'];
        } else {
            return ['level' => 'high', 'message' => 'Market highly saturated, fokus pada niche market'];
        }
    }

    private function getSupplyDemandStatus($supply, $demand)
    {
        if ($supply == 0) return 'unknown';

        $ratio = $demand / $supply;

        if ($ratio > 2.0) {
            return 'high_demand_low_supply'; // Opportunity
        } elseif ($ratio > 1.5) {
            return 'balanced';
        } elseif ($ratio > 0.8) {
            return 'moderate_supply';
        } else {
            return 'oversupply'; // Competition
        }
    }

    private function calculatePerformanceScore($category)
    {
        $ratingScore = ($category->avg_rating / 5.0) * 40; // 40% weight
        $volumeScore = min(($category->total_reviews / 1000) * 30, 30); // 30% weight, max 30
        $consistencyScore = ((5.0 - $category->rating_range) / 5.0) * 30; // 30% weight

        return round($ratingScore + $volumeScore + $consistencyScore, 1);
    }

    private function getRecommendedProducts($category)
    {
        // Rule-based product recommendations based on category
        $recommendations = [
            'Toko Buah' => ['Mangga', 'Pisang', 'Apel', 'Jeruk', 'Alpukat', 'Sayuran Segar'],
            'Restoran' => ['Nasi', 'Ayam', 'Ikan', 'Sayuran', 'Minuman', 'Dessert'],
            'Toko Kelontong' => ['Beras', 'Minyak', 'Gula', 'Kopi', 'Rokok', 'Snack'],
            'Warung Makan' => ['Nasi Goreng', 'Mie', 'Ayam Goreng', 'Tempe', 'Tahu', 'Sambal'],
            'Toko Bunga' => ['Mawar', 'Melati', 'Anggrek', 'Bunga Papan', 'Vas Bunga', 'Aksesoris'],
            'Apotek' => ['Obat Sakit Kepala', 'Vitamin', 'Perban', 'Masker', 'Sabun', 'Shampoo'],
            'Toko Elektronik' => ['Kabel', 'Baterai', 'Lampu', 'Stop Kontak', 'Kipas Angin', 'Radio'],
        ];

        return $recommendations[$category] ?? ['Produk Umum', 'Konsumsi Sehari-hari'];
    }

    private function calculateDensityScore($location)
    {
        $count = $location->place_count;
        $rating = $location->avg_rating;

        // Higher density with good ratings = high competition area
        // Lower density with good ratings = opportunity area
        $densityScore = ($count / 10) * 50 + ($rating / 5.0) * 50;

        return round(min($densityScore, 100), 1);
    }

    // PRICE PREDICTION AI METHODS (100% FREE - Statistical Analysis)

    public function pricePredictions()
    {
        // Get all products with price history
        $productsWithHistory = ProductPrice::select('product_name')
            ->distinct()
            ->where('recorded_at', '>=', now()->subDays(90)) // Last 90 days
            ->get()
            ->pluck('product_name');

        $predictions = [];

        foreach ($productsWithHistory as $productName) {
            $prediction = $this->predictProductPrice($productName);
            if ($prediction) {
                $predictions[] = $prediction;
            }
        }

        // Sort by confidence score
        usort($predictions, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        // Get all places for the dropdown (no authentication needed for admin)
        $places = Place::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get historical price data for charts (for products with predictions)
        $chartData = [];
        foreach ($predictions as $prediction) {
            $productName = $prediction['product_name'];
            $historicalPrices = ProductPrice::where('product_name', $productName)
                ->where('recorded_at', '>=', now()->subDays(90))
                ->orderBy('recorded_at', 'asc')
                ->select('price', 'recorded_at')
                ->get();

            $chartData[$productName] = [
                'labels' => $historicalPrices->map(function($price) {
                    return $price->recorded_at->format('M j');
                })->toArray(),
                'prices' => $historicalPrices->pluck('price')->toArray(),
            ];
        }

        return view('market-analysis.price-predictions', compact('predictions', 'places', 'chartData'));
    }

    private function predictProductPrice($productName, $daysAhead = 30)
    {
        // Get historical price data (last 90 days)
        $priceHistory = ProductPrice::where('product_name', $productName)
            ->where('recorded_at', '>=', now()->subDays(90))
            ->orderBy('recorded_at', 'asc')
            ->get(['price', 'recorded_at', 'place_id']);

        if ($priceHistory->count() < 2) {
            return null; // Not enough data for prediction
        }

        // Calculate moving average (simple trend analysis)
        $recentPrices = $priceHistory->take(-10); // Last 10 records
        $avgPrice = $recentPrices->avg('price');

        // Calculate price trend (slope)
        $trend = $this->calculatePriceTrend($priceHistory);

        // Seasonal adjustment
        $seasonalFactor = $this->getSeasonalFactor($productName);

        // Supply/Demand impact (simplified)
        $supplyDemandFactor = $this->getSupplyDemandFactor($productName);

        // Base prediction
        $predictedPrice = $avgPrice * (1 + $trend) * $seasonalFactor * $supplyDemandFactor;

        // Calculate confidence based on data quality
        $confidence = min(0.95, $priceHistory->count() / 50); // Max 95% confidence

        // Price range (prediction interval)
        $priceStd = $this->calculatePriceStandardDeviation($recentPrices);
        $lowerBound = $predictedPrice * (1 - ($priceStd / $predictedPrice) * 1.96);
        $upperBound = $predictedPrice * (1 + ($priceStd / $predictedPrice) * 1.96);

        return [
            'product_name' => $productName,
            'current_avg_price' => round($avgPrice, 0),
            'predicted_price' => round($predictedPrice, 0),
            'price_change_percent' => round((($predictedPrice - $avgPrice) / $avgPrice) * 100, 1),
            'confidence' => round($confidence * 100, 1),
            'price_range' => [
                'lower' => round(max(0, $lowerBound), 0),
                'upper' => round($upperBound, 0)
            ],
            'trend' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable'),
            'data_points' => $priceHistory->count(),
            'last_updated' => $priceHistory->last()->recorded_at->diffForHumans(),
        ];
    }

    private function calculatePriceTrend($priceHistory)
    {
        if ($priceHistory->count() < 2) return 0;

        $prices = $priceHistory->pluck('price')->toArray();
        $times = $priceHistory->pluck('recorded_at')->map(function($date) {
            return $date->timestamp;
        })->toArray();

        // Simple linear regression for trend
        $n = count($prices);
        if ($n < 2) return 0;

        $sumX = array_sum($times);
        $sumY = array_sum($prices);
        $sumXY = 0;
        $sumXX = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $times[$i] * $prices[$i];
            $sumXX += $times[$i] * $times[$i];
        }

        // Prevent division by zero
        $denominator = $n * $sumXX - $sumX * $sumX;
        if ($denominator == 0) {
            // No variation in time (all timestamps same) or perfect correlation
            // Check if prices are changing over time
            $priceVariance = max($prices) - min($prices);
            if ($priceVariance == 0) {
                return 0; // No price change, flat trend
            } else {
                // Prices vary but timestamps don't - use simple average change
                $avgPrice = $sumY / $n;
                $dailyChangePercent = ($priceVariance / $avgPrice) * 0.01; // Very small change
                return $dailyChangePercent;
            }
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;

        // Normalize slope to percentage change per day
        $avgPrice = $sumY / $n;
        if ($avgPrice == 0) return 0; // Prevent division by zero

        $dailyChangePercent = ($slope / $avgPrice) * 86400; // 86400 seconds per day

        return $dailyChangePercent;
    }

    private function calculatePriceStandardDeviation($prices)
    {
        if ($prices->count() < 2) return 0;

        $priceValues = $prices->pluck('price')->toArray();
        $mean = array_sum($priceValues) / count($priceValues);

        $variance = 0;
        foreach ($priceValues as $price) {
            $variance += pow($price - $mean, 2);
        }
        $variance /= count($priceValues) - 1; // Sample variance

        return sqrt($variance);
    }

    private function getSeasonalFactor($productName)
    {
        $currentMonth = (int) date('m');

        // Seasonal products
        $seasonalProducts = [
            'mangga' => [3, 4, 5, 6, 7, 8, 9], // March - September
            'durian' => [6, 7, 8, 9, 10], // June - October
            'salak' => [1, 2, 3, 7, 8, 9, 10, 11, 12], // Most months
            'rambutan' => [5, 6, 7, 8, 9, 10], // May - October
        ];

        $productLower = strtolower($productName);

        foreach ($seasonalProducts as $product => $peakMonths) {
            if (str_contains($productLower, $product)) {
                return in_array($currentMonth, $peakMonths) ? 1.15 : 0.90; // +15% in season, -10% off-season
            }
        }

        // Holiday season boost (December, January)
        if (in_array($currentMonth, [12, 1])) {
            return 1.08; // +8% for holidays
        }

        return 1.00; // No seasonal effect
    }

    private function getSupplyDemandFactor($productName)
    {
        // Get current supply/demand ratio from places data
        // This is a simplified version - in real implementation you'd analyze
        // actual supply chain data

        $productPlaces = Place::where('name', 'like', '%' . $productName . '%')
            ->orWhere('category', 'like', '%' . $productName . '%')
            ->count();

        // Mock supply/demand calculation
        // In real implementation, this would analyze:
        // - Current inventory levels
        // - Recent sales data
        // - Market demand indicators

        if ($productPlaces > 10) {
            return 0.95; // High supply, slight price decrease
        } elseif ($productPlaces > 5) {
            return 1.00; // Balanced
        } else {
            return 1.10; // Low supply, higher prices
        }
    }
}
