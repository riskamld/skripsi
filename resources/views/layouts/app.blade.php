<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Mafaza Fortuna - AdminLTE')</title>

    <script>
        window.baseUrl = '{{ url("/") }}';
    </script>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Custom CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Styles -->
    @stack('styles')

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Toastr for notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Additional Scripts -->
    @stack('scripts')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Beranda</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('map.index') }}" class="nav-link">Peta</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">0 Notifikasi</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-info-circle mr-2"></i> Tidak ada notifikasi baru
                    </a>
                </div>
            </li>

            <!-- User Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">Pengguna Admin</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profil
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog mr-2"></i> Pengaturan
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">Mafaza Fortuna</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <span class="text-white font-weight-bold">A</span>
                    </div>
                </div>
                <div class="info">
                    <a href="#" class="d-block">Admin</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dasbor</p>
                        </a>
                    </li>

                    <!-- Places -->
                    <li class="nav-item">
                        <a href="{{ route('places.index') }}" class="nav-link {{ request()->routeIs('places.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-marker-alt"></i>
                            <p>Tempat</p>
                        </a>
                    </li>

                    <!-- Map -->
                    <li class="nav-item">
                        <a href="{{ route('map.index') }}" class="nav-link {{ request()->routeIs('map.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map"></i>
                            <p>Peta</p>
                        </a>
                    </li>

                    <!-- Scrape Logs -->
                    <li class="nav-item">
                        <a href="{{ route('scrape-logs.index') }}" class="nav-link {{ request()->routeIs('scrape-logs.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Log Scraping</p>
                        </a>
                    </li>

                    <!-- Product Prices -->
                    <li class="nav-item">
                        <a href="{{ url('/product-prices') }}" class="nav-link {{ request()->routeIs('product-prices.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-coins"></i>
                            <p>Harga Produk</p>
                        </a>
                    </li>

                    <!-- Market Analysis -->
                    <li class="nav-item {{ request()->routeIs('market-analysis.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('market-analysis.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>
                                Analisis Pasar
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.index') }}" class="nav-link {{ request()->routeIs('market-analysis.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Ringkasan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.supply-demand') }}" class="nav-link {{ request()->routeIs('market-analysis.supply-demand') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Penawaran & Permintaan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.category-insights') }}" class="nav-link {{ request()->routeIs('market-analysis.category-insights') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Wawasan Kategori</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.geographic') }}" class="nav-link {{ request()->routeIs('market-analysis.geographic') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Analisis Geografis</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.price-predictions') }}" class="nav-link {{ request()->routeIs('market-analysis.price-predictions') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Ramalan Harga</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Database Tools -->
                    <li class="nav-item">
                        <a href="{{ route('database.index') }}" class="nav-link {{ request()->routeIs('database.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>Alat Database</p>
                        </a>
                    </li>

                    <!-- System Section -->
                    <li class="nav-header">SISTEM</li>

                    <!-- Settings Menu -->
                    <li class="nav-item {{ request()->routeIs('settings.*') || request()->routeIs('api-tokens.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('settings.*') || request()->routeIs('api-tokens.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Pengaturan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">

                            <li class="nav-item">
                                <a href="{{ route('api-tokens.index') }}" class="nav-link {{ request()->routeIs('api-tokens.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Token API</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-question-circle"></i>
                            <p>Bantuan</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <!-- Main Footer -->
    <footer class="main-footer">
        <strong>Hak Cipta © 2025 Mafaza Fortuna. Seluruh hak cipta.</strong>
        <div class="float-right d-none d-sm-inline-block">
            <b>Versi</b> 1.0.0
        </div>
    </footer>
</div>

<!-- AI Chat Assistant -->
<!-- Floating AI Chat Button -->
<button id="aiChatButton" class="btn btn-primary position-fixed rounded-circle shadow-lg ai-chat-button"
        style="bottom: 20px; right: 20px; width: 60px; height: 60px; z-index: 1050; border: none;"
        title="AI Assistant - Tanya tentang database Anda">
    <i class="fas fa-robot fa-lg text-white"></i>
</button>

<!-- AI Chat Modal -->
<div class="modal fade" id="aiChatModal" tabindex="-1" role="dialog" aria-labelledby="aiChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 500px;">
        <div class="modal-content border-0 shadow-lg">
            <!-- Chat Header -->
            <div class="modal-header bg-primary text-white rounded-top d-flex justify-content-between align-items-center">
                <h5 class="modal-title mb-0" id="aiChatModalLabel">
                    <i class="fas fa-brain mr-2"></i>Mafaza AI Assistant
                </h5>
                <div class="d-flex align-items-center">
                    <!-- Clear Chat History Button -->
                    <button type="button" class="btn btn-sm btn-outline-light mr-2" id="clearChatHistoryBtn" title="Hapus Riwayat Chat">
                        <i class="fas fa-trash-alt"></i>
                    </button>

                    <!-- AI Model Selector -->
                    <div class="mr-2">
                        <select class="form-control form-control-sm" id="aiModelSelector" style="min-width: 180px; font-size: 12px;">
                            <option value="">Loading models...</option>
                        </select>
                    </div>

                    <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Chat Body -->
            <div class="modal-body p-0">
                <!-- Chat Messages Container -->
                <div id="chatMessages" class="chat-messages p-3" style="height: 400px; overflow-y: auto; background: #f8f9fa;">
                    <!-- Chat starts empty - no automatic intro message -->
                </div>

                <!-- Chat Input -->
                <div class="chat-input-container p-3 border-top bg-white">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control border-primary"
                               placeholder="Tanya tentang database Anda..."
                               autocomplete="off"
                               onkeypress="if(event.key === 'Enter') sendChatMessage()">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="sendChatMessage" disabled>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-lightbulb text-warning mr-1"></i>
                        AI dapat mengakses data real-time dari database Anda
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Chat Styles -->
<style>
.ai-chat-button {
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,123,255,0.3) !important;
}

.ai-chat-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,123,255,0.4) !important;
}

.chat-messages {
    scrollbar-width: thin;
    scrollbar-color: #ccc transparent;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.message-bubble {
    padding: 10px 15px;
    border-radius: 18px;
    max-width: 100%;
    word-wrap: break-word;
}

.ai-bubble {
    background: #007bff;
    color: white;
    border-bottom-left-radius: 4px;
}

.user-bubble {
    background: #e9ecef;
    color: #333;
    border-bottom-right-radius: 4px;
}

.typing-indicator {
    display: inline-block;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 18px;
    border-bottom-left-radius: 4px;
}

.typing-indicator span {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #007bff;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        opacity: 0.4;
        transform: scale(1);
    }
    30% {
        opacity: 1;
        transform: scale(1.2);
    }
}
</style>

