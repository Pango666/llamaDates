<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * GET /api/v1/mobile/services
     * List all active services
     */
    public function index()
    {
        $services = Service::where('active', true)
            ->get([
                'id', 'name', 'price', 'duration_min',
                'discount_active', 'discount_type', 'discount_amount',
                'discount_start_at', 'discount_end_at',
            ])
            ->map(fn (Service $service) => [
                'id' => $service->id,
                'name' => $service->name,
                'base_price' => (float) $service->price,
                'price' => $service->priceEffective(now()),
                'duration_min' => $service->duration_min,
                'discount' => $service->discountIsActive(now()) ? [
                    'type' => $service->discount_type,
                    'amount' => (float) $service->discount_amount,
                    'label' => $service->discountLabel(),
                ] : null,
            ]);

        return response()->json($services);
    }
}
