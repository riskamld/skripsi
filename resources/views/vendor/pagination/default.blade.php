@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bi bi-chevron-left"></i>
                        Previous
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bi bi-chevron-left"></i>
                        Previous
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Next
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        Next
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>

        {{-- Pagination Info --}}
        <div class="d-flex justify-content-center mt-2">
            <small class="text-muted">
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
                (Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }})
            </small>
        </div>
    </nav>
@endif

<style>
.pagination {
    margin: 0;
}

.page-link {
    color: #495057;
    background-color: #fff;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
    border-radius: 0.375rem !important;
    margin: 0 2px;
}

.page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
    border-color: #adb5bd;
    text-decoration: none;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

.page-link:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

@media (max-width: 576px) {
    .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
    }

    .pagination {
        flex-wrap: wrap;
    }
}
</style>
