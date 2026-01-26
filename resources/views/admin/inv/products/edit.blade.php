@extends('layouts.app')
@section('title', 'Editar Producto: ' . $product->name)

@section('header-actions')
  <a href="{{ route('admin.inv.products.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Productos
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Editar Producto
        </h1>
        <p class="text-sm text-slate-600 mt-1">Modifique la información del producto.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.products.update', $product) }}" class="card">
      @csrf @method('PUT')
      @include('admin.inv.products.form-fields')

      <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 mb-6 md:col-span-3">
        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Lotes y Stock Actual
        </h3>
        
        @if(isset($batches) && $batches->isNotEmpty())
          <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                  <th class="px-4 py-2 font-medium text-slate-700">Lote</th>
                  <th class="px-4 py-2 font-medium text-slate-700">Vencimiento</th>
                  <th class="px-4 py-2 font-medium text-slate-700 text-right">Cantidad</th>
                  <th class="px-4 py-2 font-medium text-slate-700">Estado</th>
                  <th class="px-4 py-2 font-medium text-slate-700 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                @foreach($batches as $batch)
                  @php
                    $qty = (int) $batch->stock; 
                    if($qty <= 0) continue; 
                    
                    $expDate = $batch->expires_at ? \Carbon\Carbon::parse($batch->expires_at) : null;
                    $isExpired = $expDate?->isPast();
                    // Fix: Check if expDate is not null before checking diff
                    $isSoon = $expDate && !$isExpired && $expDate->diffInDays(now()) <= 30;
                    
                    // ID único para el modal/form
                    $rowId = 'batch_' . Str::random(8); 
                  @endphp
                  <tr>
                    <td class="px-4 py-2 text-slate-600 font-mono">
                      @if($batch->lot === 'STOCK_ANTIGUO')
                        <span class="text-amber-600 font-bold">Stock Inicial (Sin Lote)</span>
                      @else
                        {{ $batch->lot ?: 'Sin lote' }}
                      @endif
                    </td>
                    <td class="px-4 py-2">
                      @if($batch->lot === 'STOCK_ANTIGUO')
                         <span class="text-slate-400 italic">Sin fecha</span>
                      @elseif($expDate)
                        <span class="{{ $isExpired ? 'text-rose-600 font-bold' : ($isSoon ? 'text-amber-600 font-bold' : 'text-slate-600') }}">
                          {{ $expDate->format('d/m/Y') }}
                        </span>
                      @else
                         <span class="text-slate-400">—</span>
                      @endif
                    </td>
                    <td class="px-4 py-2 text-right font-medium text-slate-700">
                      {{ number_format($qty, 0) }}
                    </td>
                    <td class="px-4 py-2">
                       @if($batch->lot === 'STOCK_ANTIGUO')
                         <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Requiere Actualización</span>
                       @elseif($isExpired)
                         <span class="text-xs bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full">Vencido</span>
                       @elseif($isSoon)
                         <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Vence pronto</span>
                       @else
                         <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Ok</span>
                       @endif
                    </td>
                    <td class="px-4 py-2 text-right">
                       <button type="button" onclick="openBatchModal('{{ $batch->lot }}', '{{ $batch->expires_at }}')" class="btn btn-xs btn-ghost text-blue-600">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                         </svg>
                       </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-4 bg-white rounded-lg border border-slate-200 border-dashed">
            <p class="text-sm text-slate-500">No hay stock registrado para este producto.</p>
          </div>
        @endif
      </div>

      <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6 md:col-span-3">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <span class="font-medium text-slate-700">Creado:</span>
            <span class="text-slate-600">{{ $product->created_at?->format('d/m/Y H:i') }}</span>
          </div>
          <div>
            <span class="font-medium text-slate-700">Última actualización:</span>
            <span class="text-slate-600">{{ $product->updated_at?->format('d/m/Y H:i') }}</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-4 pt-6 border-t border-slate-200 md:col-span-3">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar Cambios
        </button>
        <a href="{{ route('admin.inv.products.index') }}" class="btn btn-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Volver
        </a>
      </div>
    </form>
  </div>

  {{-- SINGLE MODAL FOR BATCH EDITING (OUTSIDE MAIN FORM) --}}
  <div id="batchEditModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-6 text-left">
      <h4 class="font-bold text-lg mb-4">Editar Lote/Vencimiento</h4>
      
      <form action="{{ route('admin.inv.products.update_batch', $product) }}" method="POST">
        @csrf
        <input type="hidden" name="current_lot" id="modalCurrentLot">
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Código de Lote</label>
          <input type="text" name="new_lot" id="modalNewLot" class="w-full border-slate-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Vencimiento</label>
          <input type="date" name="expires_at" id="modalExpiresAt" class="w-full border-slate-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeBatchModal()" class="btn btn-ghost">Cancelar</button>
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openBatchModal(currentLot, expiresAt) {
        const modal = document.getElementById('batchEditModal');
        const lotInput = document.getElementById('modalNewLot');
        const currentInput = document.getElementById('modalCurrentLot');
        const dateInput = document.getElementById('modalExpiresAt');

        currentInput.value = currentLot;
        
        if(currentLot === 'STOCK_ANTIGUO') {
             lotInput.value = '';
             lotInput.placeholder = 'Ingrese nuevo código';
        } else {
             lotInput.value = currentLot;
        }

        // expiresAt comes as '2023-05-10' or empty string
        // If the date includes Time (DB format), split it
        if(expiresAt && expiresAt.includes('T')) {
            dateInput.value = expiresAt.split('T')[0];
        } else {
            dateInput.value = expiresAt || '';
        }

        modal.classList.remove('hidden');
    }

    function closeBatchModal() {
        document.getElementById('batchEditModal').classList.add('hidden');
    }
  </script>
@endsection
