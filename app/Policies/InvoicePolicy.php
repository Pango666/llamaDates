<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function __construct()
    {
        //
    }

    /**
     * Determina si el usuario puede ver el listado de facturas.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'billing.manage',
            'billing.index',
            'payments.view_status',
        ]);
    }

    /**
     * Determina si el usuario puede ver una factura específica.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Staff con permiso puede ver cualquier factura
        if ($user->hasAnyPermission(['billing.manage', 'billing.show', 'invoices.show'])) {
            return true;
        }

        // Paciente solo puede ver sus propias facturas
        $pid = optional($user->patient)->id;
        return $pid && $invoice->patient_id === $pid;
    }

    /**
     * Determina si el usuario puede crear facturas.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['billing.manage', 'billing.create']);
    }

    /**
     * Determina si el usuario puede actualizar una factura.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission(['billing.manage', 'billing.update']);
    }

    /**
     * Determina si el usuario puede eliminar una factura.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission(['billing.manage', 'billing.delete']);
    }

    /**
     * Determina si el usuario puede emitir una factura.
     */
    public function issue(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission(['billing.manage', 'billing.issue']);
    }

    /**
     * Determina si el usuario puede cancelar una factura.
     */
    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission(['billing.manage', 'billing.cancel']);
    }

    /**
     * Determina si el usuario puede agregar pagos a una factura.
     */
    public function addPayment(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission(['billing.manage', 'billing.payments.add', 'invoices.payments.store']);
    }

    /**
     * Determina si el usuario puede marcar como pagada una factura.
     */
    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission(['billing.manage', 'invoices.markPaid']);
    }

    /**
     * Determina si el usuario puede descargar una factura.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        // Staff puede descargar cualquier factura
        if ($user->hasAnyPermission(['billing.manage', 'invoices.download'])) {
            return true;
        }

        // Paciente puede descargar sus propias facturas
        $pid = optional($user->patient)->id;
        return $pid && $invoice->patient_id === $pid;
    }
}
