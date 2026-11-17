{{-- Paginación a partir de `meta` (formato estándar de Laravel). Params: meta, route, query? --}}
@if (($meta['last_page'] ?? 1) > 1)
    <nav aria-label="Paginación">
        <ul class="pagination">
            @for ($p = 1; $p <= $meta['last_page']; $p++)
                <li class="page-item {{ $p == $meta['current_page'] ? 'active' : '' }}">
                    <a class="page-link" href="{{ route($route, array_filter([...($query ?? []), 'page' => $p])) }}">{{ $p }}</a>
                </li>
            @endfor
        </ul>
    </nav>
@endif
