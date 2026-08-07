@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Paginação">
        <p>
            Exibindo <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
            de <strong>{{ $paginator->total() }}</strong>
        </p>
        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="pagination-button is-disabled">Anterior</span>
            @else
                <a class="pagination-button" href="{{ $paginator->previousPageUrl() }}">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-button is-disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="pagination-button is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-button" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-button" href="{{ $paginator->nextPageUrl() }}">Próxima</a>
            @else
                <span class="pagination-button is-disabled">Próxima</span>
            @endif
        </div>
    </nav>
@endif
