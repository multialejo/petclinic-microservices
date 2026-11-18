{{-- Paginador de ownersList.html / vetList.html. Params: meta, url (base, ej. '/vets.html'), query? (extra) --}}
@php
    $total = (int) ($meta['last_page'] ?? 1);
    $current = (int) ($meta['current_page'] ?? 1);
    $href = fn (int $p) => $url.'?'.http_build_query(array_filter([...($query ?? []), 'page' => $p], fn ($v) => $v !== '' && $v !== null));
@endphp
@if ($total > 1)
  <div>
    <span>pages</span>
    <span>[</span>
@for ($i = 1; $i <= $total; $i++)
    <span>
@if ($current != $i)
      <a href="{{ $href($i) }}">{{ $i }}</a>
@else
      <span>{{ $i }}</span>
@endif
    </span>
@endfor
    <span>]&nbsp;</span>
    <span>
@if ($current > 1)
      <a href="{{ $href(1) }}" title="First" class="fa fa-fast-backward"></a>
@else
      <span title="First" class="fa fa-fast-backward"></span>
@endif
    </span>
    <span>
@if ($current > 1)
      <a href="{{ $href($current - 1) }}" title="Previous" class="fa fa-step-backward"></a>
@else
      <span title="Previous" class="fa fa-step-backward"></span>
@endif
    </span>
    <span>
@if ($current < $total)
      <a href="{{ $href($current + 1) }}" title="Next" class="fa fa-step-forward"></a>
@else
      <span title="Next" class="fa fa-step-forward"></span>
@endif
    </span>
    <span>
@if ($current < $total)
      <a href="{{ $href($total) }}" title="Last" class="fa fa-fast-forward"></a>
@else
      <span title="Last" class="fa fa-fast-forward"></span>
@endif
    </span>
  </div>
@endif
