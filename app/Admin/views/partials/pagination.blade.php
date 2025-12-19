@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
    @endphp
    
    <div class="pagination-wrapper">
        <nav aria-label="Навигация по страницам">
            <ul class="pagination pagination-custom">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link page-link-prev">
                            <i class="bi bi-chevron-left me-1"></i> Назад
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link page-link-prev" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="bi bi-chevron-left me-1"></i> Назад
                        </a>
                    </li>
                @endif

                {{-- First Page --}}
                @if ($currentPage > 3)
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if ($currentPage > 4)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page >= $currentPage - 2 && $page <= $currentPage + 2)
                                @if ($page == $currentPage)
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Last Page --}}
                @if ($currentPage < $lastPage - 2)
                    @if ($currentPage < $lastPage - 3)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link page-link-next" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            Вперед <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link page-link-next">
                            Вперед <i class="bi bi-chevron-right ms-1"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
        
        {{-- Информация о страницах --}}
        {{-- <div class="pagination-info">
            <span class="text-muted">
                Страница {{ $currentPage }} из {{ $lastPage }}
            </span>
        </div> --}}
    </div>

    <style>
        .pagination-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
        
        .pagination-custom {
            margin-bottom: 0;
        }
        
        .pagination-custom .page-item {
            margin: 0 2px;
        }
        
        .pagination-custom .page-link {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            color: #495057;
            background-color: #fff;
            transition: all 0.2s ease;
        }
        
        .pagination-custom .page-link:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #007bff;
            text-decoration: none;
        }
        
        .pagination-custom .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }
        
        .pagination-custom .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
            border-color: #dee2e6;
            opacity: 0.6;
        }
        
        .pagination-custom .page-link-prev,
        .pagination-custom .page-link-next {
            min-width: 80px;
            text-align: center;
        }
        
        .pagination-info {
            font-size: 0.875rem;
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .pagination-custom {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .pagination-custom .page-item {
                margin-bottom: 5px;
            }
            
            .pagination-custom .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.8125rem;
            }
            
            .pagination-custom .page-link-prev,
            .pagination-custom .page-link-next {
                min-width: 70px;
            }
        }
    </style>
@endif