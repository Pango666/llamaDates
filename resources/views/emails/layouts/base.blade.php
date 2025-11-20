<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $subject ?? 'LlamaDates' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Email-safe inline-ish styles */
    body { margin:0; padding:0; background:#f6f7fb; font-family: Arial, Helvetica, sans-serif; }
    .wrapper { width:100%; background:#f6f7fb; padding:24px 0; }
    .container { width:100%; max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; }
    .header { padding:16px 24px; border-bottom:1px solid #eee; }
    .brand { font-size:20px; font-weight:bold; color:#111; }
    .preheader { display:none; font-size:1px; color:#f6f7fb; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; }
    .banner img { display:block; width:100%; height:auto; }
    .hero { padding:24px; }
    .title { font-size:22px; margin:0 0 8px; color:#111; }
    .subtitle { font-size:14px; color:#666; margin:0 0 16px; }
    .content { padding:0 24px 24px; color:#333; font-size:14px; line-height:1.6; }
    .block-image img { display:block; width:100%; max-width:560px; height:auto; border-radius:6px; }
    .btn { display:inline-block; padding:12px 18px; background:#3b82f6; color:#fff !important; text-decoration:none; border-radius:6px; font-weight:bold; }
    .list { margin:12px 0; padding-left:18px; }
    .footer { padding:18px 24px; border-top:1px solid #eee; font-size:12px; color:#888; }
    .center { text-align:center; }
  </style>
</head>
<body>
  @if(!empty($preheader))
    <div class="preheader">{{ $preheader }}</div>
  @endif

  <div class="wrapper">
    <div class="container">
      <div class="header">
        <span class="brand">{{ $brand ?? 'LlamaDates' }}</span>
      </div>

      @if(!empty($banner_url))
      <div class="banner">
        <img src="{{ $banner_url }}" alt="Banner">
      </div>
      @endif

      <div class="hero">
        @isset($title)<h1 class="title">{{ $title }}</h1>@endisset
        @isset($subtitle)<p class="subtitle">{{ $subtitle }}</p>@endisset
      </div>

      <div class="content">
        @if(!empty($image_url))
          <p class="block-image center">
            <img src="{{ $image_url }}" alt="Imagen">
          </p>
        @endif

        @if(!empty($html))
          {!! $html !!}
        @elseif(!empty($text))
          <p>{{ $text }}</p>
        @endif

        @if(!empty($details) && is_array($details))
          <ul class="list">
            @foreach($details as $item)
              <li>{!! $item !!}</li>
            @endforeach
          </ul>
        @endif

        @if(!empty($button_url) && !empty($button_text))
          <p class="center" style="margin-top:18px;">
            <a class="btn" href="{{ $button_url }}" target="_blank" rel="noopener">{{ $button_text }}</a>
          </p>
        @endif
      </div>

      <div class="footer">
        {{ $footer ?? '© '.date('Y').' LlamaDates. Todos los derechos reservados.' }}
        @if(!empty($legal_note))
          <br>{{ $legal_note }}
        @endif
      </div>
    </div>
  </div>
</body>
</html>
