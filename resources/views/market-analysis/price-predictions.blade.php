@extends('layouts.app')

@section('page-title', 'Price Predictions')
@section('page-subtitle', 'AI-powered price forecasting with Puter AI & statistical analysis')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-coins mr-2"></i>
                    Ramalan Harga & Analisis
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <i class="fas fa-chart-line mr-1"></i>
                        Prediksi harga berdasarkan data historis dan tren pasar
                    </small>
                </div>
            </div>
            <div class="card-body">
                @if(count($predictions) > 0)
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-lightbulb"></i> Hasil Analisis Ramalan Harga</h5>
                    <p>Ramalan harga ini didasarkan pada data historis, tren pasar, faktor musiman, dan analisis penawaran-permintaan.</p>
                </div>

                <div class="row">
                    @foreach($predictions as $prediction)
                    <div class="col-md-6 mb-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    {{ $prediction['product_name'] }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <div class="border p-2 rounded">
                                            <strong class="text-primary h4">Rp {{ number_format($prediction['current_avg_price']) }}</strong><br>
                                            <small class="text-muted">Current Price</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border p-2 rounded">
                                            <strong class="text-{{ $prediction['predicted_price'] > $prediction['current_avg_price'] ? 'danger' : 'success' }} h4">
                                                Rp {{ number_format($prediction['predicted_price']) }}
                                            </strong><br>
                                            <small class="text-muted">Predicted (30 days)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-chart-line mr-1"></i>Perubahan Harga:</span>
                                        <span class="badge badge-{{ $prediction['price_change_percent'] > 0 ? 'danger' : 'success' }} h6">
                                            <i class="fas fa-arrow-{{ $prediction['price_change_percent'] > 0 ? 'up' : 'down' }} mr-1"></i>
                                            {{ abs($prediction['price_change_percent']) }}%
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-trend-line mr-1"></i>Arah Tren:</span>
                                        <span class="badge badge-{{ $prediction['trend'] === 'up' ? 'danger' : ($prediction['trend'] === 'down' ? 'success' : 'secondary') }}">
                                            <i class="fas fa-arrow-{{ $prediction['trend'] === 'up' ? 'up' : ($prediction['trend'] === 'down' ? 'down' : 'right') }} mr-1"></i>
                                            {{ $prediction['trend'] === 'up' ? 'Naik' : ($prediction['trend'] === 'down' ? 'Turun' : 'Stabil') }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-target mr-1"></i>Tingkat Kepercayaan:</span>
                                        <span class="badge badge-{{ $prediction['confidence'] >= 80 ? 'success' : ($prediction['confidence'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $prediction['confidence'] }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Rentang Harga (95% akurasi):</small>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-success"><strong>Rp {{ number_format($prediction['price_range']['lower']) }}</strong></small>
                                        <small class="text-danger"><strong>Rp {{ number_format($prediction['price_range']['upper']) }}</strong></small>
                                    </div>
                                    <div class="progress mt-1" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 50%"></div>
                                        <div class="progress-bar bg-danger" style="width: 50%"></div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="fas fa-lightbulb text-warning mr-1"></i>
                                        Harga kemungkinan berada dalam rentang ini
                                    </small>
                                </div>

                                <div class="text-right">
                                    <small class="text-muted">
                                        <i class="fas fa-database mr-1"></i>
                                        {{ $prediction['data_points'] }} data points |
                                        Updated {{ $prediction['last_updated'] }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-coins fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No Price Predictions Available</h4>
                    <p class="text-muted mb-4">
                        Price predictions require historical price data. Start by adding product prices to enable AI forecasting.
                    </p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPriceModal">
                        <i class="fas fa-plus mr-2"></i>
                        Add Product Price
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Price Trend Charts -->
@if(count($predictions) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Price Trend Analysis
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        Historical price movements for products with available data
                    </small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($predictions as $index => $prediction)
                        @if($index < 2) <!-- Show only first 2 products for demo -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-line mr-2"></i>
                                        {{ $prediction['product_name'] }} - Price History
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="priceChart{{ $index }}" style="max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                @if(count($predictions) > 2)
                <div class="text-center">
                    <small class="text-muted">Showing trends for top 2 products. Add more historical data to see trends for all products.</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- AI Methodology Explanation -->
@if(count($predictions) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    Free AI Price Prediction Methodology
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-calculator text-primary"></i> Statistical Algorithms Used:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-chart-line text-success"></i> <strong>Linear Regression:</strong> Trend analysis from historical data</li>
                            <li><i class="fas fa-wave-square text-info"></i> <strong>Moving Averages:</strong> Smooth price fluctuations</li>
                            <li><i class="fas fa-calendar-alt text-warning"></i> <strong>Seasonal Adjustments:</strong> Holiday and seasonal effects</li>
                            <li><i class="fas fa-balance-scale text-danger"></i> <strong>Supply/Demand Factors:</strong> Market equilibrium analysis</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-lightbulb text-success"></i> How Predictions Work:</h5>
                        <div class="timeline timeline-inverse">
                            <div class="time-label">
                                <span class="bg-success">Step 1</span>
                            </div>
                            <div>
                                <i class="fas fa-history bg-blue"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Collect Historical Data</h3>
                                    <div class="timeline-body">
                                        Gather 90 days of price history for accurate trend analysis
                                    </div>
                                </div>
                            </div>
                            <div class="time-label">
                                <span class="bg-info">Step 2</span>
                            </div>
                            <div>
                                <i class="fas fa-chart-line bg-green"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Calculate Trends</h3>
                                    <div class="timeline-body">
                                        Use linear regression to identify price movement patterns
                                    </div>
                                </div>
                            </div>
                            <div class="time-label">
                                <span class="bg-warning">Step 3</span>
                            </div>
                            <div>
                                <i class="fas fa-adjust bg-yellow"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Apply Adjustments</h3>
                                    <div class="timeline-body">
                                        Factor in seasonal effects and market supply/demand
                                    </div>
                                </div>
                            </div>
                            <div class="time-label">
                                <span class="bg-danger">Step 4</span>
                            </div>
                            <div>
                                <i class="fas fa-bullseye bg-red"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Generate Prediction</h3>
                                    <div class="timeline-body">
                                        Produce 30-day price forecast with confidence intervals
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-question-circle text-info"></i> Understanding Confidence Levels:</h5>
                        <ul class="list-unstyled">
                            <li><strong class="text-success">80-95%:</strong> High confidence - Very reliable predictions</li>
                            <li><strong class="text-warning">60-79%:</strong> Medium confidence - Good for planning</li>
                            <li><strong class="text-danger">0-59%:</strong> Low confidence - Use with caution</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-exclamation-triangle text-warning"></i> Important Notes:</h5>
                        <div class="alert alert-warning">
                            <ul class="mb-0">
                                <li>Predictions are statistical estimates, not guarantees</li>
                                <li>External factors (weather, economy) may affect actual prices</li>
                                <li>More historical data = better prediction accuracy</li>
                                <li>Regular price updates improve AI learning</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Add Price Modal -->
<div class="modal fade" id="addPriceModal" tabindex="-1" role="dialog" aria-labelledby="addPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPriceModalLabel">
                    <i class="fas fa-plus mr-2"></i>
                    Add Product Price
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addPriceForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                        <small class="form-text text-muted">e.g., Mangga, Beras Premium, Ayam</small>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="place_id">Place</label>
                        <select class="form-control" id="place_id" name="place_id" required>
                            <option value="">Select Place</option>
                            @foreach($places as $place)
                            <option value="{{ $place->id }}">{{ $place->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Save Price
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery and Chart.js are loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery not loaded');
        return;
    }

    // Handle form submission
    $('#addPriceForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            product_name: $('#product_name').val(),
            price: $('#price').val(),
            place_id: $('#place_id').val(),
            source: 'manual',
            recorded_at: new Date().toISOString()
        };

        $.ajax({
            url: '/product-prices',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#addPriceModal').modal('hide');
                $('#addPriceForm')[0].reset();

                // Show success message
                toastr.success('Product price added successfully!');

                // Reload the page to show new predictions
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Failed to add price';
                toastr.error(error);
            }
        });
    });

    // Price Trend Charts
    @if(count($predictions) > 0)
        @foreach($predictions as $index => $prediction)
            @if($index < 2 && isset($chartData[$prediction['product_name']]))
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded for price charts');
            } else {
                var canvas{{ $index }} = document.getElementById('priceChart{{ $index }}');
                if (canvas{{ $index }}) {
                    var ctxPrice{{ $index }} = canvas{{ $index }}.getContext('2d');
                    var priceChart{{ $index }} = new Chart(ctxPrice{{ $index }}, {
                type: 'line',
                data: {
                    labels: @json($chartData[$prediction['product_name']]['labels']),
                    datasets: [{
                        label: 'Historical Price (Rp)',
                        data: @json($chartData[$prediction['product_name']]['prices']),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxTicksLimit: 10
                            }
                        }
                    }
                }
            });
                } else {
                    console.error('Canvas element not found for price chart {{ $index }}');
                }
            }
            @endif
        @endforeach
    @endif
});
</script>

