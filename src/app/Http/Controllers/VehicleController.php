<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(auth()->user()->vehicle !== null, 422);

        $data = $request->validate([
            'model' => 'required|string|max:100',
            'plate' => 'required|string|max:10|unique:vehicles,plate',
            'color' => 'required|string|max:50',
            'year'  => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'seats' => 'required|integer|min:1|max:8',
        ]);

        $data['plate']   = strtoupper($data['plate']);
        $data['user_id'] = auth()->id();

        Vehicle::create($data);

        return redirect()->route('dashboard')
            ->with('success', 'Veículo cadastrado! Agora você pode oferecer caronas.');
    }

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
