<div class="tile"></div>
@php $surfaces = $surfaces ?? []; @endphp
<div class="surf s-O {{ isset($surfaces['O']) ? 'cond-'.$surfaces['O'] : '' }}"></div>
<div class="surf s-M {{ isset($surfaces['M']) ? 'cond-'.$surfaces['M'] : '' }}"></div>
<div class="surf s-D {{ isset($surfaces['D']) ? 'cond-'.$surfaces['D'] : '' }}"></div>
<div class="surf s-B {{ isset($surfaces['B']) ? 'cond-'.$surfaces['B'] : '' }}"></div>
<div class="surf s-L {{ isset($surfaces['L']) ? 'cond-'.$surfaces['L'] : '' }}"></div>
