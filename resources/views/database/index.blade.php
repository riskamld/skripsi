@extends('layouts.app')
@section('title', 'Database — Mafaza Fortuna')
@section('page-title', 'Database')

@section('content')

@if(session('success'))
<div class="alert alert-success mb-16"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

<div class="grid grid-3 mb-16">
    <div class="metric">
        <div class="metric-icon mi-blue"><i class="fas fa-server"></i></div>
        <div class="metric-label">Nama Database</div>
        <div class="metric-value" style="font-size:16px">{{ $databaseInfo['name'] }}</div>
    </div>
    <div class="metric">
        <div class="metric-icon mi-green"><i class="fas fa-table"></i></div>
        <div class="metric-label">Tabel</div>
        <div class="metric-value">{{ number_format($databaseInfo['tables_count']) }}</div>
    </div>
    <div class="metric">
        <div class="metric-icon mi-orange"><i class="fas fa-list"></i></div>
        <div class="metric-label">Total Baris</div>
        <div class="metric-value">{{ number_format($databaseInfo['total_records']) }}</div>
    </div>
</div>

<div class="grid" style="grid-template-columns:1fr 2fr" id="grid-db">

    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-header"><i class="fas fa-hdd" style="color:var(--ac);margin-right:6px"></i>Ukuran Database</div>
            <div class="card-body">
                <div class="fw-700" style="font-size:22px">{{ $databaseInfo['size'] }} <small class="text-muted" style="font-size:13px">MB</small></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-file-download" style="color:var(--ac);margin-right:6px"></i>Export Terbaru</div>
            <div class="card-body p-0">
                @if(count($exportFiles) > 0)
                <div style="display:flex;flex-direction:column">
                    @foreach($exportFiles as $file)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 14px;{{ !$loop->last ? 'border-bottom:1px solid var(--bdr)' : '' }}">
                        <div style="min-width:0">
                            <div class="text-sm fw-600" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px"><i class="fas fa-file"></i> {{ $file['name'] }}</div>
                            <div class="text-xs text-muted">{{ $file['size_human'] }} · {{ \Carbon\Carbon::createFromTimestamp($file['date'])->diffForHumans() }}</div>
                        </div>
                        <div class="d-flex gap-4" style="flex-shrink:0">
                            <a href="{{ route('database.download', $file['name']) }}" class="btn btn-xs btn-secondary" title="Download"><i class="fas fa-download"></i></a>
                            <form method="POST" action="{{ route('database.delete-file', $file['name']) }}" onsubmit="return confirm('Hapus file ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-ghost" style="color:var(--rd)" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted text-sm" style="padding:24px"><i class="fas fa-file-download" style="font-size:22px;display:block;margin-bottom:6px"></i> Belum ada file export</div>
                @endif
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px">

        <div class="card">
            <div class="card-header"><i class="fas fa-upload" style="color:var(--ac);margin-right:6px"></i>Export Database</div>
            <div class="card-body">
                <div class="grid grid-3">
                    <div class="card" style="border-color:var(--ac)">
                        <div class="card-header" style="background:var(--ac);color:#fff"><i class="fas fa-code"></i> SQL</div>
                        <div class="card-body">
                            <div class="text-xs text-muted mb-8">Dump lengkap struktur + data</div>
                            <form method="POST" action="{{ route('database.export.sql') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Tabel:</label>
                                    <select name="tables[]" class="form-control" multiple size="3">
                                        <option value="">Semua Tabel</option>
                                        @foreach($tables as $table)
                                        <option value="{{ $table['name'] }}" selected>{{ $table['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="text-xs d-flex align-center gap-4 mb-4"><input type="checkbox" name="include_data" checked> Sertakan data</label>
                                <label class="text-xs d-flex align-center gap-4 mb-12"><input type="checkbox" name="compress"> Kompres (ZIP)</label>
                                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-download"></i> Export SQL</button>
                            </form>
                        </div>
                    </div>

                    <div class="card" style="border-color:var(--gn)">
                        <div class="card-header" style="background:var(--gn);color:#fff"><i class="fas fa-table"></i> CSV</div>
                        <div class="card-body">
                            <div class="text-xs text-muted mb-8">Format kompatibel spreadsheet</div>
                            <form method="POST" action="{{ route('database.export.csv') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Pilih Tabel:</label>
                                    <select name="tables[]" class="form-control" multiple size="3" required>
                                        @foreach($tables as $table)
                                        <option value="{{ $table['name'] }}">{{ $table['name'] }} ({{ number_format($table['records']) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="text-xs d-flex align-center gap-4 mb-12"><input type="checkbox" name="compress"> Kompres (ZIP)</label>
                                <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-download"></i> Export CSV</button>
                            </form>
                        </div>
                    </div>

                    <div class="card" style="border-color:#0891b2">
                        <div class="card-header" style="background:#0891b2;color:#fff"><i class="fas fa-brackets-curly"></i> JSON</div>
                        <div class="card-body">
                            <div class="text-xs text-muted mb-8">Format API & aplikasi web</div>
                            <form method="POST" action="{{ route('database.export.json') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Pilih Tabel:</label>
                                    <select name="tables[]" class="form-control" multiple size="3" required>
                                        @foreach($tables as $table)
                                        <option value="{{ $table['name'] }}">{{ $table['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-info btn-sm w-100" style="margin-top:36px"><i class="fas fa-download"></i> Export JSON</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-download" style="color:var(--ac);margin-right:6px"></i>Import Database</div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="card" style="border-color:var(--rd)">
                        <div class="card-header" style="background:var(--rd);color:#fff"><i class="fas fa-code"></i> Import SQL</div>
                        <div class="card-body">
                            <div class="text-xs text-muted mb-8">Import dari file dump SQL (maks 50MB)</div>
                            <form method="POST" action="{{ route('database.import.sql') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <input type="file" name="sql_file" class="form-control" accept=".sql,.txt" required>
                                </div>
                                <label class="text-xs d-flex align-center gap-4 mb-12"><input type="checkbox" name="backup_first"> Backup dulu sebelum import</label>
                                <button type="submit" class="btn btn-danger btn-sm w-100"><i class="fas fa-upload"></i> Import SQL</button>
                            </form>
                        </div>
                    </div>

                    <div class="card" style="border-color:#d97706">
                        <div class="card-header" style="background:#d97706;color:#fff"><i class="fas fa-table"></i> Import CSV</div>
                        <div class="card-body">
                            <div class="text-xs text-muted mb-8">Import dari file CSV (maks 50MB)</div>
                            <form method="POST" action="{{ route('database.import.csv') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Tabel Tujuan:</label>
                                    <select name="table" class="form-control" required>
                                        @foreach($tables as $table)
                                        <option value="{{ $table['name'] }}">{{ $table['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                                </div>
                                <label class="text-xs d-flex align-center gap-4 mb-4"><input type="checkbox" name="has_headers" checked> Baris pertama adalah header</label>
                                <label class="text-xs d-flex align-center gap-4 mb-4"><input type="checkbox" name="clear_table"> Kosongkan tabel sebelum import</label>
                                <label class="text-xs d-flex align-center gap-4 mb-12"><input type="checkbox" name="backup_first"> Backup dulu sebelum import</label>
                                <button type="submit" class="btn btn-warning btn-sm w-100"><i class="fas fa-upload"></i> Import CSV</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-table" style="color:var(--ac);margin-right:6px"></i>Daftar Tabel</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Tabel</th>
                            <th>Baris</th>
                            <th>Ukuran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tables as $table)
                        <tr>
                            <td class="fw-600">{{ $table['name'] }}</td>
                            <td><span class="badge badge-blue">{{ number_format($table['records']) }}</span></td>
                            <td class="text-sm text-muted">{{ $table['size'] }} MB</td>
                            <td>
                                <div class="d-flex gap-4">
                                    <form method="POST" action="{{ route('database.export.csv') }}">
                                        @csrf
                                        <input type="hidden" name="tables[]" value="{{ $table['name'] }}">
                                        <button type="submit" class="btn btn-xs btn-secondary" title="Export CSV"><i class="fas fa-file-csv"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('database.export.json') }}">
                                        @csrf
                                        <input type="hidden" name="tables[]" value="{{ $table['name'] }}">
                                        <button type="submit" class="btn btn-xs btn-secondary" title="Export JSON"><i class="fas fa-brackets-curly"></i></button>
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

@endsection

@push('styles')
<style>
@media(max-width:1024px){ #grid-db{grid-template-columns:1fr!important} }
</style>
@endpush
