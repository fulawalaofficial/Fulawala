@if($paginator->hasPages())
<nav class="pagination" role="navigation" aria-label="Pagination">
@if($paginator->onFirstPage())<span class="disabled">← Previous</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Previous</a>@endif
<div class="pagination-pages">@foreach($elements as $element)@if(is_string($element))<span class="dots">{{ $element }}</span>@endif @if(is_array($element))@foreach($element as $page=>$url)@if($page==$paginator->currentPage())<span class="current">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif @endforeach @endif @endforeach</div>
@if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>@else<span class="disabled">Next →</span>@endif
</nav>
@endif
