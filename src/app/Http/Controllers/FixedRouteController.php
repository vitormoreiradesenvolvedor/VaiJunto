<?php

namespace App\Http\Controllers;

use App\Models\FixedRoute;
use Illuminate\Http\JsonResponse;

class FixedRouteController extends Controller
{
    public function pause(FixedRoute $fixedRoute): JsonResponse
    {
        if ($fixedRoute->driver_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para pausar esta rota.');
        }

        $fixedRoute->update(['status' => 'paused']);

        return response()->json(['message' => 'Rota pausada.']);
    }
}
