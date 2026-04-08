@extends('layouts.app')

@section('title', 'Editar Veículo')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}"
           class="text-blue-600 hover:text-blue-800 text-sm transition">
            ← Voltar
        </a>
        <h2 class="text-xl font-bold text-gray-900">Meu Veículo</h2>
    </div>

    <div id="alert-success" class="hidden mb-4 bg-green-100 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
        Veículo atualizado com sucesso!
    </div>
    <div id="alert-error" class="hidden mb-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm"></div>

    <form id="vehicle-form" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                <input type="text" name="model" required
                       value="{{ $vehicle->model }}"
                       placeholder="Ex: Fiat Uno"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                <input type="text" name="plate" required
                       value="{{ $vehicle->plate }}"
                       placeholder="ABC-1234"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                <input type="text" name="color" required
                       value="{{ $vehicle->color }}"
                       placeholder="Branco"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                <input type="number" name="year" required
                       value="{{ $vehicle->year }}"
                       min="1990" max="{{ date('Y') + 1 }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assentos</label>
                <input type="number" name="seats" required
                       value="{{ $vehicle->seats }}"
                       min="1" max="8"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl shadow transition text-sm">
            Salvar Alterações
        </button>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('vehicle-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const alertSuccess = document.getElementById('alert-success');
    const alertError   = document.getElementById('alert-error');
    alertSuccess.classList.add('hidden');
    alertError.classList.add('hidden');

    const form = e.target;
    const payload = {
        model:  form.model.value,
        plate:  form.plate.value.toUpperCase(),
        color:  form.color.value,
        year:   parseInt(form.year.value),
        seats:  parseInt(form.seats.value),
    };

    const res = await fetch('/vehicles/{{ $vehicle->id }}', {
        method:  'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept':        'application/json',
        },
        body: JSON.stringify(payload),
    });

    if (res.ok) {
        alertSuccess.classList.remove('hidden');
    } else {
        const data = await res.json();
        alertError.textContent = data.message ?? 'Erro ao salvar veículo.';
        alertError.classList.remove('hidden');
    }
});
</script>
@endpush
