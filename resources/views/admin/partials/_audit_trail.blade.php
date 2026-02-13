{{-- 
  Audit Trail Partial
  Usage: @include('admin.partials._audit_trail', ['model' => $patient])
  Only visible for users with 'users.manage' permission (admins)
--}}
@can('users.manage')
  @php
    $auditLogs = \App\Models\AuditLog::where('auditable_type', get_class($model))
      ->where('auditable_id', $model->getKey())
      ->with('user:id,name')
      ->orderBy('created_at', 'desc')
      ->limit(20)
      ->get();
  @endphp

  @if($auditLogs->count())
    <div class="mt-6 border-t border-slate-200 pt-5">
      <h4 class="text-base font-semibold text-slate-600 uppercase tracking-wider mb-3 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Historial de cambios
      </h4>

      <div class="space-y-0 relative pl-4 border-l-2 border-slate-200">
        @foreach($auditLogs as $log)
          @php
            $color = match($log->action) {
                'created' => 'bg-emerald-500',
                'updated' => 'bg-blue-500',
                'deleted' => 'bg-rose-500',
                'toggled' => 'bg-amber-500',
                default   => 'bg-slate-400',
            };
          @endphp
          <div class="relative pb-4 last:pb-0">
            {{-- Timeline dot --}}
            <div class="absolute -left-[calc(0.5rem+1px)] top-1.5 w-2.5 h-2.5 rounded-full {{ $color }} ring-2 ring-white"></div>

            <div class="ml-3">
              <div class="flex items-baseline gap-1.5 flex-wrap">
                <span class="text-base font-semibold text-slate-800">{{ $log->user->name ?? 'Sistema' }}</span>
                <span class="text-base text-slate-600">{{ $log->action_label }}</span>
                <span class="text-sm text-slate-400">· {{ $log->created_at->diffForHumans() }}</span>
              </div>

              @if($log->action === 'updated' && !empty($log->changed_fields))
                <div class="mt-1.5 space-y-1">
                  @foreach($log->changed_fields as $field => $change)
                    <div class="text-sm text-slate-500">
                      <span class="font-medium text-slate-700">{{ $field }}:</span>
                      <span class="line-through text-rose-400">{{ Str::limit($change['old'] ?? '(vacío)', 50) }}</span>
                      →
                      <span class="text-emerald-600">{{ Str::limit($change['new'] ?? '(vacío)', 50) }}</span>
                    </div>
                  @endforeach
                </div>
              @elseif($log->action === 'toggled')
                @php $newActive = $log->new_values['is_active'] ?? null; @endphp
                <div class="text-sm mt-0.5 font-medium {{ $newActive ? 'text-emerald-600' : 'text-rose-500' }}">
                  {{ $newActive ? 'Activado' : 'Desactivado' }}
                </div>
              @endif

              <div class="text-xs text-slate-400 mt-0.5">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->ip_address }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
@endcan
