<div class="space-y-6">
  <div class="grid md:grid-cols-2 gap-6">
    {{-- Nombre --}}
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Nombre del Rol <span class="text-red-500">*</span>
      </label>
      <input 
        type="text" 
        name="name" 
        value="{{ old('name', $role->name ?? '') }}" 
        class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        placeholder="Ej: administrador, asistente, etc."
        required
      >
      <p class="text-xs text-slate-500">Identificador único del rol (usado en el código).</p>
    </div>

    {{-- Etiqueta --}}
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        Etiqueta
      </label>
      <input 
        type="text" 
        name="label" 
        value="{{ old('label', $role->label ?? '') }}" 
        class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        placeholder="Ej: Administrador del Sistema"
      >
      <p class="text-xs text-slate-500">Nombre descriptivo para mostrar en la interfaz.</p>
    </div>
  </div>

  {{-- Información adicional --}}
  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start gap-3">
      <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div class="text-sm text-blue-700">
        <strong>Nota:</strong> Después de crear el rol, podrá asignarle permisos específicos desde la opción "Permisos" en la lista de roles.
      </div>
    </div>
  </div>
</div>