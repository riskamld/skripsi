@extends('layouts.app')

@section('page-title', __('messages.welcome_title'))
@section('page-subtitle', __('messages.welcome_subtitle'))

@section('content')
<!-- Welcome Hero -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h1 class="text-white mb-3">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            
                        </h1>
                        <p class="text-white-50 mb-4">
                            
                        </p>
                        <div class="d-flex flex-wrap">
                            <a href="{{ route('places.create') }}" class="btn btn-light mr-3 mb-2">
                                <i class="fas fa-plus mr-2"></i>
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-light mb-2">
                                <i class="fas fa-chart-line mr-2"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-map-marked-alt fa-5x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $placesCount ?? 0 }}</h3>
                <p></p>
            </div>
            <div class="icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <a href="{{ route('places.index') }}" class="small-box-footer">
                 <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $scrapeLogsCount ?? 0 }}</h3>
                <p></p>
            </div>
            <div class="icon">
                <i class="fas fa-history"></i>
            </div>
            <a href="{{ route('scrape-logs.index') }}" class="small-box-footer">
                 <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $apiTokensCount ?? 0 }}</h3>
                <p></p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <a href="{{ route('api-tokens.index') }}" class="small-box-footer">
                 <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>99.9<small>%</small></h3>
                <p></p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="small-box-footer">&nbsp;</div>
        </div>
    </div>
</div>

<!-- Feature Overview -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-star mr-2"></i>
                    
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-primary">
                                <i class="fas fa-map-marker-alt"></i> 
                            </span>
                            <span class="description-text"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-success">
                                <i class="fab fa-whatsapp"></i> 
                            </span>
                            <span class="description-text"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-info">
                                <i class="fas fa-history"></i> 
                            </span>
                            <span class="description-text"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-warning">
                                <i class="fas fa-key"></i> 
                            </span>
                            <span class="description-text"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-rocket fa-4x text-primary"></i>
                </div>
                <h5 class="text-center mb-3"></h5>
                <p class="text-muted text-center mb-4">
                    
                </p>
                <div class="text-center">
                    <a href="{{ route('places.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>
                        
                    </a>
                    <br><br>
                    <a href="/extension-chrome-mafaza.zip" download class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-download mr-2"></i>
                        
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-2"></i>
                    
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    <div class="time-label">
                        <span class="bg-success"></span>
                    </div>
                    <div>
                        <i class="fas fa-plus bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 2 menit yang lalu</span>
                            <h3 class="timeline-header"></h3>
                            <div class="timeline-body">
                                <strong>La Bella Vista Restaurant</strong> berhasil di-scrap dan ditambahkan ke database.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-key bg-warning"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 1 jam yang lalu</span>
                            <h3 class="timeline-header"></h3>
                            <div class="timeline-body">
                                Token API aplikasi mobile diperbarui untuk keamanan yang lebih baik.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-cog bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 3 jam yang lalu</span>
                            <h3 class="timeline-header"></h3>
                            <div class="timeline-body">
                                Pemeliharaan sistem selesai dengan sukses. Semua sistem beroperasi.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
