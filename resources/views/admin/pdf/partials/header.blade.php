<table class="ceot-header">
    <tr>
        <td class="ceot-brand-cell">
            <span class="ceot-mark">CEOT</span>
            <span class="ceot-brand-copy">
                <span class="ceot-brand-name">CEOT DATES</span><br>
                <span class="ceot-brand-tagline">Gestión odontológica</span>
            </span>
        </td>
        <td class="ceot-document-cell">
            <div class="ceot-kicker">{{ $kicker ?? 'Documento clínico' }}</div>
            <div class="ceot-title">{{ $title }}</div>
            @if(!empty($subtitle))
                <div class="ceot-subtitle">{{ $subtitle }}</div>
            @endif
        </td>
    </tr>
</table>