<!-- Puter.js Library -->
<script src="https://js.puter.com/v2/"></script>

<!-- AI Model Settings & Puter AI Integration -->
@if(count($predictions) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-robot mr-2"></i>
                    AI Model Settings & Analysis
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <i class="fas fa-brain mr-1"></i>
                        Advanced AI-powered price analysis
                    </small>
                </div>
            </div>
            <div class="card-body">
                <!-- AI Model Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="aiModelSelect">
                                <i class="fas fa-cogs mr-1"></i>
                                Select AI Model for Analysis:
                            </label>
                            <select class="form-control" id="aiModelSelect">
                                <option value="">Loading models...</option>
                            </select>
                            <small class="form-text text-muted">
                                Choose the AI model that will analyze your price data
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Analysis Mode:</label><br>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active">
                                    <input type="radio" name="analysisMode" value="hybrid" checked>
                                    <i class="fas fa-balance-scale mr-1"></i>
                                    Hybrid (Statistical + AI)
                                </label>
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="analysisMode" value="ai-only">
                                    <i class="fas fa-robot mr-1"></i>
                                    AI Only
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Analysis Results -->
                <div id="aiAnalysisSection" style="display: none;">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-brain mr-2"></i> Puter AI Analysis Results</h5>
                        <div id="aiAnalysisContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Analyzing with AI...</span>
                                </div>
                                <p class="mt-2">AI is analyzing your price data...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Run AI Analysis Button -->
                <div class="text-center mb-3">
                    <button type="button" class="btn btn-success btn-lg" id="runAiAnalysisBtn">
                        <i class="fas fa-play mr-2"></i>
                        Run AI Price Analysis
                    </button>
                    <small class="d-block text-muted mt-1">
                        Uses selected AI model to provide advanced insights and predictions
                    </small>
                </div>

                <!-- AI vs Statistical Comparison -->
                <div id="comparisonSection" style="display: none;">
                    <hr>
                    <h5><i class="fas fa-chart-bar mr-2"></i> AI vs Statistical Analysis Comparison</h5>
                    <div class="row" id="comparisonContent">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Puter AI Integration Script -->
<script>
// Puter AI Integration for Price Predictions
document.addEventListener('DOMContentLoaded', function() {
    let availableModels = [];
    let selectedModel = localStorage.getItem('mafazaAiModel') || '';

    // Initialize AI Model Selection
    async function initializeAIModels() {
        try {
            console.log('Loading available AI models...');
            availableModels = await puter.ai.listModels();

            const modelSelect = document.getElementById('aiModelSelect');
            modelSelect.innerHTML = '<option value="">Select AI Model...</option>';

            // Sort models by provider and add to dropdown
            const sortedModels = availableModels.sort((a, b) => {
                if (a.provider !== b.provider) {
                    return a.provider.localeCompare(b.provider);
                }
                return (a.name || a.id).localeCompare(b.name || b.id);
            });

            sortedModels.forEach(model => {
                const option = document.createElement('option');
                option.value = model.id;

                // Format display name
                let displayName = model.name || model.id;
                if (model.provider) {
                    displayName += ` (${model.provider})`;
                }

                // Add context info if available
                if (model.context) {
                    displayName += ` - ${model.context.toLocaleString()} tokens`;
                }

                option.textContent = displayName;

                // Select previously chosen model
                if (model.id === selectedModel) {
                    option.selected = true;
                }

                modelSelect.appendChild(option);
            });

            // Set default if no model selected
            if (!selectedModel && sortedModels.length > 0) {
                // Prefer Claude or GPT models for analysis
                const preferredModels = sortedModels.filter(m =>
                    m.id.includes('claude') ||
                    m.id.includes('gpt') ||
                    m.id.includes('opus') ||
                    m.id.includes('sonnet')
                );

                if (preferredModels.length > 0) {
                    selectedModel = preferredModels[0].id;
                    modelSelect.value = selectedModel;
                    localStorage.setItem('mafazaAiModel', selectedModel);
                }
            }

            console.log(`Loaded ${availableModels.length} AI models`);
        } catch (error) {
            console.error('Failed to load AI models:', error);
            document.getElementById('aiModelSelect').innerHTML =
                '<option value="">Failed to load models</option>';
        }
    }

    // Handle model selection change
    document.getElementById('aiModelSelect').addEventListener('change', function() {
        selectedModel = this.value;
        localStorage.setItem('mafazaAiModel', selectedModel);
        console.log('Selected AI model:', selectedModel);
    });

    // Run AI Analysis
    document.getElementById('runAiAnalysisBtn').addEventListener('click', async function() {
        if (!selectedModel) {
            alert('Please select an AI model first!');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;

        // Show loading state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Analyzing...';
        btn.disabled = true;

        // Show analysis section
        document.getElementById('aiAnalysisSection').style.display = 'block';
        document.getElementById('comparisonSection').style.display = 'block';

        try {
            // Get price data from PHP (passed as JSON)
            const priceData = @json($predictions ?? []);

            if (priceData.length === 0) {
                throw new Error('No price data available for analysis');
            }

            // Prepare AI prompt
            const analysisMode = document.querySelector('input[name="analysisMode"]:checked').value;
            const prompt = createAIPrompt(priceData, analysisMode);

            console.log('Running AI analysis with model:', selectedModel);
            console.log('Analysis mode:', analysisMode);

            // Call Puter AI
            const aiResponse = await puter.ai.chat(prompt, {
                model: selectedModel,
                stream: false
            });

            console.log('AI Response received:', aiResponse);

            // Display results
            displayAIResults(aiResponse, priceData);

        } catch (error) {
            console.error('AI Analysis failed:', error);
            document.getElementById('aiAnalysisContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i>AI Analysis Failed</h6>
                    <p>${error.message}</p>
                    <small class="text-muted">Falling back to statistical analysis only.</small>
                </div>
            `;
        } finally {
            // Reset button
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    // Create AI prompt based on data and mode
    function createAIPrompt(priceData, mode) {
        let prompt = `You are an expert agricultural economist and market analyst. Analyze the following price data and provide insights:\n\n`;

        // Add data summary
        prompt += `PRICE DATA SUMMARY:\n`;
        priceData.forEach(product => {
            prompt += `- ${product.product_name}:\n`;
            prompt += `  Current: Rp ${product.current_avg_price.toLocaleString()}\n`;
            prompt += `  Predicted (30 days): Rp ${product.predicted_price.toLocaleString()}\n`;
            prompt += `  Change: ${product.price_change_percent}%\n`;
            prompt += `  Confidence: ${product.confidence}%\n`;
            prompt += `  Trend: ${product.trend}\n`;
            prompt += `  Data points: ${product.data_points}\n\n`;
        });

        if (mode === 'hybrid') {
            prompt += `\nANALYSIS REQUEST:\n`;
            prompt += `1. Compare the statistical predictions with your AI analysis\n`;
            prompt += `2. Identify any discrepancies and explain why they might occur\n`;
            prompt += `3. Consider seasonal factors, market conditions, and external influences\n`;
            prompt += `4. Provide a confidence level for your AI predictions\n`;
            prompt += `5. Suggest optimal pricing strategies for sellers\n\n`;
        } else {
            prompt += `\nAI-ONLY ANALYSIS REQUEST:\n`;
            prompt += `Provide fresh AI-powered predictions without relying on the statistical data provided above.\n`;
            prompt += `Consider current market trends, seasonal patterns, and economic factors.\n\n`;
        }

        prompt += `FORMAT YOUR RESPONSE AS:\n`;
        prompt += `- Product-by-product analysis\n`;
        prompt += `- Key insights and reasoning\n`;
        prompt += `- Specific price predictions with confidence levels\n`;
        prompt += `- Market recommendations\n\n`;
        prompt += `Be concise but comprehensive. Focus on actionable insights.`;

        return prompt;
    }

    // Display AI Results
    function displayAIResults(aiResponse, originalData) {
        console.log('Raw AI Response:', aiResponse);
        console.log('Response type:', typeof aiResponse);
        console.log('Response keys:', Object.keys(aiResponse));

        // Handle different response formats from Puter AI
        let content = '';

        try {
            if (typeof aiResponse === 'string') {
                content = aiResponse;
            } else if (aiResponse && typeof aiResponse === 'object') {
                // Check all possible content properties
                if (aiResponse.content && typeof aiResponse.content === 'string') {
                    content = aiResponse.content;
                    console.log('Found content in aiResponse.content');
                } else if (aiResponse.message && typeof aiResponse.message === 'string') {
                    content = aiResponse.message;
                    console.log('Found content in aiResponse.message');
                } else if (aiResponse.text && typeof aiResponse.text === 'string') {
                    content = aiResponse.text;
                    console.log('Found content in aiResponse.text');
                } else if (aiResponse.choices && Array.isArray(aiResponse.choices) && aiResponse.choices[0]) {
                    const choice = aiResponse.choices[0];
                    content = choice.message?.content || choice.text || '';
                    console.log('Found content in aiResponse.choices[0]');
                } else {
                    // Check if any property contains string content
                    for (const [key, value] of Object.entries(aiResponse)) {
                        if (typeof value === 'string' && value.length > 10) {
                            content = value;
                            console.log('Found content in property:', key);
                            break;
                        }
                    }
                }
            }

            // If still no content found, stringify the response
            if (!content) {
                console.warn('No string content found, using JSON stringify');
                content = JSON.stringify(aiResponse, null, 2);
            }

            console.log('Extracted content length:', content.length);
            console.log('Content preview:', content.substring(0, 200) + '...');

        } catch (error) {
            console.error('Error extracting content:', error);
            content = 'Error: Could not parse AI response. ' + error.message;
        }

        // Clean up content (remove extra quotes if present)
        content = content.replace(/^["']|["']$/g, '');

        // Format AI response with HTML
        let formattedContent = content
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/- /g, '• ')
            .replace(/---/g, '<hr>')
            .replace(/### (.*?)(<br>|$)/g, '<h4>$1</h4>')
            .replace(/## (.*?)(<br>|$)/g, '<h5>$1</h5>')
            .replace(/# (.*?)(<br>|$)/g, '<h3>$1</h3>');

        // Add metadata
        const metadata = `
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-robot mr-1"></i>
                    Analyzed by ${selectedModel} |
                    <i class="fas fa-clock mr-1"></i>
                    ${new Date().toLocaleString('id-ID')}
                </small>
            </div>
        `;

        document.getElementById('aiAnalysisContent').innerHTML = metadata + formattedContent;

        // Generate comparison
        generateComparison(originalData);
    }

    // Generate AI vs Statistical Comparison
    function generateComparison(originalData) {
        let comparisonHTML = '';

        originalData.forEach(product => {
            comparisonHTML += `
                <div class="col-md-6 mb-3">
                    <div class="card border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">${product.product_name}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border p-2 rounded">
                                        <strong class="text-primary">Rp ${product.predicted_price.toLocaleString()}</strong><br>
                                        <small class="text-muted">Statistical</small><br>
                                        <small class="badge badge-info">${product.confidence}% confidence</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border p-2 rounded">
                                        <strong class="text-success">AI Enhanced</strong><br>
                                        <small class="text-muted">See analysis above</small><br>
                                        <small class="badge badge-success">AI Insights</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById('comparisonContent').innerHTML = comparisonHTML;
    }

    // Initialize on page load
    initializeAIModels();
});
</script>
@endsection
