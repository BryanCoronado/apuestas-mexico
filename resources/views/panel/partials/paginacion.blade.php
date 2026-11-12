@if ($paginator->hasPages())
    <nav class="paginacion" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="paginacion__item deshabilitado">«</span>
            <span class="paginacion__item deshabilitado">‹</span>
        @else
            <a class="paginacion__item" href="{{ $paginator->url(1) }}" aria-label="Primera">«</a>
            <a class="paginacion__item" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="paginacion__item deshabilitado">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="paginacion__item activo" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="paginacion__item" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="paginacion__item" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">›</a>
            <a class="paginacion__item" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Última">»</a>
        @else
            <span class="paginacion__item deshabilitado">›</span>
            <span class="paginacion__item deshabilitado">»</span>
        @endif
    </nav>
@endif
