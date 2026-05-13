@extends('layouts.app')

@section('title', 'Dashboard')

@php
$statusLabel = [
    'pending'     => 'Pendente',
    'accepted'    => 'Aceita',
    'in_progress' => 'Em andamento',
    'completed'   => 'Finalizada',
    'cancelled'   => 'Cancelada',
    'rejected'    => 'Recusada',
];
@endphp

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Olá, {{ auth()->user()->name }}</h2>
    <p class="text-sm text-gray-500 mt-1">
        Modo: <span class="font-medium capitalize">
            {{ session('user_mode', 'passenger') === 'driver' ? 'Motorista' : 'Caronista' }}
        </span>
        @if(session('user_mode', 'passenger') === 'driver')
        &nbsp;·&nbsp; 🪙 {{ auth()->user()->points_balance ?? 0 }} pontos
        @endif
    </p>
</div>

@if(session('success'))
    <div class="mb-5 bg-green-100 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- ══════════════════════════ PASSAGEIRO ══════════════════════════ --}}
@if(session('user_mode', 'passenger') !== 'driver')
<div data-passenger-dashboard>

    {{-- Carona em andamento (banner fixo no topo) --}}
    @if(isset($activeRequest) && $activeRequest)
    @php
        $aRide        = $activeRequest->ride;
        $aDriver      = $aRide->driver;
        $aVehicle     = $aRide->vehicle ?? $aDriver?->vehicle;
        $aIsMoving    = $aRide->status === 'in_progress';
        $aFixedRoute  = $activeRequest->fixedRoute;
        $aRoutePaused = $aFixedRoute && $aFixedRoute->status === 'paused';
        $aIsFixedRoute = $aFixedRoute !== null;

        if ($aIsMoving) {
            $aBannerClass = 'bg-green-50 border-green-400';
            $aLabelClass  = 'text-green-800';
            $aBadgeClass  = 'bg-green-600 text-white';
            $aLabel       = '📍 Viagem em andamento';
        } elseif ($aRoutePaused) {
            $aBannerClass = 'bg-orange-50 border-orange-400';
            $aLabelClass  = 'text-orange-800';
            $aBadgeClass  = 'bg-orange-500 text-white';
            $aLabel       = '⏸ Rota pausada pelo motorista';
        } elseif ($aIsFixedRoute) {
            $aBannerClass = 'bg-indigo-50 border-indigo-400';
            $aLabelClass  = 'text-indigo-800';
            $aBadgeClass  = 'bg-indigo-600 text-white';
            $aLabel       = '🗓 Vaga confirmada na rota fixa';
        } else {
            $aBannerClass = 'bg-blue-50 border-blue-400';
            $aLabelClass  = 'text-blue-800';
            $aBadgeClass  = 'bg-blue-600 text-white';
            $aLabel       = '✅ Vaga confirmada, aguardando início';
        }
    @endphp
    <a id="active-request-banner" href="{{ route('rides.track', $activeRequest) }}"
       class="flex items-center gap-4 mb-5 p-4 rounded-2xl border-2 shadow-md transition hover:shadow-lg {{ $aBannerClass }}">
        <span class="relative flex-shrink-0">
            <span class="text-3xl">🚗</span>
            @if($aIsMoving)
            <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-green-500 border-2 border-white animate-ping"></span>
            @endif
        </span>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-sm {{ $aLabelClass }}">{{ $aLabel }}</p>
            <p class="text-xs text-gray-600 truncate mt-0.5">
                {{ $activeRequest->origin }} → {{ $activeRequest->destination }}
            </p>
            @if($aDriver)
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $aDriver->name }}
                @if($aVehicle) · {{ $aVehicle->model }} {{ $aVehicle->color }} @endif
            </p>
            @endif
        </div>
        <span class="flex-shrink-0 text-xs font-semibold px-3 py-1 rounded-full {{ $aBadgeClass }}">
            Acompanhar →
        </span>
    </a>
    @endif

    {{-- Tabs de navegação --}}
    <div class="flex gap-1 mb-5 bg-gray-100 rounded-xl p-1">
        <button id="tab-offers" onclick="showTab('offers')"
                class="tab-btn flex-1 text-sm font-semibold py-2 rounded-lg transition bg-white text-blue-700 shadow">
            Ofertas de Carona
        </button>
        <button id="tab-mine" onclick="showTab('mine')"
                class="tab-btn flex-1 text-sm font-semibold py-2 rounded-lg transition text-gray-500 hover:text-gray-700">
            Minhas Solicitações
        </button>
    </div>

    {{-- ── Tab: Ofertas de Carona ── --}}
    <div id="panel-offers">

        {{-- CTA: solicitar avulsa --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-5 mb-5 flex items-center justify-between shadow">
            <div>
                <p class="text-white font-bold text-base">Precisa de carona agora?</p>
                <p class="text-blue-100 text-xs mt-0.5">Solicite e aguarde um motorista aceitar</p>
            </div>
            <a href="{{ route('rides.create') }}"
               class="flex-shrink-0 bg-white text-blue-700 font-semibold text-xs px-4 py-2.5 rounded-xl shadow hover:bg-blue-50 transition">
                + Solicitar
            </a>
        </div>

        {{-- Rotas fixas disponíveis --}}
        <h3 class="text-base font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
            Rotas fixas recorrentes
        </h3>

        <div id="routes-list" class="space-y-3 mb-5">
        @if(isset($availableFixedRoutes) && $availableFixedRoutes->isNotEmpty())
            @foreach($availableFixedRoutes as $fr)
            @php
                $seatsLeft = max(0, $fr->available_seats - $fr->accepted_count);
                $myFrReq   = $myFixedRouteReqMap[$fr->id] ?? null;
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4" data-route-id="{{ $fr->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <img src="{{ $fr->driver->avatar ?? '' }}"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($fr->driver->name) }}&background=7c3aed&color=fff&size=32'"
                                 class="w-7 h-7 rounded-full border border-gray-200">
                            <button onclick="showReputation({{ $fr->driver->id }})"
                                    class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                {{ $fr->driver->name }}
                            </button>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $fr->origin }}
                            <span class="text-gray-400 mx-1 font-normal">→</span>
                            {{ $fr->destination }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ \Carbon\Carbon::parse($fr->departure_time)->format('H:i') }}
                            &nbsp;·&nbsp; {{ $fr->days_label }}
                            &nbsp;·&nbsp;
                            <span class="{{ $seatsLeft > 0 ? 'text-green-600' : 'text-red-500' }} font-medium">
                                {{ $seatsLeft }} vaga{{ $seatsLeft !== 1 ? 's' : '' }}
                            </span>
                        </p>
                    </div>
                    @if($myFrReq)
                    <a href="{{ route('rides.track', $myFrReq) }}"
                       class="flex-shrink-0 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                        Acompanhar →
                    </a>
                    @else
                    <button onclick="joinRoute({{ $fr->id }}, this)"
                            {{ $seatsLeft <= 0 ? 'disabled' : '' }}
                            class="flex-shrink-0 bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                        Solicitar
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        @else
        <div id="routes-empty" class="bg-white rounded-xl border border-gray-200 p-5 text-center text-gray-400 text-sm">
            Nenhuma rota fixa disponível.
        </div>
        @endif
        </div>{{-- /routes-list --}}

        {{-- Viagens avulsas abertas por motoristas --}}
        <h3 class="text-base font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
            Viagens avulsas (próximas)
        </h3>

        <div class="space-y-3 mb-2" id="trips-list">
        @if(isset($availableTrips) && $availableTrips->isNotEmpty())
            @foreach($availableTrips as $trip)
            @php
                $seatsLeft  = $trip->seats_total - $trip->accepted_count;
                $myTripReq  = $myTripReqMap[$trip->id] ?? null;
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <img src="{{ $trip->driver->avatar ?? '' }}"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($trip->driver->name) }}&background=2563eb&color=fff&size=32'"
                                 class="w-7 h-7 rounded-full border border-gray-200">
                            <button onclick="showReputation({{ $trip->driver->id }})"
                                    class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                {{ $trip->driver->name }}
                            </button>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $trip->origin }}
                            <span class="text-gray-400 mx-1 font-normal">→</span>
                            {{ $trip->destination }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $trip->departs_at->format('d/m H:i') }}
                            &nbsp;·&nbsp;
                            <span class="{{ $seatsLeft > 0 ? 'text-green-600' : 'text-red-500' }} font-medium">
                                {{ $seatsLeft }} vaga{{ $seatsLeft !== 1 ? 's' : '' }}
                            </span>
                        </p>
                    </div>
                    @if($myTripReq)
                    <a href="{{ route('rides.track', $myTripReq) }}"
                       class="flex-shrink-0 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                        Acompanhar →
                    </a>
                    @else
                    <button onclick="joinTrip({{ $trip->id }}, this)"
                            {{ $seatsLeft <= 0 ? 'disabled' : '' }}
                            class="flex-shrink-0 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                        Entrar
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        @else
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-400 text-sm" id="trips-empty">
            Nenhuma viagem avulsa disponível no momento.
        </div>
        @endif
        </div>

    </div>{{-- /panel-offers --}}

    {{-- ── Tab: Minhas Solicitações ── --}}
    <div id="panel-mine" class="hidden">

        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-semibold text-gray-700">Minhas Solicitações</h3>
            <a href="{{ route('rides.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-4 py-2 rounded-lg shadow transition">
                + Nova
            </a>
        </div>

        {{-- Filtros --}}
        <div class="flex gap-1 mb-4 bg-gray-100 rounded-xl p-1 text-xs font-medium">
            <button onclick="filterRequests('active')"   id="req-filter-active"
                    class="flex-1 py-1.5 rounded-lg transition req-filter-btn bg-white text-gray-800 shadow-sm">Ativas</button>
            <button onclick="filterRequests('done')"     id="req-filter-done"
                    class="flex-1 py-1.5 rounded-lg transition req-filter-btn text-gray-500">Finalizadas</button>
            <button onclick="filterRequests('cancelled')" id="req-filter-cancelled"
                    class="flex-1 py-1.5 rounded-lg transition req-filter-btn text-gray-500">Canceladas</button>
            <button onclick="filterRequests('all')"      id="req-filter-all"
                    class="flex-1 py-1.5 rounded-lg transition req-filter-btn text-gray-500">Todas</button>
        </div>

        @if(isset($rideRequests) && $rideRequests->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 text-sm">
                Nenhuma solicitação ainda.
                <a href="{{ route('rides.create') }}" class="text-blue-600 hover:underline ml-1">Solicite sua primeira carona!</a>
            </div>
        @else
            <div id="req-empty-msg" class="hidden bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-400 text-sm">
                Nenhuma solicitação neste filtro.
            </div>
            <div class="space-y-3">
            @foreach($rideRequests ?? [] as $req)
            @php
                // Status efetivo: rideRequest.status pode estar desatualizado em relação ao ride
                $rideActualStatus = $req->ride?->status;
                $eff = $req->status;
                if ($eff === 'accepted' && $rideActualStatus === 'in_progress') $eff = 'in_progress';
                if ($eff === 'accepted' && $rideActualStatus === 'completed')   $eff = 'completed';
                if ($eff === 'accepted' && $rideActualStatus === 'cancelled')   $eff = 'cancelled';

                $filterGroup = match(true) {
                    in_array($eff, ['pending','accepted','in_progress']) => 'active',
                    $eff === 'completed'                                 => 'done',
                    default                                              => 'cancelled',
                };
                $canTrack = in_array($eff, ['pending','accepted','in_progress']);
                $myRating = $eff === 'completed' && $req->ride ? $req->ride->ratings->first() : null;
                $canRate  = $eff === 'completed' && $req->ride && !$myRating;
            @endphp
                <div class="bg-white rounded-xl border border-gray-200 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm req-card"
                     data-filter="{{ $filterGroup }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            @if($req->trip_id)
                                <span class="text-xs bg-blue-100 text-blue-600 font-medium px-2 py-0.5 rounded-full">Viagem ofertada</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full">Avulsa</span>
                            @endif
                        </div>
                        <p class="font-medium text-gray-900 truncate">
                            {{ $req->origin }} <span class="text-gray-400 mx-1">→</span> {{ $req->destination }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $req->scheduled_for->format('d/m/Y H:i') }}
                            &nbsp;·&nbsp; {{ $req->seats_needed }} assento(s)
                        </p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($canTrack)
                            <a href="{{ route('rides.track', $req) }}"
                               class="text-xs text-blue-600 hover:underline font-medium">Acompanhar</a>
                        @endif
                        @if($canRate)
                            <a href="{{ route('rides.rate', $req->ride) }}"
                               class="text-xs text-yellow-600 hover:underline font-medium">⭐ Avaliar</a>
                        @elseif($myRating)
                            <span class="text-xs text-yellow-500 font-medium">
                                {{ str_repeat('★', $myRating->stars) }}{{ str_repeat('☆', 5 - $myRating->stars) }}
                            </span>
                        @endif
                        <span class="text-xs font-semibold px-3 py-1 rounded-full
                            {{ $eff === 'pending'     ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $eff === 'accepted'    ? 'bg-green-100 text-green-700'   : '' }}
                            {{ $eff === 'rejected'    ? 'bg-red-100 text-red-700'       : '' }}
                            {{ $eff === 'cancelled'   ? 'bg-gray-100 text-gray-500'     : '' }}
                            {{ $eff === 'in_progress' ? 'bg-blue-100 text-blue-700'     : '' }}
                            {{ $eff === 'completed'   ? 'bg-purple-100 text-purple-700' : '' }}
                        ">{{ $statusLabel[$eff] ?? $eff }}</span>
                    </div>
                </div>
            @endforeach
            </div>
        @endif

    </div>{{-- /panel-mine --}}

