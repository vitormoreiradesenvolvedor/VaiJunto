<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function edit(Vehicle $vehicle): View
    {
        if ($vehicle->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para editar este veículo.');
        }

        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Vehicle $vehicle, Request $request): JsonResponse
    {
        if ($vehicle->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para editar este veículo.');
        }

        $vehicle->update($request->only(['model', 'plate', 'color', 'year', 'seats']));

        return response()->json($vehicle);
    }
}
