@extends('layouts.app')

@section('title', 'Dashboard')

@php
$statusLabel = [
    'pending'     => 'Pendente',
    'accepted'    => 'Aceita',
    'in_progress' => 'Em andamento',
    'completed'   => 'Concluída',
    'cancelled'   => 'Cancelada',
    'rejected'    => 'Recusada',
];
@endphp

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">
        Olá, {{ auth()->user()->name }} 👋
    </h2>
    <p class="text-sm text-gray-500 mt-1">
        Você está como <span class="font-medium capitalize">
            {{ auth()->user()->role === 'driver' ? 'motorista' : 'passageiro' }}
        </span>
        &nbsp;·&nbsp; 🪙 {{ auth()->user()->points_balance ?? 0 }} pontos
    </p>
</div>

@if(auth()->user()->role === 'passenger')

    {{-- === PASSAGEIRO === --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Minhas Solicitações</h3>
        <a href="{{ route('rides.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow transition">
            + Solicitar Carona
        </a>
    </div>

    @if($rideRequests->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
            Nenhuma solicitação ainda.
            <a href="{{ route('rides.create') }}" class="text-blue-600 hover:underline ml-1">
                Solicite sua primeira carona!
            </a>
        </div>
    @else
        <div class="space-y-3">
        @foreach($rideRequests as $req)
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                <div>
                    <p class="font-medium text-gray-900">
                        {{ $req->origin }}
                        <span class="text-gray-400 mx-1">→</span>
                        {{ $req->destination }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $req->scheduled_for->format('d/m/Y H:i') }}
                        &nbsp;·&nbsp; {{ $req->seats_needed }} assento(s)
                    </p>
                </div>
                <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full
                    {{ $req->status === 'pending'     ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $req->status === 'accepted'    ? 'bg-green-100 text-green-700'  : '' }}
                    {{ $req->status === 'rejected'    ? 'bg-red-100 text-red-700'      : '' }}
                    {{ $req->status === 'cancelled'   ? 'bg-gray-100 text-gray-500'    : '' }}
                    {{ $req->status === 'in_progress' ? 'bg-blue-100 text-blue-700'    : '' }}
                    {{ $req->status === 'completed'   ? 'bg-purple-100 text-purple-700': '' }}
                ">
                    {{ $statusLabel[$req->status] ?? $req->status }}
                </span>
            </div>
        @endforeach
        </div>
    @endif

@else

    {{-- === MOTORISTA === --}}
    <div class="grid lg:grid-cols-2 gap-6">

        {{-- Solicitações pendentes próximas --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Solicitações Pendentes</h3>

            @if($pendingRequests->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-400 text-sm">
                    Nenhuma solicitação pendente no momento.
                </div>
            @else
                <div class="space-y-3" id="pending-list">
                @foreach($pendingRequests as $req)
                    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4 shadow-sm" id="req-{{ $req->id }}">
                        <p class="font-medium text-gray-900 text-sm">
                            {{ $req->origin }}
                            <span class="text-gray-400 mx-1">→</span>
                            {{ $req->destination }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1 mb-3">
                            {{ $req->scheduled_for->format('d/m/Y H:i') }}
                            &nbsp;·&nbsp; {{ $req->seats_needed }} assento(s)
                            &nbsp;·&nbsp; {{ $req->passenger->name }}
                        </p>
                        <div class="flex gap-2">
                            <button onclick="acceptRide({{ $req->id }})"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-2 rounded-lg transition">
                                Aceitar
                            </button>
                            <button onclick="rejectRide({{ $req->id }})"
                                    class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-2 rounded-lg transition">
                                Recusar
                            </button>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </section>

        {{-- Minhas caronas ativas --}}
        <section>
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Minhas Caronas</h3>

            @if($myRides->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-400 text-sm">
                    Nenhuma carona ativa.
                </div>
            @else
                <div class="space-y-3">
                @foreach($myRides as $ride)
                    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4 shadow-sm">
                        @if($ride->rideRequest)
                        <p class="font-medium text-gray-900 text-sm">
                            {{ $ride->rideRequest->origin }}
                            <span class="text-gray-400 mx-1">→</span>
                            {{ $ride->rideRequest->destination }}
                        </p>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">
                            Passageiro: {{ $ride->passenger?->name ?? '—' }}
                        </p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs font-semibold px-3 py-1 rounded-full
                                {{ $ride->status === 'accepted'    ? 'bg-green-100 text-green-700'  : '' }}
                                {{ $ride->status === 'in_progress' ? 'bg-blue-100 text-blue-700'    : '' }}
                                {{ $ride->status === 'completed'   ? 'bg-purple-100 text-purple-700': '' }}
                                {{ $ride->status === 'cancelled'   ? 'bg-gray-100 text-gray-500'    : '' }}
                            ">
                                {{ $statusLabel[$ride->status] ?? $ride->status }}
                            </span>
                            @if(in_array($ride->status, ['accepted', 'in_progress']))
                                <button onclick="cancelRide({{ $ride->id }})"
                                        class="text-xs text-red-500 hover:text-red-700 transition">
                                    Cancelar
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
            @endif

            {{-- Veículo --}}
            @if(auth()->user()->vehicle)
            <div class="mt-4 bg-white rounded-xl border border-gray-200 px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">
                        {{ auth()->user()->vehicle->model }} — {{ auth()->user()->vehicle->plate }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ auth()->user()->vehicle->color }}, {{ auth()->user()->vehicle->year }}
                        &nbsp;·&nbsp; {{ auth()->user()->vehicle->seats }} assentos
                    </p>
                </div>
                <a href="{{ route('vehicles.edit', auth()->user()->vehicle) }}"
                   class="text-blue-600 hover:underline text-sm">
                    Editar
                </a>
            </div>
            @endif
        </section>

    </div>

@endif

{{-- Cancel modal --}}
<div id="cancel-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h4 class="font-semibold text-gray-900 mb-3">Motivo do cancelamento</h4>
        <textarea id="cancel-reason" rows="3" placeholder="Informe o motivo..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
        <div class="flex gap-2 mt-4">
            <button onclick="document.getElementById('cancel-modal').classList.add('hidden')"
                    class="flex-1 border border-gray-300 rounded-lg py-2 text-sm text-gray-600 hover:bg-gray-50 transition">
                Voltar
            </button>
            <button onclick="confirmCancel()"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white rounded-lg py-2 text-sm font-medium transition">
                Confirmar
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let cancelRideId = null;

async function acceptRide(id) {
    const res = await fetch(`/rides/${id}/accept`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    });
    if (res.ok) {
        document.getElementById(`req-${id}`)?.remove();
        location.reload();
    } else {
        const data = await res.json();
        alert(data.message ?? 'Erro ao aceitar carona.');
    }
}

async function rejectRide(id) {
    const res = await fetch(`/rides/${id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    });
    if (res.ok) {
        document.getElementById(`req-${id}`)?.remove();
    } else {
        alert('Erro ao recusar solicitação.');
    }
}

function cancelRide(id) {
    cancelRideId = id;
    document.getElementById('cancel-reason').value = '';
    document.getElementById('cancel-modal').classList.remove('hidden');
}

async function confirmCancel() {
    const reason = document.getElementById('cancel-reason').value.trim();
    if (!reason) { alert('Informe o motivo.'); return; }

    const res = await fetch(`/rides/${cancelRideId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason }),
    });

    document.getElementById('cancel-modal').classList.add('hidden');
    if (res.ok) {
        location.reload();
    } else {
        alert('Erro ao cancelar carona.');
    }
}
</script>
@endpush