</div>{{-- /data-passenger-dashboard --}}

{{-- ══════════════════════════ MOTORISTA ══════════════════════════ --}}
@else

@php
    $driverUser = auth()->user();
    $pts = $driverUser->points_balance ?? 0;

    $levels = [
        [
            'name' => 'Iniciante', 'min' => 0,   'max' => 49,  'icon' => '🌱',
            'color' => 'gray',   'bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'border' => 'border-gray-300',  'bar' => 'bg-gray-400',
            'benefits'     => ['Acesso completo à plataforma VaiJunto', 'Suporte padrão por e-mail', 'Perfil público com avaliações'],
            'uni_benefits' => ['Reconhecimento no mural de sustentabilidade da UFLA', 'Acesso ao grupo de mobilidade estudantil'],
        ],
        [
            'name' => 'Bronze', 'min' => 50,  'max' => 149, 'icon' => '🥉',
            'color' => 'amber',  'bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'border' => 'border-amber-300', 'bar' => 'bg-amber-500',
            'benefits'     => ['Badge Bronze no perfil', '5% de desconto em parceiros VaiJunto', 'Prioridade básica nas solicitações avulsas'],
            'uni_benefits' => ['5% de desconto no RU (Restaurante Universitário)', 'Certificado digital de colaboração em mobilidade sustentável', 'Destaque no mural de sustentabilidade da UFLA'],
        ],
        [
            'name' => 'Prata', 'min' => 150, 'max' => 349, 'icon' => '🥈',
            'color' => 'slate',  'bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'border' => 'border-slate-300', 'bar' => 'bg-slate-500',
            'benefits'     => ['Badge Prata no perfil', '10% de desconto em parceiros VaiJunto', 'Prioridade média nas solicitações', 'Destaque nas buscas de carona'],
            'uni_benefits' => ['10% de desconto no RU', '5% de desconto na xerografia e impressão da biblioteca', 'Prioridade em vagas de estacionamento conveniadas', 'Menção honrosa no relatório anual de sustentabilidade da UFLA'],
        ],
        [
            'name' => 'Ouro', 'min' => 350, 'max' => 699, 'icon' => '🥇',
            'color' => 'yellow', 'bg' => 'bg-yellow-50',  'text' => 'text-yellow-700', 'border' => 'border-yellow-300', 'bar' => 'bg-yellow-500',
            'benefits'     => ['Badge Ouro no perfil', '15% de desconto em parceiros VaiJunto', 'Alta prioridade nas solicitações', 'Destaque premium nas buscas', 'Acesso antecipado a novas funcionalidades'],
            'uni_benefits' => ['15% de desconto no RU', '10% de desconto na biblioteca (xerografia, encadernação)', 'Vaga reservada no estacionamento conveniado em dias de semana', 'Convite para eventos e workshops de mobilidade da UFLA', 'Carta de reconhecimento da Pró-Reitoria de Extensão'],
        ],
        [
            'name' => 'Platina', 'min' => 700, 'max' => null, 'icon' => '💎',
            'color' => 'blue',   'bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'border' => 'border-blue-300',  'bar' => 'bg-blue-600',
            'benefits'     => ['Badge Platina exclusiva no perfil', '20% de desconto em parceiros VaiJunto', 'Máxima prioridade nas solicitações', 'Destaque premium nas buscas', 'Suporte prioritário dedicado', 'Participação no programa de feedback exclusivo'],
            'uni_benefits' => ['20% de desconto no RU', '15% de desconto em todos os serviços da biblioteca', 'Vaga reservada diária no estacionamento conveniado', 'Acesso VIP a todos os eventos de sustentabilidade da UFLA', 'Certificado oficial da Reitoria como Agente de Mobilidade Sustentável', 'Participação no conselho estudantil de mobilidade da UFLA'],
        ],
    ];

    $currentLevel = $levels[0];
    $nextLevel    = $levels[1];
    foreach ($levels as $i => $lvl) {
        if ($pts >= $lvl['min']) {
            $currentLevel = $lvl;
            $nextLevel    = $levels[$i + 1] ?? null;
        }
    }

    $progressPct = 0;
    if ($nextLevel) {
        $range = $nextLevel['min'] - $currentLevel['min'];
        $done  = $pts - $currentLevel['min'];
        $progressPct = $range > 0 ? min(100, round($done / $range * 100)) : 100;
    } else {
        $progressPct = 100;
    }

    // Índice do nível atual — usado no JS do @push
    $activeLevelIdx = 0;
    foreach ($levels as $li => $lvl) {
        if ($lvl['name'] === $currentLevel['name']) { $activeLevelIdx = $li; break; }
    }
