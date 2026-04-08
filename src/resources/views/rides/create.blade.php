@extends('layouts.app')

@section('title', 'Solicitar Carona')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}"
           class="text-blue-600 hover:text-blue-800 text-sm transition">
            ← Voltar
        </a>
        <h2 class="text-xl font-bold text-gray-900">Solicitar Carona</h2>
    </div>

    <div id="alert-success" class="hidden mb-4 bg-green-100 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
        Solicitação enviada com sucesso! Aguarde um motorista aceitar.
    </div>
    <div id="alert-error" class="hidden mb-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm"></div>

    <form id="ride-form" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origem</label>
                <input type="text" name="origin" required placeholder="Ex: Rua das Flores, Lavras"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
                <input type="text" name="destination" required placeholder="Ex: UFLA — Campus Universitário"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Coordenadas da Origem</label>
                <input type="text" name="origin_coords" required placeholder="-21.2300,-45.0000"
                       pattern="-?\d+\.\d+,-?\d+\.\d+"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Formato: latitude,longitude</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Coordenadas do Destino</label>
                <input type="text" name="destination_coords" required placeholder="-21.2410,-45.0010"
                       pattern="-?\d+\.\d+,-?\d+\.\d+"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data e Hora</label>
                <input type="datetime-local" name="scheduled_for" required
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assentos Necessários</label>
                <input type="number" name="seats_needed" required min="1" max="6" value="1"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl shadow transition text-sm">
            Solicitar Carona
        </button>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('ride-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const alertSuccess = document.getElementById('alert-success');
    const alertError   = document.getElementById('alert-error');
    alertSuccess.classList.add('hidden');
    alertError.classList.add('hidden');

    const form = e.target;
    const payload = {
        origin:              form.origin.value,
        destination:         form.destination.value,
        origin_coords:       form.origin_coords.value,
        destination_coords:  form.destination_coords.value,
        scheduled_for:       new Date(form.scheduled_for.value).toISOString(),
        seats_needed:        parseInt(form.seats_needed.value),
    };

    const res = await fetch('/rides/request', {
        method:  'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept':        'application/json',
        },
        body: JSON.stringify(payload),
    });

    if (res.status === 201) {
        alertSuccess.classList.remove('hidden');
        form.reset();
    } else {
        const data = await res.json();
        const msgs = data.errors
            ? Object.values(data.errors).flat().join(' ')
            : (data.message ?? 'Erro ao enviar solicitação.');
        alertError.textContent = msgs;
        alertError.classList.remove('hidden');
    }
});
</script>
@endpush
