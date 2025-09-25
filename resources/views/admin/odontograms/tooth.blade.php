@php $code = (string)$code; @endphp
<div id="tooth-{{ $code }}" class="tooth p-2 border rounded text-center cursor-pointer select-none" data-code="{{ $code }}">
  <div class="text-[10px] text-slate-500 mb-1">#{{ $code }}</div>

  {{-- superficies: O M D B L (arriba al centro / laterales / abajo) --}}
  <div class="grid grid-cols-3 gap-0.5 mb-1">
    <span class="surf block h-2.5 rounded col-span-3" data-surface="O"></span>
    <span class="surf block h-2.5 rounded" data-surface="M"></span>
    <span class="surf block h-2.5 rounded" data-surface="D"></span>
    <span class="surf block h-2.5 rounded col-span-3" data-surface="L"></span>
  </div>

  <div class="tooth-status min-h-[16px]"></div>
</div>
