@extends('layouts.app')

@section('title', 'Cadastrar Veículo')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Voltar</a>
        <h2 class="text-xl font-bold text-gray-900">Cadastrar Veículo</h2>
    </div>

    <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm">
        Cadastre seu veículo para poder oferecer caronas como motorista.
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('vehicles.store') }}"
          class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                <input type="text" name="model" required value="{{ old('model') }}"
                       placeholder="Ex: Fiat Uno"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                <input type="text" name="plate" required value="{{ old('plate') }}"
                       placeholder="ABC1234"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                <input type="text" name="color" required value="{{ old('color') }}"
                       placeholder="Branco"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                <input type="number" name="year" required value="{{ old('year') }}"
                       min="1990" max="{{ date('Y') + 1 }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assentos</label>
                <input type="number" name="seats" required value="{{ old('seats', 4) }}"
                       min="1" max="8"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl shadow transition">
            Cadastrar Veículo
        </button>
    </form>
</div>

@endsection
