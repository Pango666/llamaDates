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
            ->select('id', 'name', 'price', 'duration_min')
            ->get();

        return response()->json($services);
    }
}
