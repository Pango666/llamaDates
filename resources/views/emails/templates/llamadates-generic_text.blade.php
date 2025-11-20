{{ $title ?? 'LlamaDates' }}

@isset($subtitle)
{{ $subtitle }}
@endisset

@isset($text)
{{ $text }}
@endisset

@if(!empty($details) && is_array($details))
@foreach($details as $item)
- {!! strip_tags($item) !!}
@endforeach
@endif

@isset($button_text)
{{ $button_text }}: {{ $button_url }}
@endisset

{{ $footer ?? '© '.date('Y').' LlamaDates' }}
