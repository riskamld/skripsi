@extends('layouts.app')
@section('title', 'Token API — Mafaza Fortuna')
@section('page-title', 'Token API')

@push('topbar-actions')
<a href="{{ route('api-tokens.create') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-plus"></i> Buat Token
</a>
@endpush

@section('content')

<div class="card">
    <div class="card-header">
        <span>Token API</span>
        <span class="text-muted text-sm">{{ $tokens->total() }} token</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama Token</th>
                    <th>Status</th>
                    <th class="hide-mobile">Terakhir Dipakai</th>
                    <th class="hide-mobile">Dibuat</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tokens as $token)
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ Str::limit($token->name, 20) }}</div>
                        <code class="text-xs">…{{ substr($token->token, -8) }}</code>
                    </td>
                    <td>
                        @if($token->is_active)
                            <span class="badge badge-green"><i class="fas fa-circle" style="font-size:7px"></i> Aktif</span>
                        @else
                            <span class="badge badge-gray"><i class="fas fa-circle" style="font-size:7px"></i> Nonaktif</span>
                        @endif
                    </td>
                    <td class="hide-mobile text-muted text-sm">
                        {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : '—' }}
                    </td>
                    <td class="hide-mobile text-muted text-sm">
                        {{ $token->created_at->format('d M Y') }}
                    </td>
                    <td>
                        <div class="d-flex gap-4" style="justify-content:flex-end">
                            <a href="{{ route('api-tokens.show', $token) }}" class="btn btn-ghost btn-xs" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('api-tokens.toggle-status', $token) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-xs"
                                    title="{{ $token->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    onclick="return confirm('{{ $token->is_active ? 'Nonaktifkan' : 'Aktifkan' }} token ini?')">
                                    <i class="fas fa-{{ $token->is_active ? 'pause' : 'play' }}"
                                       style="color:{{ $token->is_active ? 'var(--or)' : 'var(--gn)' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('api-tokens.regenerate', $token) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-xs" title="Buat ulang"
                                    onclick="return confirm('Token lama akan tidak valid. Lanjutkan?')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('api-tokens.destroy', $token) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--rd)" title="Hapus"
                                    onclick="return confirm('Hapus token ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:48px;color:var(--tx3)">
                        <i class="fas fa-key" style="font-size:28px;display:block;margin-bottom:8px"></i>
                        Belum ada token API.<br>
                        <a href="{{ route('api-tokens.create') }}" class="btn btn-primary btn-sm mt-8">
                            <i class="fas fa-plus"></i> Buat Token
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tokens->lastPage() > 1)
    <div class="card-footer d-flex align-center justify-between">
        <span class="text-xs text-muted">{{ $tokens->firstItem() }}–{{ $tokens->lastItem() }} dari {{ $tokens->total() }}</span>
        <div class="pagination">
            @if($tokens->onFirstPage())
                <span class="page-link disabled">‹</span>
            @else
                <a class="page-link" href="{{ $tokens->previousPageUrl() }}">‹</a>
            @endif
            @foreach($tokens->getUrlRange(1, $tokens->lastPage()) as $page => $url)
                <a class="page-link {{ $page == $tokens->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
            @endforeach
            @if($tokens->hasMorePages())
                <a class="page-link" href="{{ $tokens->nextPageUrl() }}">›</a>
            @else
                <span class="page-link disabled">›</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
