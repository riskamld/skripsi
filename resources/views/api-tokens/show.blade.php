@extends('layouts.app')
@section('title', 'Detail Token API — Mafaza Fortuna')
@section('page-title', 'Detail Token API')

@section('content')

<div class="mb-16">
    <a href="{{ route('api-tokens.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Token</a>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr" id="grid-token">

    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-header"><i class="fas fa-key" style="color:var(--ac);margin-right:6px"></i>Detail Token API</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <div class="d-flex justify-between text-sm"><span class="text-muted">ID Token</span><span class="fw-600">{{ $token->id }}</span></div>
                <div class="d-flex justify-between text-sm"><span class="text-muted">Nama Token</span><span class="fw-700">{{ $token->name }}</span></div>
                <div class="d-flex justify-between text-sm align-center">
                    <span class="text-muted">Status</span>
                    @if($token->is_active)
                    <span class="badge badge-green"><i class="fas fa-check-circle"></i> Aktif</span>
                    @else
                    <span class="badge badge-gray"><i class="fas fa-pause-circle"></i> Nonaktif</span>
                    @endif
                </div>
                <div>
                    <label class="form-label">Token</label>
                    <div class="input-group">
                        <input type="password" class="form-control" value="{{ $token->token }}" readonly id="tokenField">
                        <button class="btn btn-secondary" type="button" onclick="copyToken(event)" title="Salin"><i class="fas fa-copy"></i></button>
                        <button class="btn btn-secondary" type="button" onclick="toggleTokenVisibility()" title="Tampilkan/sembunyikan"><i class="fas fa-eye" id="toggleIcon"></i></button>
                    </div>
                    <div class="text-xs text-muted mt-4">Token ini memberi akses API ke aplikasi Anda</div>
                </div>
                <div class="d-flex justify-between text-sm"><span class="text-muted">Dibuat</span><span>{{ $token->created_at->format('d M Y \\p\\u\\k\\u\\l H:i:s') }}</span></div>
                <div class="d-flex justify-between text-sm"><span class="text-muted">Diperbarui</span><span>{{ $token->updated_at->format('d M Y \\p\\u\\k\\u\\l H:i:s') }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-chart-line" style="color:var(--ac);margin-right:6px"></i>Informasi Penggunaan</div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="metric">
                        <div class="metric-label">Terakhir Digunakan</div>
                        <div class="metric-value" style="font-size:16px">{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Belum pernah' }}</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">IP Terakhir</div>
                        <div class="metric-value" style="font-size:16px">{{ $token->last_used_ip ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-header"><i class="fas fa-cogs" style="color:var(--ac);margin-right:6px"></i>Aksi Token</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px">
                <form method="POST" action="{{ route('api-tokens.toggle-status', $token) }}">
                    @csrf
                    <button type="submit" class="btn {{ $token->is_active ? 'btn-warning' : 'btn-success' }} btn-sm w-100">
                        <i class="fas fa-{{ $token->is_active ? 'pause' : 'play' }}"></i> {{ $token->is_active ? 'Nonaktifkan Token' : 'Aktifkan Token' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('api-tokens.regenerate', $token) }}" onsubmit="return confirm('Ini akan membuat token lama tidak valid. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="fas fa-sync-alt"></i> Regenerasi Token</button>
                </form>
                <form method="POST" action="{{ route('api-tokens.destroy', $token) }}" onsubmit="return confirm('Yakin ingin menghapus token ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100"><i class="fas fa-trash"></i> Hapus Token</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-shield-alt" style="color:var(--ac);margin-right:6px"></i>Informasi Keamanan</div>
            <div class="card-body">
                <div class="alert alert-info" style="align-items:flex-start">
                    <i class="fas fa-info-circle" style="margin-top:2px"></i>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <div class="text-sm">Jaga kerahasiaan token API, jangan bagikan ke publik</div>
                        <div class="text-sm">Regenerasi token secara berkala untuk keamanan lebih baik</div>
                        <div class="text-sm">Pantau penggunaan & nonaktifkan token yang tidak terpakai</div>
                        <div class="text-sm">Gunakan token berbeda untuk aplikasi berbeda</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-book" style="color:var(--ac);margin-right:6px"></i>Cara Penggunaan API</div>
            <div class="card-body">
                <div class="text-sm fw-600 mb-6">Cara menggunakan token ini:</div>
                <pre style="background:var(--bg);padding:10px;border-radius:6px;font-size:11.5px;overflow-x:auto"><code>Authorization: Bearer {{ $token->token }}</code></pre>
                <div class="text-xs text-muted mt-4">Sertakan header ini di setiap request API</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@media(max-width:1024px){ #grid-token{grid-template-columns:1fr!important} }
</style>
@endpush

@push('scripts')
<script>
function copyToken(e) {
    const tokenField = document.getElementById('tokenField');
    const prevType = tokenField.type;
    tokenField.type = 'text';
    tokenField.select();
    document.execCommand('copy');
    tokenField.type = prevType;

    const button = e.target.closest('button');
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => { button.innerHTML = originalIcon; }, 2000);
}

function toggleTokenVisibility() {
    const tokenField = document.getElementById('tokenField');
    const toggleIcon = document.getElementById('toggleIcon');
    if (tokenField.type === 'password') {
        tokenField.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        tokenField.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}
</script>
@endpush
