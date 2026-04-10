@extends('layouts.app')

@section('title', 'Avaliar Viagem')

@section('content')

<div class="max-w-sm mx-auto">

    {{-- Topo --}}
    <div class="text-center mb-8 pt-4">
        <div class="text-5xl mb-3">🌟</div>
        <h2 class="text-2xl font-bold text-gray-900">Avaliar viagem</h2>
        <p class="text-sm text-gray-500 mt-1">
            @if($role === 'passenger')
                Como foi o motorista durante a viagem?
            @else
                Como foi o passageiro durante a viagem?
            @endif
        </p>
    </div>

    {{-- Card do avaliado --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 mb-6 flex items-center gap-4">
        <img src="{{ $ratee->avatar ?? '' }}"
             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($ratee->name) }}&background=2563eb&color=fff&size=64'"
             alt="Avatar" class="w-14 h-14 rounded-full object-cover border-2 border-blue-100">
        <div>
            <p class="font-semibold text-gray-900">{{ $ratee->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">
                @if($role === 'passenger') Motorista @else Passageiro @endif
            </p>
            @php
                $rideReq = $ride->rideRequest;
            @endphp
            @if($rideReq)
            <p class="text-xs text-gray-500 mt-1 truncate max-w-[200px]">
                {{ $rideReq->origin }} → {{ $rideReq->destination }}
            </p>
            @endif
        </div>
    </div>

    {{-- Formulário de avaliação --}}
    <form method="POST" action="{{ route('rides.rate.store', $ride) }}">
        @csrf

        {{-- Estrelas --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-4">
            <p class="text-sm font-semibold text-gray-700 mb-4 text-center">Toque para avaliar</p>

            <div class="flex justify-center gap-3 mb-2" id="star-row">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" data-star="{{ $i }}"
                        class="star-btn text-5xl leading-none transition-transform hover:scale-110 focus:outline-none text-gray-300"
                        onclick="selectStar({{ $i }})">
                    ★
                </button>
                @endfor
            </div>

            <p id="star-label" class="text-center text-sm text-gray-400 mt-2 h-5"></p>

            <input type="hidden" name="stars" id="stars-input" value="">
        </div>

        {{-- Comentário --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Comentário <span class="font-normal text-gray-400">(opcional)</span>
            </label>
            <textarea name="comment" rows="3" maxlength="500"
                      placeholder="Compartilhe detalhes sobre sua experiência..."
                      class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('comment') }}</textarea>
            <p class="text-xs text-gray-400 text-right mt-1">máx. 500 caracteres</p>
        </div>

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-300 text-red-700 rounded-xl px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <button type="submit" id="submit-btn"
                disabled
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold py-3.5 rounded-xl transition text-sm shadow">
            Enviar Avaliação
        </button>

        <a href="{{ route('dashboard') }}"
           class="block text-center text-sm text-gray-400 hover:text-gray-600 mt-4 transition">
            Pular avaliação
        </a>
    </form>

</div>

@endsection

@push('scripts')
<script>
const labels = ['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'];
const stars  = document.querySelectorAll('.star-btn');
let selected = 0;

function selectStar(n) {
    selected = n;
    document.getElementById('stars-input').value = n;
    document.getElementById('submit-btn').disabled = false;
    document.getElementById('star-label').textContent = labels[n];

    stars.forEach((btn, i) => {
        btn.style.color = (i < n) ? '#f59e0b' : '#d1d5db'; // yellow-400 / gray-300
    });
}

// Hover preview
stars.forEach((btn, i) => {
    btn.addEventListener('mouseenter', () => {
        stars.forEach((b, j) => { b.style.color = j <= i ? '#fbbf24' : '#d1d5db'; });
        document.getElementById('star-label').textContent = labels[i + 1];
    });
    btn.addEventListener('mouseleave', () => {
        stars.forEach((b, j) => { b.style.color = j < selected ? '#f59e0b' : '#d1d5db'; });
        document.getElementById('star-label').textContent = selected ? labels[selected] : '';
    });
});
</script>
@endpush
