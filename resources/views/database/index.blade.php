@extends('layouts.app')

@section('page-title', 'Database Tools')
@section('page-subtitle', 'Export and Import Database Operations')

@section('content')
<div class="row">
    <!-- Database Information -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-database mr-2"></i>
                    Database Information
                </h3>
            </div>
            <div class="card-body">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-server"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Database Name</span>
                        <span class="info-box-number">{{ $databaseInfo['name'] }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h4>{{ number_format($databaseInfo['tables_count']) }}</h4>
                                <p>Tables</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-table"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h4>{{ number_format($databaseInfo['total_records']) }}</h4>
                                <p>Records</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-list"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Database Size:</strong>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: 100%">{{ $databaseInfo['size'] }} MB</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Export Files -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-download mr-2"></i>
                    Recent Exports
                </h3>
            </div>
            <div class="card-body p-0">
                @if(count($exportFiles) > 0)
                <ul class="list-group list-group-flush">
                    @foreach($exportFiles as $file)
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-truncate d-block" style="max-width: 150px;">
                                    <i class="fas fa-file mr-1"></i>
                                    {{ $file['name'] }}
                                </small>
                                <small class="text-muted">{{ $file['size_human'] }} • {{ \Carbon\Carbon::createFromTimestamp($file['date'])->diffForHumans() }}</small>
                            </div>
                            <div>
                                <a href="{{ route('database.download', $file['name']) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form method="POST" action="{{ route('database.delete-file', $file['name']) }}" class="d-inline" onsubmit="return confirm('Delete this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-file-download fa-2x text-muted mb-2"></i>
                    <p class="text-muted small">No export files yet</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-upload mr-2"></i>
                    Export Database
                </h3>
                <div class="card-tools">
                    <small class="text-muted">Choose format and options</small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- SQL Export -->
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-code mr-2"></i>
                                    SQL Export
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">Complete database dump with structure and data</p>
                                <form method="POST" action="{{ route('database.export.sql') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label class="small">Tables to Export:</label>
                                        <select name="tables[]" class="form-control form-control-sm" multiple size="3">
                                            <option value="">All Tables</option>
                                            @foreach($tables as $table)
                                            <option value="{{ $table['name'] }}" selected>{{ $table['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="include_data" name="include_data" checked>
                                        <label class="form-check-label small" for="include_data">Include data</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="compress_sql" name="compress">
                                        <label class="form-check-label small" for="compress_sql">Compress (ZIP)</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-download mr-1"></i>
                                        Export SQL
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- CSV Export -->
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-table mr-2"></i>
                                    CSV Export
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">Spreadsheet compatible format</p>
                                <form method="POST" action="{{ route('database.export.csv') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label class="small">Select Tables:</label>
                                        <select name="tables[]" class="form-control form-control-sm" multiple size="3" required>
                                            @foreach($tables as $table)
                                            <option value="{{ $table['name'] }}">{{ $table['name'] }} ({{ number_format($table['records']) }} records)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="compress_csv" name="compress">
                                        <label class="form-check-label small" for="compress_csv">Compress (ZIP)</label>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm btn-block">
                                        <i class="fas fa-download mr-1"></i>
                                        Export CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- JSON Export -->
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-brackets-curly mr-2"></i>
                                    JSON Export
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">API and web app format</p>
                                <form method="POST" action="{{ route('database.export.json') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label class="small">Select Tables:</label>
                                        <select name="tables[]" class="form-control form-control-sm" multiple size="3" required>
                                            @foreach($tables as $table)
                                            <option value="{{ $table['name'] }}">{{ $table['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-info btn-sm btn-block">
                                        <i class="fas fa-download mr-1"></i>
                                        Export JSON
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-download mr-2"></i>
                    Import Database
                </h3>
                <div class="card-tools">
                    <small class="text-muted">Upload and import data files</small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- SQL Import -->
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-code mr-2"></i>
                                    SQL Import
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">Import from SQL dump files</p>
                                <form method="POST" action="{{ route('database.import.sql') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label class="small">SQL File:</label>
                                        <input type="file" name="sql_file" class="form-control form-control-sm" accept=".sql,.txt" required>
                                        <small class="form-text text-muted">Max: 50MB</small>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="backup_sql" name="backup_first">
                                        <label class="form-check-label small" for="backup_sql">Create backup first</label>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-sm btn-block">
                                        <i class="fas fa-upload mr-1"></i>
                                        Import SQL
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- CSV Import -->
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-table mr-2"></i>
                                    CSV Import
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">Import from CSV files</p>
                                <form method="POST" action="{{ route('database.import.csv') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label class="small">Target Table:</label>
                                        <select name="table" class="form-control form-control-sm" required>
                                            @foreach($tables as $table)
                                            <option value="{{ $table['name'] }}">{{ $table['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="small">CSV File:</label>
                                        <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,.txt" required>
                                        <small class="form-text text-muted">Max: 50MB</small>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="has_headers" name="has_headers" checked>
                                        <label class="form-check-label small" for="has_headers">First row is headers</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="clear_table" name="clear_table">
                                        <label class="form-check-label small" for="clear_table">Clear table before import</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="backup_csv" name="backup_first">
                                        <label class="form-check-label small" for="backup_csv">Create backup first</label>
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-sm btn-block">
                                        <i class="fas fa-upload mr-1"></i>
                                        Import CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Tables -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>
                    Available Tables
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Records</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tables as $table)
                        <tr>
                            <td>
                                <strong>{{ $table['name'] }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ number_format($table['records']) }}</span>
                            </td>
                            <td>
                                <small>{{ $table['size'] }} MB</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="{{ route('database.export.csv') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="tables[]" value="{{ $table['name'] }}">
                                        <button type="submit" class="btn btn-outline-success" title="Export CSV">
                                            <i class="fas fa-file-csv"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('database.export.json') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="tables[]" value="{{ $table['name'] }}">
                                        <button type="submit" class="btn btn-outline-info" title="Export JSON">
                                            <i class="fas fa-brackets-curly"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show success/error messages
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
});
</script>
@endsection