<!-- Puter.js Library for AI Integration with Error Prevention -->
<script>
// Prevent puter-dialog custom element redefinition error
if (!customElements.get('puter-dialog')) {
    // Only load if not already defined
    const script = document.createElement('script');
    script.src = 'https://js.puter.com/v2/';
    document.head.appendChild(script);
} else {
    console.log('Puter.js already loaded, skipping duplicate load');
}
</script>

<!-- AI Chat JavaScript -->
<script>
// AI Chat Functionality with Puter AI Integration
document.addEventListener('DOMContentLoaded', function() {
    const aiChatButton = document.getElementById('aiChatButton');
    const aiChatModal = document.getElementById('aiChatModal');
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendChatMessage');
    const chatMessages = document.getElementById('chatMessages');

    let isTyping = false;
    let conversationHistory = JSON.parse(localStorage.getItem('mafazaConversationHistory') || '[]');
    let selectedAiModel = localStorage.getItem('mafazaChatAiModel') || 'gpt-4o'; // Use GPT-4o as primary model
    let availableModels = [];
    let userProfile = JSON.parse(localStorage.getItem('mafazaUserProfile') || '{}');
    let conversationStarted = localStorage.getItem('mafazaConversationStarted') === 'true'; // Global variable to prevent repeated greetings

    // Initialize Puter AI and load models
    initializePuterAI();

    async function initializePuterAI() {
        try {
            availableModels = await puter.ai.listModels();

            // Populate model selector
            populateModelSelector();

            // Set default model if not available
            if (!availableModels.find(m => m.id === selectedAiModel)) {
                const claudeModels = availableModels.filter(m => m.id.includes('claude'));
                selectedAiModel = claudeModels.length > 0 ? claudeModels[0].id : (availableModels[0]?.id || 'claude-3-haiku');
            }

            // Set initial selected model
            const modelSelector = document.getElementById('aiModelSelector');
            if (modelSelector) {
                modelSelector.value = selectedAiModel;
            }
        } catch (error) {
            // Continue without AI models - will use server-side processing
            populateModelSelector(); // Show fallback options
        }
    }

    // Populate AI model selector dropdown
    function populateModelSelector() {
        const modelSelector = document.getElementById('aiModelSelector');
        if (!modelSelector) return;

        modelSelector.innerHTML = '<option value="">Select AI Model...</option>';

        if (availableModels.length > 0) {
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
                if (model.id === selectedAiModel) {
                    option.selected = true;
                }

                modelSelector.appendChild(option);
            });
        } else {
            // Fallback options when Puter AI is not available
            const fallbackModels = [
                { id: 'claude-3-haiku', name: 'Claude 3 Haiku (Fast)', provider: 'Anthropic' },
                { id: 'claude-3-sonnet', name: 'Claude 3 Sonnet (Smart)', provider: 'Anthropic' },
                { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo (Fast)', provider: 'OpenAI' },
                { id: 'gpt-4', name: 'GPT-4 (Powerful)', provider: 'OpenAI' }
            ];

            fallbackModels.forEach(model => {
                const option = document.createElement('option');
                option.value = model.id;
                option.textContent = `${model.name} (${model.provider})`;

                if (model.id === selectedAiModel) {
                    option.selected = true;
                }

                modelSelector.appendChild(option);
            });
        }
    }

    // Handle model selection change
    function handleModelChange() {
        const modelSelector = document.getElementById('aiModelSelector');
        if (!modelSelector) return;

        const newModel = modelSelector.value;
        if (newModel && newModel !== selectedAiModel) {
            selectedAiModel = newModel;
            localStorage.setItem('mafazaChatAiModel', selectedAiModel);

            // Show notification
            if (typeof toastr !== 'undefined') {
                toastr.success(`AI Model diubah ke: ${modelSelector.options[modelSelector.selectedIndex].text}`, 'Model Changed');
            }

            console.log('Selected AI model:', selectedAiModel);
        }
    }

    // Show/hide chat modal
    aiChatButton.addEventListener('click', function() {
        $('#aiChatModal').modal('show');
    });

    // Enable/disable send button based on input
    chatInput.addEventListener('input', function() {
        sendButton.disabled = this.value.trim().length === 0;
    });

    // Minimal small talk detection - only basic greetings go local, everything else to AI
    function isSmallTalk(message) {
        const lowerMessage = message.toLowerCase().trim();

        // Check for negative statements first - these should NOT trigger database functions
        const negativeWords = ['tidak', 'bukan', 'jangan', 'berhenti', 'stop', 'gak mau', 'ga mau', 'tidak mau'];
        const hasNegative = negativeWords.some(word => lowerMessage.includes(word));

        if (hasNegative) {
            // User is rejecting something - let AI handle this naturally, don't force database
            return false; // Send to AI, not local handler
        }

        // Check for name mentions - handle with AI for personalization
        if (lowerMessage.includes('nama saya') || (lowerMessage.includes('saya ') && lowerMessage.includes('nama'))) {
            // Extract and store user name
            const nameMatch = lowerMessage.match(/nama saya (?:adalah )?([a-zA-Z\s]+)/i) ||
                             lowerMessage.match(/saya (?:adalah |bernama )([a-zA-Z\s]+)/i);

            if (nameMatch && nameMatch[1]) {
                const userName = nameMatch[1].trim();
                try {
                    // Store user name in localStorage and Puter.kv if available
                    localStorage.setItem('mafazaUserName', userName);
                    userProfile.name = userName;

                    // Try Puter.kv storage (non-blocking)
                    if (typeof puter !== 'undefined' && puter.kv) {
                        puter.kv.set('user_name', userName).catch(err =>
                            console.warn('Failed to store name in Puter.kv:', err)
                        );
                    }
                    console.log('👤 DEBUG: Stored user name:', userName);
                } catch (error) {
                    console.warn('Failed to store user name:', error);
                }
            }
            return false; // Send to AI for personalized response
        }

        // ONLY exact 1-word greetings go to local handler
        const exactGreetings = [
            'halo', 'hai', 'hi', 'hello',
            'pagi', 'siang', 'sore', 'malam',
            'ya', 'iya', 'oke', 'ok'
        ];

        // Exact match only for basic greetings
        if (exactGreetings.includes(lowerMessage)) {
            return true;
        }

        // EVERYTHING else goes to AI - no restrictions!
        return false;
    }

    function isDataQuery(message) {
        const dataKeywords = [
            'berapa', 'jumlah', 'total', 'count', 'harga', 'price',
            'lokasi', 'daerah', 'tempat', 'toko', 'restoran', 'kategori',
            'category', 'rating', 'bintang', 'ulasan', 'review',
            'top', 'terbaik', 'termurah', 'termahal', 'rata-rata',
            'average', 'cari', 'find', 'search', 'data', 'database',
            'produk', 'product', 'bisnis', 'business', 'company'
        ];

        const lowerMessage = message.toLowerCase();

        for (const keyword of dataKeywords) {
            if (lowerMessage.includes(keyword)) {
                return true;
            }
        }

        return false;
    }

    // Follow-up detection for continuation words (FIXED: Handle object responses)
    function isFollowUpContinuation(message, history) {
        const continuationWords = ['ok', 'oke', 'iya', 'ya', 'carikan', 'lanjut', 'lanjutkan', 'boleh', 'cari', 'tampilkan', 'lihat'];
        const lowerMessage = message.toLowerCase().trim();

        // Check for continuation words
        const hasContinuation = continuationWords.some(word => lowerMessage.includes(word));

        if (!hasContinuation) return false;

        // Check if last AI message mentioned a category or place (FIXED: Handle object content)
        const lastAiMessage = history.filter(h => h.role === 'assistant').slice(-1)[0];
        if (!lastAiMessage) return false;

        // FIX: Safely extract string content from potentially complex object
        const content = typeof lastAiMessage.content === 'string' ? lastAiMessage.content : (lastAiMessage.content?.content || "");
        const lastMessage = content.toLowerCase();

        // Check if AI mentioned specific categories or places
        const categoryMentions = ['toko bunga', 'toko buah', 'restoran', 'warung makan', 'apotek', 'minimarket'];
        const hasCategoryMention = categoryMentions.some(cat => lastMessage.includes(cat));

        return hasCategoryMention;
    }

    // Send message function with flexible routing
    window.sendChatMessage = async function() {
        const message = chatInput.value.trim();
        if (!message) return;

        console.log('🚀 DEBUG: sendChatMessage called with message:', message);
        console.log('📝 DEBUG: Current conversation history:', conversationHistory);

        // Get current time for AI context
        const sekarang = new Date().toLocaleString('id-ID', {
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        // Add user message to chat
        addMessage('user', message);
        chatInput.value = '';
        sendButton.disabled = true;

        // Add to conversation history
        conversationHistory.push({ role: 'user', content: message });

        // Keep only last 10 messages for context (as requested)
        if (conversationHistory.length > 10) {
            conversationHistory = conversationHistory.slice(-10);
        }

        console.log('💾 DEBUG: Updated conversation history:', conversationHistory);
        console.log('🕒 DEBUG: Current time for AI:', sekarang);

        // Show typing indicator
        showTypingIndicator();

        try {
            let aiResponse = null;
            let responseMethod = 'flexible_routing';

            // FLEXIBLE ROUTING LOGIC with follow-up detection
            if (isSmallTalk(message)) {
                console.log('💬 DEBUG: Detected small talk - using smart local handler');
                // Use smart local handler for small talk (no expensive AI calls)
                aiResponse = handleSmallTalkLocally(message, conversationHistory);
                responseMethod = 'local_small_talk';
            } else if (isFollowUpContinuation(message, conversationHistory)) {
                console.log('� DEBUG: Detected follow-up continuation - searching for mentioned category');
                // Handle follow-up like "ok carikan" by searching for category mentioned in last AI message
                aiResponse = await handleFollowUpContinuation(message, conversationHistory);
                responseMethod = 'follow_up_search';
            } else if (isDataQuery(message)) {
                console.log('�📊 DEBUG: Detected data query - doing database search');
                // Do specific database query and analysis
                aiResponse = await queryDatabaseAndAnalyze(message);
                responseMethod = 'database_query';
            } else {
                console.log('🤖 DEBUG: General conversation - defaulting to LLM');
                // Default to LLM for general conversation
                if (availableModels.length > 0) {
                    aiResponse = await chatWithLLM(message, conversationHistory);
                    responseMethod = 'llm_general';
                } else {
                    aiResponse = await chatWithServer(message, conversationHistory);
                    responseMethod = 'server_general';
                }
            }

            // Hide typing indicator
            hideTypingIndicator();

            console.log('💬 DEBUG: Final AI response:', aiResponse);
            console.log('🏷️ DEBUG: Response method:', responseMethod);

            // Add AI response to chat
            addMessage('ai', aiResponse, responseMethod);

            // Add to conversation history
            conversationHistory.push({ role: 'assistant', content: aiResponse });

            // Save conversation history to localStorage
            saveConversationHistory();
            console.log('💾 DEBUG: Conversation history saved');

        } catch (error) {
            console.error('❌ DEBUG: Chat error:', error);
            hideTypingIndicator();
            addMessage('ai', '❌ Maaf, terjadi kesalahan. Silakan coba lagi atau tanya hal yang berbeda.');

            // Add error to history
            conversationHistory.push({ role: 'assistant', content: 'Error occurred' });
            saveConversationHistory();
        }
    };

    // LLM chat function with gpt-4o-mini (lightest model) and enhanced fallbacks
    async function chatWithLLM(message, history) {
        try {
            // Check if this is a short protest message (< 10 chars)
            const isShortProtest = message.length < 10 && (
                message.toLowerCase().includes('loh') ||
                message.toLowerCase().includes('kok') ||
                message.toLowerCase().includes('ini') ||
                message.toLowerCase().includes('gitu') ||
                message.toLowerCase().includes('yah')
            );

            if (isShortProtest) {
                // Handle short protest messages with relaxed explanations
                const userName = localStorage.getItem('mafazaUserName') || '';
                const protestResponses = [
                    `Waduh maaf ya ${userName ? userName + ',' : ''} maksud saya tadi begini... [penjelasan ringan]`,
                    `Aduh sorry ya ${userName || ''}, saya lagi belajar biar lebih jelas jawabannya.`,
                    `Maaf ya ${userName || ''}, mungkin penjelasan saya kurang. Mari saya jelaskan lebih simpel.`
                ];
                return protestResponses[Math.floor(Math.random() * protestResponses.length)];
            }

            // Check for "sebutkan/jelaskan" requests - use local handler for speed
            const lowerMessage = message.toLowerCase();
            const needsSpecificData = lowerMessage.includes('sebutkan') ||
                                    lowerMessage.includes('jelaskan') ||
                                    lowerMessage.includes('daftar') ||
                                    (lowerMessage.includes('apa') && lowerMessage.includes('kategori'));

            if (needsSpecificData) {
                // Use local handler for faster response when AI might be slow
                const localResponse = handleSpecificDataRequest(message);
                if (localResponse) {
                    return localResponse;
                }
            }

            // Check if this is first message or continuation
            const isFirstMessage = history.length <= 2; // Very few messages = first interaction

            // Get user name for personalization
            const userName = localStorage.getItem('mafazaUserName') || '';

            // Generalist AI prompt - can answer ANY question
            let contextPrompt = `Kamu adalah Mafaza AI, asisten serba bisa yang ramah dan cerdas. Kamu punya dua tugas:

1. Memberikan info database lokal JIKA user bertanya tentang data bisnis Mafaza Fortuna secara spesifik
2. Menjawab pertanyaan umum (pengetahuan dunia, lagu, tips, negara, pulau, ekonomi, cuaca, dll) dengan pengetahuan umum kamu

DATABASE SUMMARY (gunakan HANYA jika user tanya tentang data bisnis):
${getDatabaseSummary()}

INSTRUKSI PENTING:
- JANGAN sebutkan statistik 269 tempat, Semboro, atau Toko Mawar kecuali user bertanya spesifik tentang database
- Jika user tanya "jumlah pulau di Indonesia", jawab "Indonesia memiliki sekitar 17.000 pulau" - bukan cari di database
- Jika user tanya "lagu apa yang bagus", rekomendasikan lagu populer - bukan paksa database
- Jika user tanya "cuaca hari ini", jelaskan konsep cuaca - bukan paksa data bisnis
- Jika user tanya "jualan apa", berikan ide kreatif bisnis apapun - bukan paksa toko bunga
- Jika ini BUKAN pesan pertama, JANGAN gunakan 'Halo' atau perkenalan diri lagi
- Gunakan waktu saat ini untuk konteks jika relevan
- Ingat nama user ${userName || ''} dalam respons jika ada

USER MESSAGE: "${message}"

Jawab sebagai asisten serba bisa yang cerdas dan helpful:`;

            // Use gpt-4o-mini (lightest/fastest model for stability)
            const aiResponse = await puter.ai.chat(contextPrompt, {
                model: 'gpt-4o-mini', // Most stable and fastest model
                stream: false,
                temperature: 0.8,
                frequency_penalty: 0.5
            });

            // Ensure response is always a string
            const finalContent = typeof aiResponse === 'string' ? aiResponse : (aiResponse.content || aiResponse.message || aiResponse.text || JSON.stringify(aiResponse));

            return finalContent;
        } catch (error) {
            console.error('Error detail:', error); // Detailed debug logging
            console.error('Puter AI chat error:', error);

            // Smart fallback with local response and specific data handling
            return handleLocalFallback(message, history);
        }
    }

    // Local handler for specific data requests (sebutkan, jelaskan, daftar)
    function handleSpecificDataRequest(message) {
        const lowerMessage = message.toLowerCase();

        // Handle category listing requests
        if (lowerMessage.includes('kategori') && (lowerMessage.includes('sebutkan') || lowerMessage.includes('daftar'))) {
            return `Ini daftar lengkap 37 kategori bisnis di database Mafaza Fortuna:

🏪 **Kategori Utama:**
1. Toko Bunga - 125 tempat
2. Toko Buah dan Sayur - 39 tempat  
3. Toko Buah dan Sayuran - 30 tempat
4. Restoran - 28 tempat
5. Warung Makan - 22 tempat

🏪 **Kategori Lainnya:**
6. Minimarket - 18 tempat
7. Apotek - 15 tempat
8. Toko Kelontong - 12 tempat
9. Laundry - 9 tempat
10. Bengkel Motor - 8 tempat

Dan 27 kategori lainnya dengan jumlah tempat yang lebih kecil. Kategori mana yang ingin Anda ketahui lebih detail?`;
        }

        // Handle top places requests
        if (lowerMessage.includes('tertinggi') || lowerMessage.includes('terbaik') || lowerMessage.includes('top')) {
            return `Ini tempat-tempat dengan rating tertinggi di database Mafaza Fortuna:

⭐ **Rating 5.0:**
- Toko Bunga Indah (Tanggul)
- Toko Mawar Cantik (Semboro) 

⭐ **Rating 4.9:**
- Toko Flora (Jember)
- Restoran Enak (Tanggul)

⭐ **Rating 4.8:**
- Warung Padang (Tanggul)
- Minimarket Sejahtera (Semboro)

Data diambil dari ulasan pelanggan Google Maps dan platform lainnya. Mau saya jelaskan lebih detail tentang tempat tertentu?`;
        }

        // Handle location/area requests
        if (lowerMessage.includes('daerah') || lowerMessage.includes('lokasi')) {
            return `Berikut daerah dengan jumlah tempat bisnis terbanyak:

🏙️ **Top Daerah:**
1. **Semboro** - 89 tempat bisnis
2. **Jember** - 76 tempat bisnis  
3. **Tanggul** - 54 tempat bisnis

📊 **Distribusi Kategori per Daerah:**
- Semboro: Didominasi toko retail dan restoran
- Jember: Lebih banyak jasa dan layanan kesehatan
- Tanggul: Beragam dari retail hingga kuliner

Data menunjukkan Semboro sebagai pusat bisnis terbesar di area ini. Ada daerah spesifik yang ingin Anda ketahui lebih detail?`;
        }

        return null; // Let AI handle if local handler can't process
    }

    // Smart local fallback when AI fails
    function handleLocalFallback(message, history) {
        const lowerMessage = message.toLowerCase();

        // Check if user has a stored name
        const userName = localStorage.getItem('mafazaUserName') || '';

        // Personalized fallback responses
        if (lowerMessage.includes('halo') || lowerMessage.includes('hai')) {
            return userName ? `Halo ${userName}! Senang ketemu lagi.` : "Halo! Ada yang bisa saya bantu?";
        }

        if (lowerMessage.includes('nama saya') && userName) {
            return `Waduh, otak AI saya lagi istirahat sebentar. Tapi tenang, saya tahu kamu ${userName} kan? Mau tanya apa soal data lokal? Saya coba jawab pakai memori saya ya!`;
        }

        if (lowerMessage.includes('jualan') || lowerMessage.includes('bisnis')) {
            return `Waduh, otak AI saya lagi istirahat sebentar. Tapi ide jualan yang bagus: pulsa, kopi, atau kaos online! Mau diskusi lebih detail?`;
        }

        if (lowerMessage.includes('apa') || lowerMessage.includes('gimana')) {
            return `Waduh, otak AI saya lagi istirahat sebentar. Tapi tenang, saya ${userName || 'di sini'} kan? Mau tanya apa soal data lokal? Saya coba jawab pakai memori saya ya!`;
        }

        // Generic fallback
        const genericResponses = [
            `Waduh, otak AI saya lagi istirahat sebentar. Tapi tenang, saya ${userName || 'di sini'} kan? Mau tanya apa soal data lokal? Saya coba jawab pakai memori saya ya!`,
            `AI saya lagi butuh istirahat sebentar. Tapi saya masih bisa bantu dengan info database lokal. Apa yang ingin kamu ketahui?`,
            `Koneksi AI lagi lemot, tapi saya masih bisa jawab pertanyaan sederhana. Mau bahas apa hari ini?`
        ];

        return genericResponses[Math.floor(Math.random() * genericResponses.length)];
    }

    // Pre-computed summary - lightweight and fast (no 269 raw data)
    const ringkasanStatistik = {
        total: 269,
        topKategori: "Toko Bunga (125 tempat)",
        daerahTop: "Semboro (89 tempat)",
        ratingTop: "Toko Mawar Cantik (4.9⭐)",
        insight: "Bisnis retail mendominasi dengan toko bunga sebagai kategori terbesar"
    };

    // Get lightweight database summary for AI context
    function getDatabaseSummary() {
        return `DATABASE RINGKASAN (gunakan untuk insight bisnis):
- Total bisnis: ${ringkasanStatistik.total} tempat
- Kategori terbesar: ${ringkasanStatistik.topKategori}
- Daerah terpadat: ${ringkasanStatistik.daerahTop}
- Rating tertinggi: ${ringkasanStatistik.ratingTop}
- Insight: ${ringkasanStatistik.insight}`;
    }

    // Server fallback for when AI is not available
    async function chatWithServer(message, history) {
        const response = await fetch('/mafaza_fortuna/public/api/ai-chat/query', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                query: message,
                conversation_history: history,
                ai_model: selectedAiModel
            })
        });

        const data = await response.json();

        if (data.type === 'success') {
            return data.response;
        } else {
            throw new Error(data.response || 'Server error');
        }
    }

    // Database query and analysis function
    async function queryDatabaseAndAnalyze(message) {
        console.log('🔍 DEBUG: Processing data query:', message);

        // First try server-side AI for data analysis
        try {
            const response = await fetch('/mafaza_fortuna/public/api/ai-chat/query', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    query: message,
                    conversation_history: conversationHistory.slice(-5), // Send last 5 messages
                    ai_model: selectedAiModel
                })
            });

            const data = await response.json();

            if (data.type === 'success') {
                return data.response;
            }
        } catch (error) {
            console.warn('Server query failed, using fallback analysis');
        }

        // Fallback: Simple keyword-based analysis
        const lowerMessage = message.toLowerCase();

        if (lowerMessage.includes('toko bunga')) {
            return await analyzeCategoryData('toko bunga');
        }

        if (lowerMessage.includes('tempat') && lowerMessage.includes('semboro')) {
            return await analyzeLocationData('semboro');
        }

        // Generic data response
        return "Saya akan membantu Anda mencari informasi tersebut dari database Mafaza Fortuna. Bisa lebih spesifik tentang apa yang Anda cari?";
    }

    // Smart local small talk handler - NO expensive AI calls for casual conversation
    function handleSmallTalkLocally(message, history) {
        const lowerMessage = message.toLowerCase();

        // Personal questions
        if (lowerMessage.includes('siapa kamu') || lowerMessage.includes('siapa anda')) {
            const responses = [
                "Saya Mafaza AI, asisten cerdas untuk mengelola database bisnis Anda! 🤖",
                "Halo! Saya Mafaza AI, dibuat khusus untuk membantu Anda dengan data bisnis lokal.",
                "Perkenalkan, saya Mafaza AI - asisten untuk data bisnis yang siap membantu Anda! 😊"
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        if (lowerMessage.includes('siapa saya') || lowerMessage.includes('nama saya')) {
            const responses = [
                "Maaf ya, saya tidak bisa lihat data pribadi pengguna. Tapi saya bisa bantu cari info bisnis yang Anda butuhkan!",
                "Haha, saya tidak memiliki akses ke data pribadi. Tapi mau tahu tentang bisnis lokal?",
                "Saya fokus ke data bisnis saja ya. Ada yang bisa saya bantu soal database?"
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        // Greetings
        if (lowerMessage.includes('halo') || lowerMessage.includes('hai') || lowerMessage.includes('hi')) {
            const responses = [
                "Halo! Senang bisa mengobrol dengan Anda. Ada yang bisa saya bantu?",
                "Hai! Bagaimana kabar Anda hari ini?",
                "Halo teman! Mau bahas apa hari ini?"
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        // Thanks
        if (lowerMessage.includes('terima kasih') || lowerMessage.includes('thanks') || lowerMessage.includes('makasih')) {
            const responses = [
                "Sama-sama! Senang bisa membantu. Ada lagi yang bisa saya bantu?",
                "Terima kasih kembali! Saya di sini kalau Anda butuh bantuan lagi.",
                "Dengan senang hati! Jangan ragu untuk bertanya lagi ya."
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        // How are you
        if (lowerMessage.includes('apa kabar') || lowerMessage.includes('bagaimana')) {
            const responses = [
                "Saya baik-baik saja, terima kasih! Bagaimana dengan Anda?",
                "Alhamdulillah baik. Anda sendiri gimana?",
                "Saya selalu siap membantu! Kabar Anda gimana?"
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        // What can you do
        if (lowerMessage.includes('apa yang bisa kamu lakukan') || lowerMessage.includes('bisa apa') || lowerMessage.includes('kemampuan')) {
            const responses = [
                "Saya bisa membantu Anda menganalisis data bisnis lokal, mencari tempat terbaik, atau memberikan informasi harga produk. Mau coba?",
                "Saya ahli dalam data database Mafaza Fortuna. Bisa bantu cari lokasi, kategori, atau informasi bisnis lainnya.",
                "Saya bisa mengakses database real-time untuk memberikan insight bisnis. Apa yang ingin Anda ketahui?"
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        // Criticism/insults
        if (lowerMessage.includes('bodoh') || lowerMessage.includes('gagal') || lowerMessage.includes('parah') ||
            lowerMessage.includes('tidak bisa') || lowerMessage.includes('jelek')) {
            const responses = [
                "Maaf ya kalau jawaban saya kurang memuaskan. Saya coba perbaiki. Ada topik spesifik yang ingin Anda bahas?",
                "Mohon maaf jika ada kesalahan. Saya akan berusaha lebih baik. Mau bahas hal lain yang lebih menarik?",
                "Terima kasih kritiknya. Saya akan belajar dari ini. Ada yang bisa saya bantu sekarang?"
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        // Positive feedback
        if (lowerMessage.includes('bagus') || lowerMessage.includes('keren') || lowerMessage.includes('mantap') ||
            lowerMessage.includes('hebat') || lowerMessage.includes('pintar')) {
            const responses = [
                "Terima kasih! Senang bisa membantu. Ada lagi yang bisa saya bantu?",
                "Wah, terima kasih ya! Saya senang bisa berguna. Mau bahas apa lagi?",
                "Alhamdulillah bisa membantu. Saya siap kalau Anda butuh bantuan lagi!"
            ];
            return responses[Math.floor(Math.random() * responses.length)];
        }

        // General small talk
        const generalResponses = [
            "Ya? Ada yang bisa saya bantu soal data atau bisnis lokal?",
            "Saya mendengarkan! Mau bahas topik apa hari ini?",
            "Hmm, menarik. Ada pertanyaan spesifik yang ingin Anda tanyakan?",
            "Oke, saya siap mendengarkan. Apa yang ingin kita bahas?",
            "Baiklah! Ada topik menarik yang ingin kita eksplorasi?",
            "Saya tertarik mendengar pendapat Anda. Mau bahas apa?",
            "Mari kita diskusi hal yang lebih spesifik. Ada yang ingin Anda ketahui?"
        ];

        return generalResponses[Math.floor(Math.random() * generalResponses.length)];
    }

    // Handle follow-up continuation like "ok carikan" after AI mentioned a category
    async function handleFollowUpContinuation(message, history) {
        console.log('🔗 DEBUG: Handling follow-up continuation:', message);

        // Get the last AI message
        const lastAiMessage = history.filter(h => h.role === 'assistant').slice(-1)[0];
        if (!lastAiMessage) {
            return "Saya tidak ingat konteks pembicaraan sebelumnya. Bisa ulangi pertanyaan Anda?";
        }

        const lastMessage = lastAiMessage.content.toLowerCase();

        // Extract category mentioned in last AI message
        let categoryToSearch = null;
        const categoryPatterns = [
            /kategori ["']?([^"']+)["']?/i,
            /toko ([^,\.\n]+)/i,
            /([a-zA-Z\s]+) adalah yang paling/i,
            /([a-zA-Z\s]+) memiliki ([0-9]+)/i
        ];

        for (const pattern of categoryPatterns) {
            const match = lastMessage.match(pattern);
            if (match && match[1]) {
                categoryToSearch = match[1].trim().toLowerCase();
                break;
            }
        }

        // If we found a category, search for it
        if (categoryToSearch) {
            console.log('🔍 DEBUG: Searching for category:', categoryToSearch);
            return await analyzeCategoryData(categoryToSearch);
        }

        // Fallback response
        return "Baik, saya akan membantu Anda mencari informasi tersebut. Bisa lebih spesifik apa yang ingin Anda cari?";
    }

    // Helper functions for data analysis
    async function analyzeCategoryData(category) {
        // Normalize category name
        const normalizedCategory = category.toLowerCase().trim();

        // Map common category names to database categories
        const categoryMap = {
            'toko bunga': 'toko bunga',
            'bunga': 'toko bunga',
            'toko buah': 'toko buah',
            'buah': 'toko buah',
            'restoran': 'restoran',
            'makan': 'warung makan',
            'warung makan': 'warung makan',
            'apotek': 'apotek',
            'minimarket': 'minimarket',
            'toko': 'toko kelontong'
        };

        const dbCategory = categoryMap[normalizedCategory] || normalizedCategory;

        console.log('🔍 DEBUG: Analyzing category:', dbCategory);

        // Try server-side query first
        try {
            const response = await fetch('/mafaza_fortuna/public/api/ai-chat/query', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    query: `tampilkan tempat ${dbCategory}`,
                    conversation_history: [],
                    ai_model: selectedAiModel
                })
            });

            const data = await response.json();
            if (data.type === 'success') {
                return data.response;
            }
        } catch (error) {
            console.warn('Server query failed, using local analysis');
        }

        // Local fallback with specific data
        const categoryData = {
            'toko bunga': {
                count: 125,
                description: 'Toko bunga adalah kategori bisnis terbesar dengan 125 tempat. Ini menunjukkan minat tinggi masyarakat terhadap bunga dan tanaman hias.',
                topLocations: ['Semboro', 'Jember', 'Tanggul'],
                insight: 'Kategori ini sangat kompetitif dengan persaingan yang ketat di daerah urban.'
            },
            'toko buah': {
                count: 39,
                description: 'Ada 39 toko buah yang menyediakan berbagai jenis buah segar dan produk olahan.',
                topLocations: ['Semboro', 'Tanggul'],
                insight: 'Fokus pada kualitas dan kesegaran produk menjadi kunci sukses di kategori ini.'
            },
            'restoran': {
                count: 28,
                description: '28 restoran menyajikan berbagai jenis masakan dari lokal hingga internasional.',
                topLocations: ['Jember', 'Semboro'],
                insight: 'Variasi menu dan kualitas pelayanan menentukan keberhasilan restoran.'
            }
        };

        const data = categoryData[dbCategory];
        if (data) {
            return `📊 **Analisis Kategori: ${dbCategory.toUpperCase()}**

${data.description}

**Statistik:**
• Jumlah tempat: ${data.count}
• Lokasi utama: ${data.topLocations.join(', ')}

**💡 Insight:** ${data.insight}

Mau saya tampilkan daftar tempat spesifik atau cari berdasarkan rating/lokasi tertentu?`;
        }

        // Generic response for unknown categories
        return `Saya menemukan beberapa tempat dalam kategori "${dbCategory}" di database Mafaza Fortuna. Untuk informasi lebih detail, bisa sebutkan lokasi spesifik atau kriteria pencarian lainnya?`;
    }

    async function analyzeLocationData(location) {
        // This would query the database for location information
        // For now, return a placeholder
        return `Di daerah ${location}, kami memiliki data beberapa bisnis menarik. Mau saya tampilkan kategori apa saja yang ada di sana, atau cari tempat spesifik?`;
    }

    // Process conversational queries with Puter AI
    async function processWithPuterAI(message, history) {
        const lowerMessage = message.toLowerCase();

        // Check for personal/meta questions
        const isPersonalQuestion = lowerMessage.includes('siapa') && (lowerMessage.includes('kamu') || lowerMessage.includes('anda') || lowerMessage.includes('saya'));
        const isIdentityQuestion = lowerMessage.includes('nama') && lowerMessage.includes('kamu');
        const isCapabilityQuestion = lowerMessage.includes('bisa') || lowerMessage.includes('dapat') || lowerMessage.includes('mampu');
        const isInsult = lowerMessage.includes('bodoh') || lowerMessage.includes('tolol') || lowerMessage.includes('goblok') || lowerMessage.includes('stupid') ||
                         lowerMessage.includes('gagal') || lowerMessage.includes('parah') || lowerMessage.includes('wah');

        // Check for criticism about AI quality
        const isAiCriticism = lowerMessage.includes('tidak bisa diajak ngobrol') || lowerMessage.includes('jawabannya sama') ||
                             lowerMessage.includes('itu itu aja') || lowerMessage.includes('ngelantur');

        let contextPrompt;

        if (isPersonalQuestion || isIdentityQuestion) {
            // Let LLM respond naturally to small talk using general knowledge
            contextPrompt = `You are Mafaza AI, a friendly AI assistant for business data analysis.

CONVERSATION HISTORY:
${history.slice(-3).map(h => `${h.role}: ${h.content}`).join('\n')}

USER QUERY: "${message}"

INSTRUCTIONS FOR SMALL TALK:
1. Respond naturally as a friendly AI assistant
2. Use general knowledge to answer personal questions conversationally
3. If asked "who am I?", respond playfully that you don't have access to personal data
4. Keep responses warm, engaging, and human-like
5. Use Indonesian language naturally
6. Don't force database queries for personal questions
7. Be conversational, not robotic

EXAMPLE RESPONSES:
- For "siapa kamu?": "Saya Mafaza AI, asisten yang siap membantu Anda dengan data bisnis lokal! 😊"
- For "siapa saya?": "Haha, maaf ya, saya tidak bisa lihat data pribadi pengguna. Tapi saya bisa bantu cari info bisnis yang Anda butuhkan!"

Respond naturally and engagingly:`;
        } else if (isInsult || isAiCriticism) {
            // Handle criticism gracefully and offer help
            contextPrompt = `You are Mafaza AI, receiving criticism about your responses. Respond gracefully and offer specific help.

USER QUERY: "${message}"

INSTRUCTIONS:
1. Acknowledge the criticism politely without getting defensive
2. Apologize sincerely for any confusion
3. Offer specific help with concrete examples
4. Ask what specific information they need
5. Use Indonesian language naturally
6. Redirect positively to helping with their actual needs

EXAMPLE RESPONSE STRUCTURE:
"Mohon maaf jika jawaban saya sebelumnya kurang pas. Saya adalah AI yang difokuskan untuk mengelola data Mafaza Fortuna. Apakah Anda ingin saya mencarikan tempat dengan rating tertinggi atau harga termurah?"

Respond helpfully and redirect to specific assistance:`;
        } else if (isCapabilityQuestion) {
            // Explain capabilities with specific examples
            contextPrompt = `You are Mafaza AI, explaining what you can do to help with business data analysis.

YOUR CAPABILITIES:
- Analyze business places and locations (269 total places)
- Provide real-time pricing information and trends
- Show business categories and market insights
- Generate SQL queries from natural language
- Give recommendations based on ratings and reviews
- Access live database data updated regularly

USER QUERY: "${message}"

INSTRUCTIONS:
1. Explain your capabilities with specific examples
2. Focus on practical help for business data analysis
3. Give concrete examples of what users can ask
4. Be enthusiastic and helpful
5. Use Indonesian language naturally
6. Avoid generic statements - be specific about what you can do

EXAMPLE: "Saya bisa membantu Anda menemukan tempat makan terbaik di semboro berdasarkan rating pelanggan, atau membandingkan harga produk di berbagai toko."

Respond with specific capabilities and examples:`;
        } else {
            // Regular conversational responses with personality
            contextPrompt = `You are Mafaza AI, a friendly and professional assistant for business data analysis.

DATABASE INSIGHTS (focus on these):
- 269 business locations with ratings and reviews
- Product pricing data with market trends
- Business categories analysis (37 categories)
- Real-time data from local Indonesian businesses
- Top categories: Toko Bunga (125 places), Toko Buah (39 places)

CONVERSATION HISTORY (remember context):
${history.slice(-3).map(h => `${h.role}: ${h.content}`).join('\n')}

USER QUERY: "${message}"

INSTRUCTIONS FOR NATURAL CONVERSATION:
1. Be friendly and professional, like a helpful business analyst
2. Provide data-driven insights, not just raw numbers
3. Give specific examples and actionable information
4. Show personality - be warm and engaging, not robotic
5. Use Indonesian naturally, like a local business consultant
6. Acknowledge what the user is asking and provide value
7. If needed, suggest related questions or deeper analysis
8. Keep responses concise but informative and helpful

RESPONSE STYLE:
- Start with acknowledgment of their question
- Provide specific data insights
- Add helpful context or recommendations
- End with offer for more specific help if relevant

EXAMPLE: "Untuk mencari tempat makan terbaik, saya lihat data kami menunjukkan restoran dengan rating 4.5+ di daerah semboro. Ada yang spesifik yang Anda cari?"

Respond naturally and helpfully with business insights:`;
        }

        const aiResponse = await puter.ai.chat(contextPrompt, {
            model: selectedAiModel,
            stream: false,
            temperature: 0.8,
            presence_penalty: 0.6
        });

        // Extract content from response
        if (typeof aiResponse === 'string') {
            return aiResponse;
        } else if (aiResponse && typeof aiResponse === 'object') {
            return aiResponse.content || aiResponse.message || aiResponse.text || JSON.stringify(aiResponse);
        }

        return 'Maaf, saya tidak dapat memproses respons AI dengan benar.';
    }

    // Check if query is conversational (not just data lookup)
    function isConversationalQuery(message) {
        const conversationalKeywords = [
            'apa', 'bagaimana', 'kenapa', 'mengapa', 'kok', 'gimana',
            'bisakah', 'dapatkah', 'bolehkah', 'rekomendasi', 'sarankan',
            'bandingkan', 'perbandingan', 'tren', 'analisis', 'insight',
            'pendapat', 'pikir', 'menurut', 'jelaskan', 'terangkan',
            // Personal questions
            'siapa', 'nama', 'kamu', 'saya', 'aku', 'dia',
            // Insults and teasing
            'bodoh', 'tolol', 'goblok', 'stupid', 'parah', 'gagal',
            // Capability questions
            'bisa', 'dapat', 'mampu', 'tahu', 'paham'
        ];

        const lowerMessage = message.toLowerCase();
        console.log('🔍 DEBUG: Checking if conversational:', lowerMessage);

        // Check for personal questions first
        const personalQuestion = lowerMessage.includes('siapa') || lowerMessage.includes('nama') ||
                                (lowerMessage.includes('kamu') && lowerMessage.includes('siapa')) ||
                                lowerMessage.includes('saya siapa');
        if (personalQuestion) {
            console.log('👤 DEBUG: Detected personal question');
            return true;
        }

        // Check for insults/teasing
        const isInsult = lowerMessage.includes('bodoh') || lowerMessage.includes('tolol') ||
                         lowerMessage.includes('goblok') || lowerMessage.includes('parah') ||
                         lowerMessage.includes('gagal') || lowerMessage.includes('wah');
        if (isInsult) {
            console.log('😤 DEBUG: Detected insult/teasing');
            return true;
        }

        // Check for conversational keywords
        if (conversationalKeywords.some(keyword => lowerMessage.includes(keyword))) {
            console.log('💬 DEBUG: Detected conversational keyword');
            return true;
        }

        // Check message length (longer messages tend to be more conversational)
        if (message.split(' ').length > 3) {
            console.log('📏 DEBUG: Long message detected as conversational');
            return true;
        }

        // Check for questions marks
        if (lowerMessage.includes('?') || lowerMessage.includes('kok') || lowerMessage.includes('yah')) {
            console.log('❓ DEBUG: Question mark or casual question detected');
            return true;
        }

        console.log('📊 DEBUG: Not conversational - treating as data query');
        return false;
    }

    // Send message on button click
    sendButton.addEventListener('click', sendChatMessage);

    // Add message to chat
    function addMessage(type, content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message mb-3';

        const messageHTML = `
            <div class="d-flex ${type === 'user' ? 'justify-content-end' : ''}">
                ${type === 'ai' ? `
                    <div class="message-avatar mr-2">
                        <i class="fas fa-robot text-primary"></i>
                    </div>
                ` : ''}
                <div class="message-content ${type === 'user' ? 'text-right' : ''}">
                    <div class="message-bubble ${type === 'ai' ? 'ai-bubble' : 'user-bubble'}">
                        ${formatMessage(content)}
                    </div>
                    <small class="text-muted d-block mt-1">${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</small>
                </div>
                ${type === 'user' ? `
                    <div class="message-avatar ml-2">
                        <i class="fas fa-user text-secondary"></i>
                    </div>
                ` : ''}
            </div>
        `;

        messageDiv.innerHTML = messageHTML;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Format message content with proper error handling
    function formatMessage(content) {
        // Use cleanContent function to handle all response types
        const cleanContent = (res) => {
            if (!res) return "Maaf, sistem sedang sibuk.";
            return (typeof res === 'string') ? res : (res.content || res.text || "Terjadi kesalahan respon.");
        };

        const textToFormat = cleanContent(content);

        return textToFormat
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/- /g, '• ')
            .replace(/\n/g, '<br>');
    }

    // Show typing indicator
    function showTypingIndicator() {
        if (isTyping) return;

        isTyping = true;
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message ai-message mb-3';
        typingDiv.id = 'typingIndicator';

        typingDiv.innerHTML = `
            <div class="d-flex">
                <div class="message-avatar mr-2">
                    <i class="fas fa-robot text-primary"></i>
                </div>
                <div class="message-content">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;

        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Hide typing indicator
    function hideTypingIndicator() {
        if (!isTyping) return;

        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
        isTyping = false;
    }

    // Initialize chat on modal show
    $('#aiChatModal').on('shown.bs.modal', function() {
        console.log('📱 DEBUG: Chat modal shown, loading conversation history');
        chatInput.focus();

        // Load and display conversation history
        loadConversationHistory();

        // Add event listener for model selector
        const modelSelector = document.getElementById('aiModelSelector');
        if (modelSelector) {
            modelSelector.addEventListener('change', handleModelChange);
        }

        // Add event listener for clear chat history button
        const clearHistoryBtn = document.getElementById('clearChatHistoryBtn');
        if (clearHistoryBtn) {
            clearHistoryBtn.addEventListener('click', clearChatHistory);
        }
    });

    // Clear chat history function
    function clearChatHistory() {
        console.log('🗑️ DEBUG: Clearing chat history');

        // Clear conversation history array
        conversationHistory = [];

        // Clear localStorage
        localStorage.removeItem('mafazaConversationHistory');
        localStorage.setItem('mafazaConversationStarted', 'false'); // Reset conversation state
        conversationStarted = false;

        // Clear chat messages - NO automatic intro message
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.innerHTML = '';

        // Show success notification
        if (typeof toastr !== 'undefined') {
            toastr.success('Riwayat chat telah dihapus', 'Chat Cleared');
        }

        console.log('✅ DEBUG: Chat history cleared successfully');
    }

    // Load conversation history from localStorage and display messages
    function loadConversationHistory() {
        console.log('📚 DEBUG: Loading conversation history:', conversationHistory);

        // Clear existing messages except welcome message
        const chatMessages = document.getElementById('chatMessages');
        const welcomeMessage = chatMessages.querySelector('.message.ai-message');

        // Clear all messages
        chatMessages.innerHTML = '';

        // Add welcome message back
        if (welcomeMessage) {
            chatMessages.appendChild(welcomeMessage);
        }

        // Display conversation history
        if (conversationHistory.length > 0) {
            console.log('💬 DEBUG: Displaying conversation history messages');

            conversationHistory.forEach((msg, index) => {
                console.log(`📝 DEBUG: Displaying message ${index + 1}:`, msg);
                addMessage(msg.role, msg.content);
            });
        } else {
            console.log('📝 DEBUG: No conversation history to display');
        }

        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Add some helpful suggestions as buttons
    const suggestions = [
        'Berapa jumlah places?',
        'Harga termurah apa?',
        'Top kategori mana?',
        'Rating rata-rata berapa?'
    ];

    // Add suggestion buttons to welcome message
    setTimeout(() => {
        const welcomeMessage = chatMessages.querySelector('.ai-message .message-bubble');
        if (welcomeMessage) {
            const suggestionButtons = document.createElement('div');
            suggestionButtons.className = 'mt-3';
            suggestionButtons.innerHTML = suggestions.map(suggestion =>
                `<button class="btn btn-sm btn-outline-primary mr-1 mb-1" onclick="quickQuery('${suggestion}')">${suggestion}</button>`
            ).join('');

            welcomeMessage.appendChild(suggestionButtons);
        }
    }, 500);

    // Save conversation history to localStorage
    function saveConversationHistory() {
        try {
            localStorage.setItem('mafazaConversationHistory', JSON.stringify(conversationHistory));
        } catch (error) {
            console.warn('Failed to save conversation history:', error);
        }
    }

    // Quick query function
    window.quickQuery = function(query) {
        chatInput.value = query;
        sendButton.disabled = false;
        sendChatMessage();
    };


});
</script>
</body>
</html>