@endphp

    {{-- Prompt cadastro de veículo --}}
    @if(!$driverUser->vehicle)
    <div class="mb-5 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-4 flex items-start gap-3">
        <span class="text-2xl flex-shrink-0">🚗</span>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm">Cadastre seu veículo para oferecer caronas</p>
            <p class="text-xs mt-0.5 opacity-75">Você precisa de um veículo cadastrado para publicar viagens.</p>
        </div>
        <a href="{{ route('vehicles.create') }}"
           class="flex-shrink-0 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
            Cadastrar
        </a>
    </div>
    @endif

    {{-- Header do painel --}}
    <div class="mb-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h3 class="text-lg font-semibold text-gray-800 flex-shrink-0">Painel do Motorista</h3>
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('routes.create') }}"
                   class="bg-gray-700 hover:bg-gray-800 text-white text-xs font-semibold px-3 py-2 rounded-lg shadow transition whitespace-nowrap">
                    + Rota Fixa
                </a>
                <a href="{{ route('trips.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-2 rounded-lg shadow transition whitespace-nowrap">
                    + Viagem Avulsa
                </a>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
            <button id="dtab-activity" onclick="showDriverTab('activity')"
                    class="dtab-btn flex-1 text-xs font-semibold py-2 rounded-lg transition bg-white text-blue-700 shadow">
                Atividade
            </button>
            <button id="dtab-requests" onclick="showDriverTab('requests')"
                    class="dtab-btn flex-1 text-xs font-semibold py-2 rounded-lg transition text-gray-500 hover:text-gray-700 relative">
                Pedidos
                @php $totalPending = ($pendingRequests ?? collect())->count(); @endphp
                @if($totalPending > 0)
                <span class="req-badge absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $totalPending }}</span>
                @endif
            </button>
            <button id="dtab-rides" onclick="showDriverTab('rides')"
                    class="dtab-btn flex-1 text-xs font-semibold py-2 rounded-lg transition text-gray-500 hover:text-gray-700">
                Corridas
            </button>
            <button id="dtab-points" onclick="showDriverTab('points')"
                    class="dtab-btn flex-1 text-xs font-semibold py-2 rounded-lg transition text-gray-500 hover:text-gray-700">
                Pontos
            </button>
        </div>
    </div>

    {{-- ── TAB: Atividade ── --}}
    <div id="dpanel-activity" class="space-y-5">

        {{-- Rotas Fixas --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Rotas Fixas</h3>
            @if(isset($myFixedRoutes) && $myFixedRoutes->isNotEmpty())
                <div class="space-y-2">
                @foreach($myFixedRoutes as $fr)
                <a href="{{ route('routes.show', $fr) }}"
                   class="flex items-center gap-3 bg-white rounded-xl border border-gray-200 px-4 py-3 shadow-sm hover:border-blue-300 transition"
                   data-fr-id="{{ $fr->id }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $fr->origin }}
                            <span class="text-gray-400 font-normal">→</span>
                            {{ $fr->destination }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5 truncate">
                            {{ \Carbon\Carbon::parse($fr->departure_time)->format('H:i') }}
                            · {{ $fr->days_label }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $fr->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-600' }}">
                            {{ $fr->status === 'active' ? 'Ativa' : 'Pausada' }}
                        </span>
                        <span class="fr-pending-badge text-xs text-blue-700 font-medium {{ $fr->pending_count === 0 ? 'hidden' : '' }}"
                              data-count="{{ $fr->pending_count }}">
                            {{ $fr->pending_count }} pendente{{ $fr->pending_count > 1 ? 's' : '' }}
                        </span>
                    </div>
                </a>
                @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center text-gray-400 text-sm">
                    Nenhuma rota fixa.
                    <a href="{{ route('routes.create') }}" class="text-blue-600 hover:underline ml-1">Criar rota</a>
                </div>
            @endif
        </div>

        {{-- Viagens Avulsas --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Viagens Avulsas</h3>
            @if(isset($myTrips) && $myTrips->isNotEmpty())
                <div class="space-y-2">
                @foreach($myTrips as $trip)
                @php $within24h = $trip->departs_at->lte(now()->addHours(24)); @endphp
                <a href="{{ route('trips.show', $trip) }}" data-trip-id="{{ $trip->id }}"
                   class="flex items-center gap-3 bg-white rounded-xl border px-4 py-3 shadow-sm hover:border-blue-300 transition
                          {{ $within24h ? 'border-amber-300 bg-amber-50' : 'border-gray-200' }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $trip->origin }}
                            <span class="text-gray-400 font-normal">→</span>
                            {{ $trip->destination }}
                        </p>
                        <p class="trip-info-p text-xs text-gray-500 mt-0.5">
                            {{ $trip->departs_at->format('d/m H:i') }}
                            · {{ $trip->seats_total }} vagas
                            @if($trip->pending_count > 0)
                            · <span class="trip-pending-count text-blue-600 font-medium" data-count="{{ $trip->pending_count }}">{{ $trip->pending_count }} pendente{{ $trip->pending_count > 1 ? 's' : '' }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                            {{ $trip->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $trip->status === 'open' ? 'Aberta' : 'Lotada' }}
                        </span>
                    </div>
                </a>
                @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center text-gray-400 text-sm">
                    Nenhuma viagem publicada.
                    <a href="{{ route('trips.create') }}" class="text-blue-600 hover:underline ml-1">Ofereça uma carona</a>
                </div>
            @endif
        </div>

        {{-- Caronas Ativas --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Caronas Ativas</h3>
            @if(isset($myRides) && $myRides->isNotEmpty())
                <div class="space-y-2">
                @foreach($myRides as $ride)
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 shadow-sm">
                    @if($ride->rideRequest)
                    <p class="text-sm font-semibold text-gray-900 truncate">
                        {{ $ride->rideRequest->origin }}
                        <span class="text-gray-400 font-normal">→</span>
                        {{ $ride->rideRequest->destination }}
                    </p>
                    @endif
                    <p class="text-xs text-gray-500 mt-0.5 truncate">Passageiro: {{ $ride->passenger?->name ?? '—' }}</p>
                    <div class="flex items-center justify-between mt-2 gap-2">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0
                            {{ $ride->status === 'accepted'    ? 'bg-green-100 text-green-700' : '' }}
                            {{ $ride->status === 'in_progress' ? 'bg-blue-100 text-blue-700'   : '' }}">
                            {{ $statusLabel[$ride->status] ?? $ride->status }}
                        </span>
                        @if(in_array($ride->status, ['accepted','in_progress']))
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <a href="{{ route('rides.drive', $ride) }}"
                               class="text-xs text-blue-600 hover:underline font-medium">Gerenciar</a>
                            <button onclick="cancelRide({{ $ride->id }})"
                                    class="text-xs text-red-500 hover:text-red-700 transition">Cancelar</button>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center text-gray-400 text-sm">
                    Nenhuma carona ativa.
                </div>
            @endif
        </div>

        {{-- Veículo --}}
        @if($driverUser->vehicle)
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 shadow-sm flex items-center gap-3">
            <span class="text-2xl flex-shrink-0">🚗</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">
                    {{ $driverUser->vehicle->model }} · {{ $driverUser->vehicle->plate }}
                </p>
                <p class="text-xs text-gray-500 truncate">
                    {{ $driverUser->vehicle->color }}, {{ $driverUser->vehicle->year }}
                    · {{ $driverUser->vehicle->seats }} assentos
                </p>
            </div>
            <a href="{{ route('vehicles.edit', $driverUser->vehicle) }}"
               class="flex-shrink-0 text-xs text-blue-600 hover:underline font-medium">Editar</a>
        </div>
        @endif

    </div>{{-- /dpanel-activity --}}

    {{-- ── TAB: Pedidos ── --}}
    <div id="dpanel-requests" class="hidden space-y-5">

        {{-- Solicitações avulsas --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Solicitações Avulsas</h3>
            <div class="space-y-3" id="pending-list">
            @forelse($pendingRequests ?? [] as $req)
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-4 shadow-sm" id="req-{{ $req->id }}">
                <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ $req->origin }} <span class="text-gray-400 font-normal">→</span> {{ $req->destination }}
                </p>
                <p class="text-xs text-gray-500 mt-1 truncate">
                    {{ $req->scheduled_for->format('d/m H:i') }}
                    · {{ $req->seats_needed }} assento(s)
                </p>
                <button onclick="showReputation({{ $req->passenger_id }})"
                        class="text-xs text-gray-700 mt-0.5 mb-3 flex items-center gap-1 hover:text-blue-600 transition text-left">
                    {{ $req->passenger->name }}
                    @if(($req->passenger->ratings_received_count ?? 0) > 0)
                        <span class="text-yellow-500 ml-1">★</span>
                        <span>{{ number_format($req->passenger->ratings_received_avg_stars ?? 0, 1) }}</span>
                        <span class="text-gray-400">({{ $req->passenger->ratings_received_count }})</span>
                    @else
                        <span class="text-gray-400 ml-1">· Novo usuário</span>
                    @endif
                </button>
                <div class="flex gap-2">
                    <button onclick="acceptRide({{ $req->id }}, this)"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-2 rounded-lg transition">
                        Aceitar
                    </button>
                    <button onclick="rejectRide({{ $req->id }}, this)"
                            class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-2 rounded-lg transition">
                        Recusar
                    </button>
                </div>
            </div>
            @empty
            <div id="pending-empty" class="bg-white rounded-xl border border-gray-200 p-5 text-center text-gray-400 text-sm">
                Nenhuma solicitação avulsa no momento.
            </div>
            @endforelse
            </div>
        </div>

    </div>{{-- /dpanel-requests --}}

    {{-- ── TAB: Corridas ── --}}
    <div id="dpanel-rides" class="hidden space-y-4">

        <div class="flex items-center justify-between mb-1">
            <h3 class="text-base font-semibold text-gray-700">Minhas Corridas</h3>
        </div>

        {{-- Filtros --}}
        <div class="flex gap-1 bg-gray-100 rounded-xl p-1 text-xs font-medium">
            <button onclick="filterDriverRides('active')"    id="dride-filter-active"
                    class="flex-1 py-1.5 rounded-lg transition dride-filter-btn bg-white text-gray-800 shadow-sm">Ativas</button>
            <button onclick="filterDriverRides('done')"      id="dride-filter-done"
                    class="flex-1 py-1.5 rounded-lg transition dride-filter-btn text-gray-500">Finalizadas</button>
            <button onclick="filterDriverRides('cancelled')" id="dride-filter-cancelled"
                    class="flex-1 py-1.5 rounded-lg transition dride-filter-btn text-gray-500">Canceladas</button>
            <button onclick="filterDriverRides('all')"       id="dride-filter-all"
                    class="flex-1 py-1.5 rounded-lg transition dride-filter-btn text-gray-500">Todas</button>
        </div>

        @if(($driverRides ?? collect())->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 text-sm">
                Nenhuma corrida realizada ainda.
            </div>
        @else
            <div id="dride-empty-msg" class="hidden bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-400 text-sm">
                Nenhuma corrida neste filtro.
            </div>
            <div class="space-y-3">
            @foreach($driverRides ?? [] as $ride)
            @php
                $driveStatus = $ride->status;
                $dFilterGroup = match(true) {
                    in_array($driveStatus, ['accepted','in_progress']) => 'active',
                    $driveStatus === 'completed'                       => 'done',
                    default                                            => 'cancelled',
                };
                $driveMyRating = $ride->ratings->first();
                $driveCanRate  = $driveStatus === 'completed' && !$driveMyRating;
                $origin      = $ride->rideRequest?->origin      ?? '—';
                $destination = $ride->rideRequest?->destination ?? '—';
                $date        = $ride->rideRequest?->scheduled_for?->format('d/m/Y H:i') ?? $ride->created_at->format('d/m/Y H:i');
            @endphp
                <div class="bg-white rounded-xl border border-gray-200 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm dride-card"
                     data-filter="{{ $dFilterGroup }}">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">
                            {{ $origin }} <span class="text-gray-400 mx-1">→</span> {{ $destination }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $date }}
                            @if($ride->passenger)
                                &nbsp;·&nbsp;
                                <button onclick="showReputation({{ $ride->passenger->id }})"
                                        class="text-blue-600 hover:underline cursor-pointer">{{ $ride->passenger->name }}</button>
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if(in_array($driveStatus, ['accepted','in_progress']))
                            <a href="{{ route('rides.drive', $ride) }}"
                               class="text-xs text-blue-600 hover:underline font-medium">Gerenciar</a>
                        @endif
                        @if($driveCanRate)
                            <a href="{{ route('rides.rate', $ride) }}"
                               class="text-xs text-yellow-600 hover:underline font-medium">⭐ Avaliar</a>
                        @elseif($driveMyRating)
                            <span class="text-xs text-yellow-500 font-medium">
                                {{ str_repeat('★', $driveMyRating->stars) }}{{ str_repeat('☆', 5 - $driveMyRating->stars) }}
                            </span>
                        @endif
                        <span class="text-xs font-semibold px-3 py-1 rounded-full
                            {{ $driveStatus === 'accepted'    ? 'bg-green-100 text-green-700'   : '' }}
                            {{ $driveStatus === 'in_progress' ? 'bg-blue-100 text-blue-700'     : '' }}
                            {{ $driveStatus === 'completed'   ? 'bg-purple-100 text-purple-700' : '' }}
                            {{ $driveStatus === 'cancelled'   ? 'bg-gray-100 text-gray-500'     : '' }}
                        ">{{ $statusLabel[$driveStatus] ?? $driveStatus }}</span>
                    </div>
                </div>
            @endforeach
            </div>
        @endif

    </div>{{-- /dpanel-rides --}}

    {{-- ── TAB: Pontos ── --}}
    <div id="dpanel-points" class="hidden space-y-4">

        {{-- Card de nível atual --}}
        <div class="rounded-2xl border {{ $currentLevel['border'] }} {{ $currentLevel['bg'] }} px-5 py-4">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-3xl flex-shrink-0">{{ $currentLevel['icon'] }}</span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide {{ $currentLevel['text'] }} opacity-70">Nível atual</p>
                        <p class="text-xl font-bold {{ $currentLevel['text'] }} leading-tight">{{ $currentLevel['name'] }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-2xl font-bold {{ $currentLevel['text'] }}">{{ number_format($pts) }}</p>
                    <p class="text-xs {{ $currentLevel['text'] }} opacity-70">pontos</p>
                </div>
            </div>

            {{-- Barra de progresso --}}
            @if($nextLevel)
            <div class="space-y-1">
                <div class="flex justify-between text-xs {{ $currentLevel['text'] }} opacity-75">
                    <span>{{ number_format($pts) }} pts</span>
                    <span>{{ number_format($nextLevel['min']) }} pts → {{ $nextLevel['name'] }} {{ $nextLevel['icon'] }}</span>
                </div>
                <div class="w-full bg-white/50 rounded-full h-2.5">
                    <div class="{{ $currentLevel['bar'] }} h-2.5 rounded-full transition-all"
                         style="width: {{ $progressPct }}%"></div>
                </div>
                <p class="text-xs {{ $currentLevel['text'] }} opacity-60 text-center">
                    Faltam {{ number_format($nextLevel['min'] - $pts) }} pontos para {{ $nextLevel['name'] }}
                </p>
            </div>
            @else
            <div class="text-center">
                <p class="text-xs {{ $currentLevel['text'] }} opacity-75 font-semibold">🏆 Nível máximo alcançado!</p>
            </div>
            @endif
        </div>

        {{-- Como ganhar pontos --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Como ganhar pontos</h3>
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-sm flex-shrink-0 font-bold">+10</span>
                    <p class="text-sm text-gray-700">Completar uma carona</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center text-sm flex-shrink-0 font-bold">+5</span>
                    <p class="text-sm text-gray-700">Receber avaliação 5 estrelas</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm flex-shrink-0 font-bold">+3</span>
                    <p class="text-sm text-gray-700">Oferecer viagem avulsa</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-sm flex-shrink-0 font-bold">+2</span>
                    <p class="text-sm text-gray-700">Aceitar solicitação avulsa</p>
                </div>
            </div>
        </div>

        {{-- Vantagens do nível atual --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                Suas vantagens — {{ $currentLevel['icon'] }} {{ $currentLevel['name'] }}
            </h3>
            <div class="space-y-2">
                @foreach($currentLevel['benefits'] as $benefit)
                <div class="flex items-start gap-2">
                    <span class="text-green-500 flex-shrink-0 mt-0.5">✓</span>
                    <p class="text-sm text-gray-700">{{ $benefit }}</p>
                </div>
                @endforeach
            </div>

            @if($nextLevel)
            <div class="mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 font-semibold mb-2 uppercase tracking-wide">
                    No próximo nível ({{ $nextLevel['icon'] }} {{ $nextLevel['name'] }})
                </p>
                <div class="space-y-1.5">
                    @foreach($nextLevel['benefits'] as $benefit)
                    <div class="flex items-start gap-2 opacity-50">
                        <span class="text-gray-400 flex-shrink-0 mt-0.5">○</span>
                        <p class="text-sm text-gray-500">{{ $benefit }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Todos os níveis (accordion clicável) --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Todos os níveis — clique para ver detalhes</h3>
            <div class="space-y-2" id="levels-accordion">
            @foreach($levels as $li => $lvl)
            @php
                $isActive   = $lvl['name'] === $currentLevel['name'];
                $isUnlocked = $pts >= $lvl['min'];
            @endphp
            <div class="rounded-xl border overflow-hidden {{ $isActive ? $lvl['border'] : 'border-gray-200' }}">
                {{-- Cabeçalho clicável --}}
                <button type="button"
                        onclick="toggleLevel({{ $li }})"
                        class="w-full flex items-center gap-3 px-3 py-2.5 text-left
                               {{ $isActive ? $lvl['bg'] : ($isUnlocked ? 'bg-gray-50' : 'bg-gray-50 opacity-60') }}
                               transition hover:brightness-95">
                    <span class="text-xl flex-shrink-0">{{ $lvl['icon'] }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold {{ $isActive ? $lvl['text'] : ($isUnlocked ? 'text-gray-700' : 'text-gray-400') }}">
                            {{ $lvl['name'] }}
                            @if($isActive)
                                <span class="ml-1 text-xs font-normal px-1.5 py-0.5 rounded-full {{ $lvl['bg'] }} {{ $lvl['border'] }} border {{ $lvl['text'] }}">você está aqui</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400">
                            @if($lvl['max']) {{ number_format($lvl['min']) }}–{{ number_format($lvl['max']) }} pts
                            @else {{ number_format($lvl['min']) }}+ pts @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($isUnlocked)
                            <span class="text-green-500 text-sm">✓</span>
                        @else
                            <span class="text-gray-300 text-sm">🔒</span>
                        @endif
                        <svg id="lvl-chevron-{{ $li }}" class="w-4 h-4 text-gray-400 transition-transform duration-200"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Conteúdo expansível --}}
                <div id="lvl-detail-{{ $li }}" class="hidden border-t border-gray-100 px-4 py-3 space-y-3
                     {{ $isActive ? $lvl['bg'] : 'bg-white' }}">

                    {{-- Vantagens VaiJunto --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Vantagens VaiJunto
                        </p>
                        <ul class="space-y-1">
                            @foreach($lvl['benefits'] as $b)
                            <li class="flex items-start gap-2 text-sm {{ $isUnlocked ? 'text-gray-700' : 'text-gray-400' }}">
                                <span class="{{ $isUnlocked ? 'text-blue-500' : 'text-gray-300' }} flex-shrink-0 mt-0.5">●</span>
                                {{ $b }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Vantagens UFLA --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Vantagens na UFLA
                        </p>
                        <ul class="space-y-1">
                            @foreach($lvl['uni_benefits'] as $b)
                            <li class="flex items-start gap-2 text-sm {{ $isUnlocked ? 'text-gray-700' : 'text-gray-400' }}">
                                <span class="{{ $isUnlocked ? 'text-green-500' : 'text-gray-300' }} flex-shrink-0 mt-0.5">●</span>
                                {{ $b }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    @if(!$isUnlocked)
                    <p class="text-xs text-gray-400 italic">
                        Faltam {{ number_format($lvl['min'] - $pts) }} pontos para desbloquear este nível.
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
            </div>
        </div>

        {{-- Histórico de transações --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Histórico de pontos</h3>
            @if(isset($pointTransactions) && $pointTransactions->isNotEmpty())
            <div class="space-y-2">
                @foreach($pointTransactions as $tx)
                <div class="flex items-center justify-between gap-3 py-1.5 border-b border-gray-50 last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 truncate">{{ $tx->reason }}</p>
                        <p class="text-xs text-gray-400">{{ $tx->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="flex-shrink-0 text-sm font-bold {{ $tx->amount > 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-4">
                Nenhuma transação ainda. Complete caronas para ganhar pontos!
            </p>
            @endif
        </div>

    </div>{{-- /dpanel-points --}}

@endif

{{-- Modal cancelar carona --}}
<div id="cancel-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h4 class="font-semibold text-gray-900 mb-3">Motivo do cancelamento</h4>
        <textarea id="cancel-reason" rows="3" placeholder="Informe o motivo..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
        <div class="flex gap-2 mt-4">
            <button onclick="document.getElementById('cancel-modal').classList.add('hidden')"
                    class="flex-1 border border-gray-300 rounded-lg py-2 text-sm text-gray-600 hover:bg-gray-50 transition">Voltar</button>
            <button onclick="confirmCancel()"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white rounded-lg py-2 text-sm font-medium transition">Confirmar</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let cancelRideId = null;

// ── Passageiro: entrar em viagem avulsa ───────────────────────────────────────
async function joinTrip(tripId, btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Aguarde...'; }
    try {
        const res = await fetch(`/trips/${tripId}/join`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (res.ok) { window.location.href = data.track_url; }
        else {
            if (btn) { btn.disabled = false; btn.textContent = 'Entrar'; }
            alert(data.message ?? 'Erro ao solicitar vaga.');
        }
    } catch {
        if (btn) { btn.disabled = false; btn.textContent = 'Entrar'; }
    }
}

// ── Passageiro: solicitar vaga em rota fixa ───────────────────────────────────
async function joinRoute(routeId, btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Aguarde...'; }
    try {
        const res = await fetch(`/routes/${routeId}/join`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (res.ok) { window.location.href = data.track_url; }
        else {
            if (btn) { btn.disabled = false; btn.textContent = 'Solicitar'; }
            alert(data.message ?? 'Erro ao solicitar vaga.');
        }
    } catch {
        if (btn) { btn.disabled = false; btn.textContent = 'Solicitar'; }
    }
}

// ── Motorista: solicitações avulsas ───────────────────────────────────────────
async function acceptRide(id, btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Aceitando...'; }
    try {
        const res = await fetch(`/rides/${id}/accept`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            window.location.href = data.drive_url ?? '/dashboard';
        } else {
            const d = await res.json();
            if (btn) { btn.disabled = false; btn.textContent = 'Aceitar'; }
            alert(d.message ?? 'Erro ao aceitar.');
        }
    } catch {
        if (btn) { btn.disabled = false; btn.textContent = 'Aceitar'; }
    }
}

async function rejectRide(id, btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Recusando...'; }
    try {
        const res = await fetch(`/rides/${id}/reject`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (res.ok) {
            document.getElementById(`req-${id}`)?.remove();
            knownRequestIds.delete(id);
            decrementRequestBadge();
        } else {
            if (btn) { btn.disabled = false; btn.textContent = 'Recusar'; }
            alert('Erro ao recusar.');
        }
    } catch {
        if (btn) { btn.disabled = false; btn.textContent = 'Recusar'; }
    }
}

// ── Cancelar carona ativa ─────────────────────────────────────────────────────
function cancelRide(id) {
    cancelRideId = id;
    document.getElementById('cancel-reason').value = '';
    document.getElementById('cancel-modal').classList.remove('hidden');
}

async function confirmCancel() {
    const reason = document.getElementById('cancel-reason').value.trim();
    if (!reason) { alert('Informe o motivo.'); return; }
    const btn = document.querySelector('#cancel-modal button[onclick="confirmCancel()"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Cancelando...'; }
    try {
        const res = await fetch(`/rides/${cancelRideId}/cancel`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason }),
        });
        document.getElementById('cancel-modal').classList.add('hidden');
        if (res.ok) { location.reload(); }
        else {
            if (btn) { btn.disabled = false; btn.textContent = 'Confirmar'; }
            alert('Erro ao cancelar carona.');
        }
    } catch {
        if (btn) { btn.disabled = false; btn.textContent = 'Confirmar'; }
    }
}

// ── Helpers de reputação ────────────────────────────────────────────────────
function renderStars(avgStars, totalRatings) {
    if (totalRatings > 0) {
        return `<span class="text-yellow-500">★</span> ${avgStars} <span class="text-gray-400">(${totalRatings})</span>`;
    }
    return `<span class="text-gray-400">Novo usuário</span>`;
}

function buildRequestCard(e) {
    const dt = new Date(e.scheduled_for).toLocaleString('pt-BR',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});
    const el = document.createElement('div');
    el.id = `req-${e.id}`;
    el.className = 'bg-white rounded-xl border border-blue-200 px-4 py-4 shadow-sm';
    el.innerHTML = `
        <p class="text-sm font-semibold text-gray-900 truncate">
            ${e.origin} <span class="text-gray-400 font-normal">→</span> ${e.destination}
        </p>
        <p class="text-xs text-gray-500 mt-1 truncate">${dt} · ${e.seats_needed} assento(s)</p>
        <button onclick="showReputation(${e.passenger_id})" class="text-xs text-gray-700 mt-0.5 mb-3 flex items-center gap-1 hover:text-blue-600 transition text-left">
            ${e.passenger.name} &nbsp;${renderStars(e.passenger.avg_stars, e.passenger.total_ratings)}
        </button>
        <div class="flex gap-2">
            <button onclick="acceptRide(${e.id}, this)"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-2 rounded-lg transition">Aceitar</button>
            <button onclick="rejectRide(${e.id}, this)"
                    class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-2 rounded-lg transition">Recusar</button>
        </div>`;
    return el;
}

// ── Listeners em tempo real ────────────────────────────────────────────────
window.addEventListener('echo:NewRideRequestForDriver', (ev) => {
    const e = ev.detail;

    if (e.fixed_route_id) {
        const link = document.querySelector(`[data-fr-id="${e.fixed_route_id}"]`);
        if (link) {
            const badge = link.querySelector('.fr-pending-badge');
            if (badge) {
                const newCount = (parseInt(badge.dataset.count) || 0) + 1;
                badge.dataset.count = newCount;
                badge.textContent = `${newCount} pendente${newCount !== 1 ? 's' : ''}`;
                badge.classList.remove('hidden');
            }
        }
        return;
    }

    const list = document.getElementById('pending-list');
    if (!list) return;
    document.getElementById('pending-empty')?.remove();
    list.prepend(buildRequestCard(e));
    showDriverTab('requests');
    incrementRequestBadge();
});

// ── Polling de pedidos pendentes (fallback quando Echo falha) ───────────────
const knownRequestIds = new Set([
    @foreach($pendingRequests ?? [] as $req) {{ $req->id }}, @endforeach
]);

async function pollPendingRequests() {
    const list = document.getElementById('pending-list');
    if (!list) return;
    try {
        const res  = await fetch('/driver/pending-requests', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) { console.warn('[poll] status', res.status); return; }
        const data = await res.json();
        const activeIds = new Set(data.requests.map(r => r.id));

        // Adiciona novos
        let hasNew = false;
        for (const req of data.requests) {
            if (!knownRequestIds.has(req.id)) {
                knownRequestIds.add(req.id);
                document.getElementById('pending-empty')?.remove();
                list.prepend(buildRequestCard(req));
                hasNew = true;
            }
        }
        if (hasNew) {
            incrementRequestBadge();
            showDriverTab('requests');
        }

        // Remove cancelados/aceitos que sumiram da lista
        for (const id of [...knownRequestIds]) {
            if (!activeIds.has(id)) {
                knownRequestIds.delete(id);
                if (document.getElementById(`req-${id}`)) {
                    document.getElementById(`req-${id}`).remove();
                    decrementRequestBadge();
                }
                // Se a lista ficou vazia, mostra empty-state
                if (!list.querySelector('[id^="req-"]')) {
                    const empty = document.createElement('div');
                    empty.id = 'pending-empty';
                    empty.className = 'bg-white rounded-xl border border-gray-200 p-5 text-center text-gray-400 text-sm';
                    empty.textContent = 'Nenhuma solicitação avulsa no momento.';
                    list.appendChild(empty);
                }
            }
        }

        // Atualiza badges de rotas fixas
        if (data.fixed_route_pending) {
            for (const [routeId, count] of Object.entries(data.fixed_route_pending)) {
                const link = document.querySelector(`[data-fr-id="${routeId}"]`);
                if (!link) continue;
                const badge = link.querySelector('.fr-pending-badge');
                if (!badge) continue;
                badge.dataset.count = count;
                badge.textContent = count > 0 ? `${count} pendente${count !== 1 ? 's' : ''}` : '';
                badge.classList.toggle('hidden', count <= 0);
            }
        }

        // Atualiza badges de viagens avulsas com solicitações pendentes
        if (data.trip_pending) {
            for (const [tripId, count] of Object.entries(data.trip_pending)) {
                const tripLink = document.querySelector(`[data-trip-id="${tripId}"]`);
                if (!tripLink) continue;
                const infoP = tripLink.querySelector('.trip-info-p');
                if (!infoP) continue;
                let span = infoP.querySelector('.trip-pending-count');
                if (count > 0) {
                    if (!span) {
                        span = document.createElement('span');
                        span.className = 'trip-pending-count text-blue-600 font-medium';
                        span.dataset.count = '0';
                        infoP.append(' · ', span);
                    }
                    if (parseInt(span.dataset.count) !== count) {
                        span.dataset.count = count;
                        span.textContent = `${count} pendente${count !== 1 ? 's' : ''}`;
                        // Destaca a aba Atividade se não estiver ativa
                        if (document.getElementById('dpanel-activity')?.classList.contains('hidden')) {
                            const actBtn = document.getElementById('dtab-activity');
                            if (actBtn && !actBtn.querySelector('.trip-req-dot')) {
                                const dot = document.createElement('span');
                                dot.className = 'trip-req-dot absolute -top-1 -right-1 w-3 h-3 rounded-full bg-blue-500 border-2 border-white';
                                actBtn.style.position = 'relative';
                                actBtn.appendChild(dot);
                            }
                        }
                    }
                } else if (span) {
                    span.parentNode?.removeChild(span.previousSibling); // remove ' · '
                    span.remove();
                }
            }
        }
    } catch (err) { console.error('[poll] erro:', err); }
}

function incrementRequestBadge() {
    const btn = document.getElementById('dtab-requests');
    if (!btn) return;
    let badge = btn.querySelector('.req-badge');
    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'req-badge absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center';
        btn.style.position = 'relative';
        btn.appendChild(badge);
    }
    badge.textContent = (parseInt(badge.textContent) || 0) + 1;
}

function decrementRequestBadge() {
    const badge = document.querySelector('#dtab-requests .req-badge');
    if (!badge) return;
    const next = (parseInt(badge.textContent) || 1) - 1;
    if (next <= 0) badge.remove();
    else badge.textContent = next;
}

// Inicia polling a cada 15s apenas se estiver no painel do motorista
if (document.getElementById('pending-list')) {
    setInterval(pollPendingRequests, 1000);
}

window.addEventListener('echo:TripRequestReceived', (ev) => {
    const e = ev.detail;

    // Localiza o card da viagem pelo data-trip-id
    const tripLink = document.querySelector(`[data-trip-id="${e.trip_id}"]`);
    if (!tripLink) return;

    // Atualiza ou cria o span de pendentes dentro do parágrafo de info
    const infoP = tripLink.querySelector('.trip-info-p');
    if (infoP) {
        let span = infoP.querySelector('.trip-pending-count');
        if (!span) {
            span = document.createElement('span');
            span.className = 'trip-pending-count text-blue-600 font-medium';
            span.dataset.count = '0';
            infoP.append(' · ', span);
        }
        const newCount = (parseInt(span.dataset.count) || 0) + 1;
        span.dataset.count = newCount;
        span.textContent = `${newCount} pendente${newCount !== 1 ? 's' : ''}`;
    }

    // Se o motorista não está na aba Atividade, destaca ela com um ponto azul
    if (document.getElementById('dpanel-activity')?.classList.contains('hidden')) {
        const actBtn = document.getElementById('dtab-activity');
        if (actBtn && !actBtn.querySelector('.trip-req-dot')) {
            const dot = document.createElement('span');
            dot.className = 'trip-req-dot absolute -top-1 -right-1 w-3 h-3 rounded-full bg-blue-500 border-2 border-white';
            actBtn.style.position = 'relative';
            actBtn.appendChild(dot);
        }
    }
});

window.addEventListener('echo:NewFixedRouteOffer', (ev) => {
    const e = ev.detail;
    if (!document.querySelector('[data-passenger-dashboard]')) return;

    const container = document.getElementById('routes-list');
    if (!container) { location.reload(); return; }
    document.getElementById('routes-empty')?.remove();

    const seatsLeft = e.available_seats;
    const card = document.createElement('div');
    card.className = 'bg-white rounded-xl border border-blue-200 shadow-sm px-5 py-4';
    card.dataset.routeId = e.id;
    card.innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1.5">
                    <img src="${e.driver?.avatar ?? ''}"
                         onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(e.driver?.name ?? '?')}&background=7c3aed&color=fff&size=32'"
                         class="w-7 h-7 rounded-full border border-gray-200">
                    <span class="text-sm font-medium text-gray-700">${e.driver?.name ?? '—'}</span>
                </div>
                <p class="text-sm font-semibold text-gray-900 truncate">
                    ${e.origin} <span class="text-gray-400 mx-1 font-normal">→</span> ${e.destination}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    ${e.departure_time ?? ''} · ${e.days_label ?? ''}
                    &nbsp;·&nbsp; <span class="text-green-600 font-medium">${seatsLeft} vaga(s)</span>
                </p>
            </div>
            <button onclick="joinRoute(${e.id}, this)"
                    class="flex-shrink-0 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                Solicitar
            </button>
        </div>`;
    container.prepend(card);
});

window.addEventListener('echo:NewTripOffer', (ev) => {
    const e = ev.detail;
    if (!document.querySelector('[data-passenger-dashboard]')) return;

    // Adiciona o novo card na lista de viagens ofertadas
    const list = document.getElementById('trips-list');
    const empty = document.getElementById('trips-empty');

    if (list) {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-xl border border-blue-200 shadow-sm px-5 py-4 animate-pulse-once';
        card.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <img src="${e.driver?.avatar ?? ''}"
                             onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(e.driver?.name ?? '?')}&background=2563eb&color=fff&size=32'"
                             class="w-7 h-7 rounded-full border border-gray-200">
                        <span class="text-sm font-medium text-gray-700">${e.driver?.name ?? '—'}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 truncate">
                        ${e.origin} <span class="text-gray-400 mx-1 font-normal">→</span> ${e.destination}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        ${new Date(e.departs_at).toLocaleString('pt-BR',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'})}
                        &nbsp;·&nbsp; <span class="text-green-600 font-medium">${e.seats_total} vaga(s)</span>
                    </p>
                </div>
                <button onclick="joinTrip(${e.id}, this)"
                        class="flex-shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                    Entrar
                </button>
            </div>`;
        list.prepend(card);
        setTimeout(() => card.classList.remove('animate-pulse-once'), 1000);
    } else if (empty) {
        // Se a lista vazia estava visível, recarrega para reconstruir com a lista correta
        location.reload();
    }
});

// ── Passageiro: motorista iniciou a viagem → atualiza banner ─────────────────
window.addEventListener('echo:RideStarted', () => {
    const banner = document.getElementById('active-request-banner');
    if (!banner) return;
    // Atualiza classe e texto do banner para "Viagem em andamento"
    banner.className = banner.className
        .replace(/bg-\S+/g, 'bg-green-50')
        .replace(/border-\S+/g, 'border-green-400');
    const labelEl = banner.querySelector('p.font-bold');
    if (labelEl) {
        labelEl.className = labelEl.className.replace(/text-\S+800/g, 'text-green-800');
        labelEl.textContent = '📍 Viagem em andamento';
    }
    const badgeEl = banner.querySelector('span.flex-shrink-0');
    if (badgeEl) {
        badgeEl.className = badgeEl.className.replace(/bg-\S+/g, 'bg-green-600');
    }
    // Adiciona o ping animado
    const iconSpan = banner.querySelector('span.relative.flex-shrink-0');
    if (iconSpan && !iconSpan.querySelector('.animate-ping')) {
        const ping = document.createElement('span');
        ping.className = 'absolute -top-1 -right-1 w-3 h-3 rounded-full bg-green-500 border-2 border-white animate-ping';
        iconSpan.appendChild(ping);
    }
});

// ── Passageiro: polling do status do banner ativo (fallback quando Echo falha) ──
@if(isset($activeRequest) && $activeRequest && $activeRequest->ride)
(function() {
    const statusUrl = "{{ route('rides.status', $activeRequest) }}";
    let bannerStatus = '{{ $activeRequest->ride?->status ?? 'accepted' }}';

    async function pollBannerStatus() {
        if (['in_progress', 'completed', 'cancelled'].includes(bannerStatus)) return;
        try {
            const res  = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            const rideStatus = data.ride?.status;
            if (rideStatus === bannerStatus) return;
            bannerStatus = rideStatus;
            // Dispara o evento como se tivesse vindo do Echo
            if (rideStatus === 'in_progress') {
                window.dispatchEvent(new CustomEvent('echo:RideStarted'));
            } else if (rideStatus === 'cancelled') {
                document.getElementById('active-request-banner')?.remove();
            }
        } catch { /* ignora */ }
    }

    setInterval(pollBannerStatus, 8000);
}());
@endif

// ── Passageiro: remove banner quando rota fixa é pausada ─────────────────────
window.addEventListener('echo:FixedRoutePaused', () => {
    document.getElementById('active-request-banner')?.remove();
});

// ── Passageiro: remove banner quando motorista cancela a carona ───────────────
window.addEventListener('echo:RideCancelledByDriver', () => {
    document.getElementById('active-request-banner')?.remove();
});

// ── Polling de ofertas disponíveis (passageiro) ──────────────────────────────
if (document.querySelector('[data-passenger-dashboard]')) {
    const knownTripIds  = new Set([{{ collect($availableTrips ?? [])->pluck('id')->join(', ') }}]);
    const knownRouteIds = new Set([{{ collect($availableFixedRoutes ?? [])->pluck('id')->join(', ') }}]);

    async function pollAvailableOffers() {
        try {
            const params = new URLSearchParams({
                trip_ids:  [...knownTripIds].join(','),
                route_ids: [...knownRouteIds].join(','),
            });
            const res = await fetch(`/passenger/available-offers?${params}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const { trips, routes, removed_route_ids } = await res.json();

            for (const t of trips) {
                if (knownTripIds.has(t.id)) continue;
                knownTripIds.add(t.id);
                const list  = document.getElementById('trips-list');
                const empty = document.getElementById('trips-empty');
                if (empty) empty.remove();
                if (!list) continue;
                const card = document.createElement('div');
                card.className = 'bg-white rounded-xl border border-blue-200 shadow-sm px-5 py-4';
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <img src="${t.driver?.avatar ?? ''}"
                                     onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(t.driver?.name ?? '?')}&background=2563eb&color=fff&size=32'"
                                     class="w-7 h-7 rounded-full border border-gray-200">
                                <span class="text-sm font-medium text-gray-700">${t.driver?.name ?? '—'}</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                ${t.origin} <span class="text-gray-400 mx-1 font-normal">→</span> ${t.destination}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${new Date(t.departs_at).toLocaleString('pt-BR',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'})}
                                &nbsp;·&nbsp; <span class="${t.seats_left > 0 ? 'text-green-600' : 'text-red-500'} font-medium">${t.seats_left} vaga(s)</span>
                            </p>
                        </div>
                        <button onclick="joinTrip(${t.id}, this)"
                                ${t.seats_left <= 0 ? 'disabled' : ''}
                                class="flex-shrink-0 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                            Entrar
                        </button>
                    </div>`;
                list.prepend(card);
            }

            for (const r of routes) {
                if (knownRouteIds.has(r.id)) continue;
                knownRouteIds.add(r.id);
                const container = document.getElementById('routes-list');
                if (!container) continue;
                document.getElementById('routes-empty')?.remove();
                const card = document.createElement('div');
                card.className = 'bg-white rounded-xl border border-purple-200 shadow-sm px-5 py-4';
                card.dataset.routeId = r.id;
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <img src="${r.driver?.avatar ?? ''}"
                                     onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(r.driver?.name ?? '?')}&background=7c3aed&color=fff&size=32'"
                                     class="w-7 h-7 rounded-full border border-gray-200">
                                <span class="text-sm font-medium text-gray-700">${r.driver?.name ?? '—'}</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                ${r.origin} <span class="text-gray-400 mx-1 font-normal">→</span> ${r.destination}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${r.departure_time ?? ''} · ${r.days_label ?? ''}
                                &nbsp;·&nbsp; <span class="${r.seats_left > 0 ? 'text-green-600' : 'text-red-500'} font-medium">${r.seats_left} vaga(s)</span>
                            </p>
                        </div>
                        <button onclick="joinRoute(${r.id}, this)"
                                ${r.seats_left <= 0 ? 'disabled' : ''}
                                class="flex-shrink-0 bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                            Solicitar
                        </button>
                    </div>`;
                container.prepend(card);
            }

            for (const id of (removed_route_ids ?? [])) {
                knownRouteIds.delete(id);
                document.querySelector(`[data-route-id="${id}"]`)?.remove();
                const container = document.getElementById('routes-list');
                if (container && !container.querySelector('[data-route-id]')) {
                    const empty = document.createElement('div');
                    empty.id = 'routes-empty';
                    empty.className = 'bg-white rounded-xl border border-gray-200 p-5 text-center text-gray-400 text-sm';
                    empty.textContent = 'Nenhuma rota fixa disponível.';
                    container.appendChild(empty);
                }
            }
        } catch { /* silencia erros de rede */ }
    }

    pollAvailableOffers(); // chamada imediata ao carregar a página
    setInterval(pollAvailableOffers, 5000);
}

// ── Tabs passageiro ─────────────────────────────────────────────────────────
function showTab(tab) {
    const panels = ['offers', 'mine'];
    panels.forEach(p => {
        document.getElementById(`panel-${p}`)?.classList.toggle('hidden', p !== tab);
        const btn = document.getElementById(`tab-${p}`);
        if (p === tab) {
            btn?.classList.add('bg-white', 'text-blue-700', 'shadow');
            btn?.classList.remove('text-gray-500');
        } else {
            btn?.classList.remove('bg-white', 'text-blue-700', 'shadow');
            btn?.classList.add('text-gray-500');
        }
    });
}

// ── Filtros de solicitações do caronista ─────────────────────────────────────
let currentReqFilter = 'active';
function filterRequests(group) {
    currentReqFilter = group;
    const cards = document.querySelectorAll('.req-card');
    let visible = 0;
    cards.forEach(card => {
        const match = group === 'all' || card.dataset.filter === group;
        card.classList.toggle('hidden', !match);
        if (match) visible++;
    });
    document.getElementById('req-empty-msg')?.classList.toggle('hidden', visible > 0);
    document.querySelectorAll('.req-filter-btn').forEach(btn => {
        const active = btn.id === `req-filter-${group}`;
        btn.classList.toggle('bg-white',    active);
        btn.classList.toggle('text-gray-800', active);
        btn.classList.toggle('shadow-sm',   active);
        btn.classList.toggle('text-gray-500', !active);
    });
}
filterRequests('active');

// ── Accordion de níveis ──────────────────────────────────────────────────────
function toggleLevel(index) {
    const detail  = document.getElementById(`lvl-detail-${index}`);
    const chevron = document.getElementById(`lvl-chevron-${index}`);
    if (!detail) return;
    const isOpen = !detail.classList.contains('hidden');
    // Fecha todos
    document.querySelectorAll('[id^="lvl-detail-"]').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[id^="lvl-chevron-"]').forEach(el => el.classList.remove('rotate-180'));
    // Abre o clicado se estava fechado
    if (!isOpen) {
        detail.classList.remove('hidden');
        chevron?.classList.add('rotate-180');
    }
}
// Abre nível atual automaticamente (só no modo motorista)
@isset($activeLevelIdx)
(function openCurrentLevel() {
    const idx = {{ $activeLevelIdx }};
    const detail  = document.getElementById(`lvl-detail-${idx}`);
    const chevron = document.getElementById(`lvl-chevron-${idx}`);
    if (detail)  detail.classList.remove('hidden');
    if (chevron) chevron.classList.add('rotate-180');
}());
@endisset

// ── Tabs motorista ───────────────────────────────────────────────────────────
function showDriverTab(tab) {
    const panels = ['activity', 'requests', 'rides', 'points'];
    panels.forEach(p => {
        document.getElementById(`dpanel-${p}`)?.classList.toggle('hidden', p !== tab);
        const btn = document.getElementById(`dtab-${p}`);
        if (!btn) return;
        if (p === tab) {
            btn.classList.add('bg-white', 'text-blue-700', 'shadow');
            btn.classList.remove('text-gray-500');
            if (p === 'activity') btn.querySelector('.trip-req-dot')?.remove();
        } else {
            btn.classList.remove('bg-white', 'text-blue-700', 'shadow');
            btn.classList.add('text-gray-500');
        }
    });
}

// ── Filtros de corridas do motorista ─────────────────────────────────────────
function filterDriverRides(group) {
    const cards = document.querySelectorAll('.dride-card');
    let visible = 0;
    cards.forEach(card => {
        const match = group === 'all' || card.dataset.filter === group;
        card.classList.toggle('hidden', !match);
        if (match) visible++;
    });
    document.getElementById('dride-empty-msg')?.classList.toggle('hidden', visible > 0);
    document.querySelectorAll('.dride-filter-btn').forEach(btn => {
        const active = btn.id === `dride-filter-${group}`;
        btn.classList.toggle('bg-white',      active);
        btn.classList.toggle('text-gray-800', active);
        btn.classList.toggle('shadow-sm',     active);
        btn.classList.toggle('text-gray-500', !active);
    });
}
filterDriverRides('active');

</script>
@endpush
