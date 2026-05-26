<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RideRequest;
use App\Services\RideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RideController extends Controller
{
    public function __construct(private RideService $rideService) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'origin'              => 'required|string',
            'destination'        => 'required|string',
            'origin_coords'      => 'required|string',
            'destination_coords' => 'required|string',
            'scheduled_for'      => 'required|date',
            'seats_needed'       => 'required|integer|min:1',
        ]);

        $rideRequest = $this->rideService->request($data, $request->user());

        return response()->json($rideRequest, 201);
    }

    public function accept(RideRequest $rideRequest): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'passenger') {
            abort(403, 'Apenas motoristas podem aceitar caronas.');
        }

        $ride = $this->rideService->accept($rideRequest, $user);

        return response()->json($ride);
    }

    public function reject(RideRequest $rideRequest): JsonResponse
    {
        $rideRequest->update(['status' => 'rejected']);

        return response()->json(['message' => 'Solicitação recusada.']);
    }

    public function cancel(Ride $ride, Request $request): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);

        $this->rideService->cancel($ride, $request->input('reason'));

        return response()->json(['message' => 'Carona cancelada.']);
    }
}
