<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Dentist;
use Illuminate\Http\Request;

class DentistController extends Controller
{
    /**
     * GET /api/v1/mobile/dentists
     * List all active dentists
     */
    public function index()
    {
        // Columns available based on Model: id, name, specialty, chair_id, status
        // NOT available: specialization, photo_path, bio
        $dentists = Dentist::where('status', 1)
            ->select('id', 'name', 'specialty') 
            ->get()
            ->map(function($d) {
                return [
                    'id'             => $d->id,
                    'name'           => $d->name,
                    'specialization' => $d->specialty ?? 'Odontólogo General',
                    'photo_url'      => null, // Not in schema
                    'bio'            => null, // Not in schema
                ];
            });

        return response()->json($dentists);
    }
}
