<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of specialties
     */
    public function index(Request $request)
    {
        $query = Specialty::where('is_active', true);

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $specialties = $query->orderBy('name')->get();

        return response()->json([
            'data' => $specialties
        ]);
    }

    /**
     * Display the specified specialty
     */
    public function show(Specialty $specialty)
    {
        return response()->json([
            'data' => $specialty
        ]);
    }
}
