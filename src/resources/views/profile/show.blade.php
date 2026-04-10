@extends('layouts.app')

@section('title', $isOwnProfile ? 'Minha Reputação' : 'Perfil de ' . $user->name)

@section('content')

@php
    $starsLabel = ['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'];
    $roleLabel  = ['driver' => 'Motorista', 'passenger' => 'Caronista'];
@endphp

<div class="max-w-xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition flex-shrink-0">← Dashboard</a>
        <h2 class="text-xl font-bold text-gray-900 truncate">
            {{ $isOwnProfile ? 'Minha Reputação' : 'Perfil de ' . $user->name }}
        </h2>
    </div>

    {{-- Card de identidade --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
        <img src="{{ $user->avatar ?? '' }}"
             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=2563eb&color=fff&size=80'"
             alt="Avatar"
             class="w-16 h-16 rounded-full object-cover border-2 border-blue-100 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <p class="text-lg font-bold text-gray-900 truncate">{{ $user->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Membro desde {{ $user->created_at->format('M/Y') }}</p>
            <div class="flex flex-wrap gap-2 mt-2">
                @if($totalAsDriver > 0)
                <span class="text-xs bg-blue-100 text-blue-700 font-medium px-2 py-0.5 rounded-full">🚗 Motorista</span>
                @endif
                @if($totalAsPassenger > 0)
                <span class="text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">🙋 Caronista</span>
                @endif
            </div>
        </div>
        @if($avgStars > 0)
        <div class="text-center flex-shrink-0">
            <p class="text-3xl font-bold text-gray-900">{{ number_format($avgStars, 1) }}</p>
            <div class="flex justify-center gap-0.5 my-0.5">
                @for($i = 1; $i <= 5; $i++)
                <span class="text-sm {{ $i <= round($avgStars) ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
                @endfor
            </div>
            <p class="text-xs text-gray-400">{{ $totalRatings }} avaliação{{ $totalRatings !== 1 ? 'ões' : '' }}</p>
        </div>
        @endif
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $totalAsDriver }}</p>
            <p class="text-xs text-gray-500 mt-0.5 leading-tight">Viagens<br>como motorista</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $totalAsPassenger }}</p>
            <p class="text-xs text-gray-500 mt-0.5 leading-tight">Viagens<br>como caronista</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 text-center">
            <p class="text-2xl font-bold text-yellow-500">{{ $totalRatings }}</p>
            <p class="text-xs text-gray-500 mt-0.5 leading-tight">Avaliações<br>recebidas</p>
        </div>
    </div>

    {{-- Distribuição de estrelas --}}
    @if($totalRatings > 0)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Distribuição das avaliações</h3>

        <div class="flex items-start gap-4">
            {{-- Nota grande à esquerda --}}
            <div class="text-center flex-shrink-0">
                <p class="text-5xl font-black text-gray-900">{{ number_format($avgStars, 1) }}</p>
                <div class="flex justify-center gap-0.5 my-1">
                    @for($i = 1; $i <= 5; $i++)
                    <span class="text-lg {{ $i <= round($avgStars) ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
                    @endfor
                </div>
                <p class="text-xs text-gray-400">de 5</p>
            </div>

            {{-- Barras à direita --}}
            <div class="flex-1 space-y-1.5">
                @for($i = 5; $i >= 1; $i--)
                @php
                    $count = $starBreakdown[$i] ?? 0;
                    $pct   = $totalRatings > 0 ? round($count / $totalRatings * 100) : 0;
                @endphp
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 w-4 text-right flex-shrink-0">{{ $i }}</span>
                    <span class="text-yellow-400 text-xs flex-shrink-0">★</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $pct > 0 ? 'bg-yellow-400' : 'bg-gray-100' }} transition-all"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-6 text-right flex-shrink-0">{{ $count }}</span>
                </div>
                @endfor
            </div>
        </div>
    </div>
    @endif

    {{-- Avaliações recentes com comentário --}}
    @if($comments->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">O que dizem sobre {{ $isOwnProfile ? 'você' : $user->name }}</h3>

        <div class="space-y-4">
            @foreach($comments as $r)
            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                <div class="flex items-start gap-3">
                    <img src="{{ $r->rater?->avatar ?? '' }}"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($r->rater?->name ?? '?') }}&background=e5e7eb&color=374151&size=40'"
                         alt="Avatar"
                         class="w-8 h-8 rounded-full object-cover flex-shrink-0 mt-0.5">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $r->rater?->name ?? 'Usuário' }}</p>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @for($i = 1; $i <= 5; $i++)
                                <span class="text-sm {{ $i <= $r->stars ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
                                @endfor
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $roleLabel[$r->role] ?? $r->role }}
                            · {{ $r->created_at->diffForHumans() }}
                            @if($r->ride?->rideRequest)
                            · <span class="truncate">{{ Str::limit($r->ride->rideRequest->origin, 20) }} → {{ Str::limit($r->ride->rideRequest->destination, 20) }}</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-700 mt-1.5 leading-relaxed">{{ $r->comment }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Avaliações sem comentário (últimas) --}}
    @elseif($totalRatings > 0)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Avaliações recentes</h3>
        <div class="space-y-3">
            @foreach($recentRatings as $r)
            <div class="flex items-center gap-3">
                <img src="{{ $r->rater?->avatar ?? '' }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($r->rater?->name ?? '?') }}&background=e5e7eb&color=374151&size=40'"
                     class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $r->rater?->name ?? 'Usuário' }}</p>
                    <p class="text-xs text-gray-400">{{ $roleLabel[$r->role] ?? '' }} · {{ $r->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex gap-0.5 flex-shrink-0">
                    @for($i = 1; $i <= 5; $i++)
                    <span class="text-sm {{ $i <= $r->stars ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
        <p class="text-4xl mb-3">⭐</p>
        <p class="text-gray-500 text-sm">
            @if($isOwnProfile)
                Você ainda não recebeu avaliações. Complete viagens para acumular reputação!
            @else
                Este usuário ainda não recebeu avaliações.
            @endif
        </p>
    </div>
    @endif

</div>

@endsection
