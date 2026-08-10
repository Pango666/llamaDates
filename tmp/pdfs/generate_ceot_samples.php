<?php

use App\Models\Appointment;
use App\Models\Consent;
use App\Models\Dentist;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$output = dirname(__DIR__, 2).'/output/pdf';
if (! is_dir($output)) {
    mkdir($output, 0777, true);
}

$user = new User(['name' => 'Administración CEOT', 'email' => 'admin@ceot.test', 'role' => 'admin']);
$user->id = 1;
Auth::setUser($user);

$patient = new Patient([
    'first_name' => 'María',
    'last_name' => 'Fernández Rojas',
    'ci' => '7894561 LP',
    'phone' => '76543210',
    'email' => 'maria.fernandez@example.com',
]);
$patient->id = 1;

$dentist = new Dentist(['name' => 'Dra. Ana Pérez']);
$dentist->id = 1;
$service = new Service(['name' => 'Restauración estética', 'price' => 350]);
$service->id = 1;

$items = collect([
    tap(new InvoiceItem(['description' => 'Restauración estética', 'quantity' => 1, 'unit_price' => 350, 'total' => 350]), fn ($item) => $item->setRelation('service', $service)),
    tap(new InvoiceItem(['description' => 'Limpieza dental preventiva', 'quantity' => 1, 'unit_price' => 180, 'total' => 180]), fn ($item) => $item->setRelation('service', new Service(['name' => 'Limpieza dental preventiva']))),
]);
$payment = new Payment(['amount' => 300, 'method' => 'transfer']);
$payment->created_at = Carbon::parse('2026-08-05 09:10');
$payments = collect([$payment]);
$invoice = new Invoice([
    'number' => '2026-0042',
    'total' => 503.50,
    'discount' => 50,
    'tax_percent' => 5,
    'status' => 'issued',
    'issued_at' => Carbon::parse('2026-08-05'),
    'notes' => 'Saldo pendiente pagadero en la próxima visita.',
    'created_at' => Carbon::parse('2026-08-05 09:00'),
]);
$invoice->id = 42;
$invoice->created_at = Carbon::parse('2026-08-05 09:00');
$invoice->setRelation('patient', $patient);
$invoice->setRelation('items', $items);
$invoice->setRelation('payments', $payments);
$invoice->setAttribute('calc_total', 503.50);
$invoice->setAttribute('calc_paid', 300);
$invoice->setAttribute('calc_balance', 203.50);

Pdf::loadView('admin.billing.print', [
    'invoice' => $invoice,
    'subtotal' => 530,
    'taxAmount' => 23.50,
    'grandTotal' => 503.50,
])->setPaper('a4')->save($output.'/muestra-recibo-ceot.pdf');

Pdf::loadView('admin.billing.pdf', [
    'invoices' => collect(array_fill(0, 12, $invoice)),
    'totalInvoiced' => 6042,
    'totalPaid' => 3600,
    'totalPending' => 2442,
    'filters' => ['from' => '2026-08-01', 'to' => '2026-08-05', 'status' => 'all', 'q' => null],
    'user' => $user,
])->setPaper('a4', 'landscape')->save($output.'/muestra-reporte-pagos-ceot.pdf');

$appointment = new Appointment([
    'date' => '2026-08-05',
    'start_time' => '10:30:00',
    'status' => 'confirmed',
]);
$appointment->id = 1;
$appointment->setRelation('patient', $patient);
$appointment->setRelation('dentist', $dentist);
$appointment->setRelation('service', $service);
Pdf::loadView('admin.appointments.pdf', [
    'appointments' => collect(array_fill(0, 24, $appointment)),
    'statusCounts' => ['reserved'=>0, 'confirmed'=>24, 'in_service'=>0, 'done'=>0, 'no_show'=>0, 'canceled'=>0],
    'topDentists' => collect([(object) ['name' => $dentist->name, 'total' => 24]]),
    'serviceStats' => collect([(object) ['name' => $service->name, 'total' => 24]]),
    'totalAppointments' => 24,
    'filters' => ['date'=>'2026-08-05', 'dentist_id'=>1, 'status'=>null, 'q'=>null],
    'dentists' => collect([$dentist]),
])->setPaper('a4')->save($output.'/muestra-reporte-citas-ceot.pdf');

$product = new Product(['name' => 'Guantes de nitrilo', 'sku' => 'GUA-NIT-M', 'unit' => 'cajas']);
$location = new Location(['name' => 'Almacén central']);
$movement = new InventoryMovement([
    'type' => 'in', 'qty' => 12, 'unit_cost' => 48.50, 'lot' => 'LOT-0826',
    'expires_at' => '2028-04-30',
]);
$movement->created_at = Carbon::parse('2026-08-05 08:15');
$movement->setRelation('product', $product);
$movement->setRelation('location', $location);
$movement->setRelation('user', $user);
$request = Request::create('/admin/inventario/movimientos/pdf', 'GET', ['from'=>'2026-08-01', 'to'=>'2026-08-05']);
Pdf::loadView('admin.inv.movs.pdf', ['movs'=>collect(array_fill(0, 26, $movement)), 'r'=>$request])
    ->setPaper('a4', 'landscape')->save($output.'/muestra-inventario-ceot.pdf');

$appointment->setRelation('dentist', $dentist);
$consent = new Consent(['title'=>'Consentimiento para restauración dental', 'body'=>'Declaro que recibí información clara sobre el procedimiento, sus beneficios, alternativas y posibles riesgos. Autorizo al equipo profesional de CEOT a realizar el tratamiento descrito.', 'signed_by_doc'=>'7894561 LP']);
$consent->id = 7;
$consent->setRelation('patient', $patient);
$consent->setRelation('appointment', $appointment);
Pdf::loadView('admin.consents.print', ['consent'=>$consent, 'html'=>$consent->body])
    ->setPaper('a4')->save($output.'/muestra-consentimiento-ceot.pdf');

$treatment = new Treatment(['tooth_code'=>'16', 'surface'=>'O', 'price'=>350, 'status'=>'pending', 'notes'=>'Control posterior en siete días.']);
$treatment->setRelation('service', $service);
$plan = new TreatmentPlan(['title'=>'Rehabilitación conservadora', 'estimate_total'=>350, 'status'=>'approved', 'approved_at'=>Carbon::parse('2026-08-05 09:30')]);
$plan->id = 18;
$plan->setRelation('patient', $patient);
$plan->setRelation('approver', $user);
$plan->setRelation('treatments', collect([$treatment]));
Pdf::loadView('admin.plans.print', ['plan'=>$plan, 'isPdf'=>true])->setPaper('a4')->save($output.'/muestra-plan-tratamiento-ceot.pdf');

echo "Muestras generadas en {$output}".PHP_EOL;
