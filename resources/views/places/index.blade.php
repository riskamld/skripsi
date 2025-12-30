@extends('layouts.app')

@section('page-title', 'Places')
@section('page-subtitle', 'Manage your places database')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('places.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Place
        </a>
        <form method="POST" action="{{ route('places.clear-all') }}" class="d-inline" style="margin-left: 10px;">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to clear all places?')">
                <i class="fas fa-trash"></i> Clear All Places
            </button>
        </form>
    </div>
</div>

<!-- Places table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Places</h3>

        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th style="width: 15%;">Name</th>
                    <th style="width: 20%;">Address</th>
                    <th style="width: 15%;">Phone</th>
                    <th style="width: 15%;">Website</th>
                    <th style="width: 10%;">Rating</th>
                    <th style="width: 25%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($places ?? [] as $place)
                <tr style="height: 45px;">
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div style="font-size: 0.875rem; font-weight: 600;">{{ Str::limit($place->name, 20) }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ $place->created_at->format('M d') }}</small>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div style="font-size: 0.875rem;">{{ Str::limit($place->address, 25) }}</div>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}"
                               target="_blank"
                               class="btn btn-sm btn-success"
                               title="Chat via WhatsApp">
                                <i class="fab fa-whatsapp"></i> {{ Str::limit($place->phone, 12) }}
                            </a>
                        @else
                            <span style="font-size: 0.875rem;">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->website)
                            <a href="{{ $place->website }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @else
                            <span style="font-size: 0.875rem;">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->rating)
                            <span class="badge badge-warning" style="font-size: 0.75rem;">
                                <i class="fas fa-star"></i> {{ $place->rating }}
                            </span>
                        @else
                            <span class="badge badge-secondary" style="font-size: 0.75rem;">-</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('places.show', $place) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('places.edit', $place) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('places.destroy', $place) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                            <h5>No places found</h5>
                            <p style="font-size: 0.875rem;">Get started by adding your first place to the database.</p>
                            <a href="{{ route('places.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Place
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->

    @if(isset($places) && $places->hasPages())
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" role="status" aria-live="polite">
                    Showing {{ $places->firstItem() }} to {{ $places->lastItem() }} of {{ $places->total() }} entries
                </div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    {{ $places->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<!-- /.card -->
@endsection
