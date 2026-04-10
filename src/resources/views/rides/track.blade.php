@extends('layouts.app')

@section('title', 'Acompanhar Carona')

@section('content')

@php
    $req   = $rideRequest;
    $ride  = $req->ride;
    $reqStatus = $req->status;   // pending | accepted | rejected | cancelled
    $rideStatus = $ride?->status; // pending | accepted | in_progress | completed | cancelled

    // Status "visual" unificado para o passageiro
    $uiStatus = match(true) {
        $reqStatus === 'cancelled'                                    => 'cancelled',
        $reqStatus === 'rejected'                                     => 'rejected',
        $rideStatus === 'completed'                                   => 'completed',
        $rideStatus === 'cancelled'                                   => 'cancelled',
        $rideStatus === 'in_progress'                                 => 'in_progress',
        in_array($rideStatus, ['pending','accepted']) && $ride        => 'driver_found',
        default                                                       => 'waiting',
    };

    $statusConfig = [
        'waiting'      => ['label' => 'Aguardando motorista',   'color' => 'yellow',  'icon' => '⏳', 'pulse' => true],
        'driver_found' => ['label' => 'Motorista a caminho!',   'color' => 'blue',    'icon' => '🚗', 'pulse' => true],
        'in_progress'  => ['label' => 'Em andamento',           'color' => 'green',   'icon' => '📍', 'pulse' => true],
        'completed'    => ['label' => 'Viagem concluída',       'color' => 'emerald', 'icon' => '✅', 'pulse' => false],
        'cancelled'    => ['label' => 'Carona cancelada',       'color' => 'gray',    'icon' => '✖',  'pulse' => false],
        'rejected'     => ['label' => 'Solicitação recusada',   'color' => 'red',     'icon' => '✖',  'pulse' => false],
    ];

    $s = $statusConfig[$uiStatus];

    $colorMap = [
        'yellow'  => ['bg' => 'bg-yellow-50',  'border' => 'border-yellow-300', 'text' => 'text-yellow-800',  'dot' => 'bg-yellow-400'],
        'blue'    => ['bg' => 'bg-blue-50',    'border' => 'border-blue-300',   'text' => 'text-blue-800',    'dot' => 'bg-blue-500'],
        'green'   => ['bg' => 'bg-green-50',   'border' => 'border-green-300',  'text' => 'text-green-800',   'dot' => 'bg-green-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-300','text' => 'text-emerald-800', 'dot' => 'bg-emerald-500'],
        'gray'    => ['bg' => 'bg-gray-100',   'border' => 'border-gray-300',   'text' => 'text-gray-600',    'dot' => 'bg-gray-400'],
        'red'     => ['bg' => 'bg-red-50',     'border' => 'border-red-300',    'text' => 'text-red-700',     'dot' => 'bg-red-500'],
    ];
    $c = $colorMap[$s['color']];

    $canCancel = in_array($reqStatus, ['pending', 'accepted'])
              && !in_array($rideStatus, ['in_progress', 'completed', 'cancelled']);
@endphp

<div class="max-w-xl mx-auto space-y-4" id="track-root"
     data-request-id="{{ $req->id }}"
     data-ui-status="{{ $uiStatus }}"
     data-can-cancel="{{ $canCancel ? 'true' : 'false' }}"
     data-cancel-url="{{ route('rides.cancel-request', $req) }}"
     data-status-url="{{ route('rides.status', $req) }}"
     data-boarded-url="{{ $ride ? route('rides.boarded', $ride) : '' }}"
     data-driver-arrived="{{ ($ride && $ride->arrived_at) ? 'true' : 'false' }}"
     data-passenger-boarded="{{ ($ride && $ride->passenger_boarded_at) ? 'true' : 'false' }}">

    {{-- Voltar --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Dashboard</a>
        <h2 class="text-xl font-bold text-gray-900">Acompanhar Carona</h2>
    </div>

    {{-- Banner de status --}}
    <div id="status-banner" class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $c['bg'] }} {{ $c['border'] }}">
        @if($s['pulse'])
        <span class="relative flex h-3 w-3 flex-shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $c['dot'] }} opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 {{ $c['dot'] }}"></span>
        </span>
        @else
        <span class="inline-flex h-3 w-3 rounded-full flex-shrink-0 {{ $c['dot'] }}"></span>
        @endif
        <div>
            <p id="status-label" class="font-semibold text-sm {{ $c['text'] }}">{{ $s['icon'] }} {{ $s['label'] }}</p>
            @if($uiStatus === 'waiting')
            <p class="text-xs {{ $c['text'] }} opacity-75 mt-0.5">Notificamos os motoristas disponíveis. Atualizando automaticamente...</p>
            @elseif($uiStatus === 'driver_found')
            <p class="text-xs {{ $c['text'] }} opacity-75 mt-0.5">Um motorista aceitou sua solicitação.</p>
            @endif
        </div>
    </div>

    {{-- Mapa --}}
    @if($mapsKey)
    <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
        <div id="track-map" class="w-full h-56 bg-gray-100"></div>
    </div>
    @endif

    {{-- Detalhes da viagem --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Detalhes da viagem</h3>
        <div class="space-y-2">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 w-2.5 h-2.5 rounded-full bg-green-500 flex-shrink-0 mt-1.5"></span>
                <div>
                    <p class="text-xs text-gray-400">Origem</p>
                    <p class="text-sm text-gray-800 font-medium">{{ $req->origin }}</p>
                </div>
            </div>
            <div class="ml-[4.5px] border-l-2 border-dashed border-gray-200 h-4"></div>
            <div class="flex items-start gap-3">
                <span class="mt-0.5 w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0 mt-1.5"></span>
                <div>
                    <p class="text-xs text-gray-400">Destino</p>
                    <p class="text-sm text-gray-800 font-medium">{{ $req->destination }}</p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-100 pt-3 flex gap-6 text-sm">
            <div>
                <p class="text-xs text-gray-400">Horário</p>
                <p class="font-medium text-gray-800">{{ $req->scheduled_for->format('d/m H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Assentos</p>
                <p class="font-medium text-gray-800">{{ $req->seats_needed }}</p>
            </div>
        </div>
    </div>

    {{-- Card do motorista (visível quando driver_found ou in_progress) --}}
    <div id="driver-card"
         class="{{ in_array($uiStatus, ['driver_found','in_progress']) ? '' : 'hidden' }} bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Motorista</h3>
        <div class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition"
             id="driver-card-inner"
             onclick="{{ $ride?->driver_id ? 'showReputation('.$ride->driver_id.')' : '' }}"
             title="Ver reputação">
            <img id="driver-avatar"
                 src="{{ $ride?->driver?->avatar ?? '' }}"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($ride?->driver?->name ?? 'M') }}&background=2563eb&color=fff&size=64'"
                 alt="Avatar"
                 class="w-12 h-12 rounded-full object-cover border-2 border-blue-100">
            <div>
                <p id="driver-name" class="font-semibold text-gray-900 text-sm">
                    {{ $ride?->driver?->name ?? '—' }}
                </p>
                @php
                    $v = $ride?->vehicle ?? $ride?->driver?->vehicle;
                    $dRatingCount = $ride?->driver?->ratingsReceived()->count() ?? 0;
                    $dAvgStars    = $dRatingCount > 0 ? round($ride->driver->ratingsReceived()->avg('stars'), 1) : null;
                @endphp
                <p id="driver-rating" class="text-xs mt-0.5 {{ $dAvgStars ? 'text-yellow-500' : 'text-gray-400' }}">
                    @if($dAvgStars)
                        ★ {{ $dAvgStars }} <span class="text-gray-400">({{ $dRatingCount }})</span>
                    @elseif($ride?->driver)
                        Novo motorista
                    @endif
                </p>
                <p id="vehicle-info" class="text-xs text-gray-500 mt-0.5">
                    @if($v)
                        {{ $v->model }}{{ $v->color ? ' · ' . $v->color : '' }} · {{ $v->plate }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Botão embarcar (visível quando motorista chegou, passageiro ainda não confirmou) --}}
    <div id="board-section"
         class="{{ ($ride && $ride->arrived_at && !$ride->passenger_boarded_at) ? '' : 'hidden' }}
                bg-orange-50 border border-orange-300 rounded-2xl p-4 space-y-3 text-center">
        <p class="text-sm font-semibold text-orange-800">🚗 O motorista chegou ao ponto de embarque!</p>
        <p class="text-xs text-orange-600">Confirme que você entrou no veículo para o motorista iniciar a viagem.</p>
        <button id="btn-board"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 rounded-xl transition text-sm shadow">
            ✅ Embarquei!
        </button>
    </div>

    {{-- Aguardando motorista iniciar (passageiro já embarcou) --}}
    <div id="boarded-waiting-section"
         class="{{ ($ride && $ride->passenger_boarded_at && $ride->status === 'accepted') ? '' : 'hidden' }}
                bg-purple-50 border border-purple-300 rounded-2xl p-4 text-center">
        <p class="text-sm font-semibold text-purple-800">⏳ Aguardando o motorista iniciar a viagem...</p>
    </div>

    {{-- Botão avaliar (visível após conclusão) --}}
    <div id="rate-section" class="{{ $uiStatus === 'completed' && $ride ? '' : 'hidden' }}">
        @if($uiStatus === 'completed' && $ride)
        <a href="{{ route('rides.rate', $ride) }}"
           class="flex items-center justify-center gap-2 w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold py-3.5 rounded-xl transition text-sm shadow">
            ⭐ Avaliar esta viagem
        </a>
        @else
        <a id="rate-link" href="#"
           class="flex items-center justify-center gap-2 w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold py-3.5 rounded-xl transition text-sm shadow">
            ⭐ Avaliar esta viagem
        </a>
        @endif
    </div>

    {{-- Botão cancelar --}}
    <div id="cancel-section" class="{{ $canCancel ? '' : 'hidden' }}">
        <button id="cancel-btn"
                class="w-full border border-red-300 text-red-600 hover:bg-red-50 font-medium py-3 rounded-xl transition text-sm">
            Cancelar carona
        </button>
    </div>

</div>

@endsection

@push('scripts')
@if($mapsKey)
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

// Variáveis globais acessíveis de qualquer script
window.trackMap       = null;
window.carMarker      = null;
window.trackPolyline  = null;  // rota atual
window.trackRoutePoints = [];  // pontos para cálculo de desvio
window.trackLastReroute = 0;
window.trackDestCoords  = null;

async function initTrackMap() {
    const { Map } = await google.maps.importLibrary("maps");

    const originCoords = "{{ $req->origin_coords }}".split(",").map(Number);
    const destCoords   = "{{ $req->destination_coords ?? '' }}".split(",").map(Number);

    const origin = originCoords.length === 2 && originCoords[0] !== 0
        ? { lat: originCoords[0], lng: originCoords[1] } : null;
    const dest   = destCoords.length === 2 && destCoords[0] !== 0
        ? { lat: destCoords[0], lng: destCoords[1] } : null;

    const center = origin ?? dest ?? { lat: -21.2342, lng: -44.9998 };

    window.trackMap = new Map(document.getElementById("track-map"), {
        center, zoom: 13,
        mapTypeControl: false, streetViewControl: false,
        fullscreenControl: false, zoomControl: false,
        gestureHandling: "cooperative",
    });

    if (origin) {
        new google.maps.Marker({
            map: window.trackMap, position: origin, title: "Origem",
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 9,
                    fillColor: "#16a34a", fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 },
        });
    }

    if (dest) {
        new google.maps.Marker({
            map: window.trackMap, position: dest, title: "Destino",
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 9,
                    fillColor: "#dc2626", fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 },
        });
    }

    // Marcador do carro do motorista — global para o listener Echo acessar
    window.carMarker = new google.maps.Marker({
        map: window.trackMap,
        position: origin ?? center,
        title: "Motorista",
        icon: {
            url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="36" height="36">
                    <circle cx="18" cy="18" r="16" fill="#2563EB" stroke="white" stroke-width="2"/>
                    <text x="18" y="23" text-anchor="middle" font-size="16" fill="white">🚗</text>
                </svg>`),
            scaledSize: new google.maps.Size(36, 36),
            anchor: new google.maps.Point(18, 18),
        },
    });

    if (origin && dest) {
        window.trackDestCoords = dest;
        const bounds = new google.maps.LatLngBounds();
        bounds.extend(origin);
        bounds.extend(dest);
        window.trackMap.fitBounds(bounds, 48);
        await drawTrackRoute(origin, dest);
    }
}

async function drawTrackRoute(from, to, color = "#2563EB") {
    if (window.trackPolyline) { window.trackPolyline.setMap(null); window.trackPolyline = null; }

    try {
        const result = await new google.maps.DirectionsService().route({
            origin: from, destination: to,
            travelMode: google.maps.TravelMode.DRIVING,
        });
        window.trackRoutePoints = result.routes[0].overview_path;
        window.trackPolyline = new google.maps.Polyline({
            path: window.trackRoutePoints,
            strokeColor: color, strokeWeight: 5, strokeOpacity: 0.85, map: window.trackMap,
        });
    } catch { /* rota indisponível */ }
}

function haversineM(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const f1 = lat1 * Math.PI / 180, f2 = lat2 * Math.PI / 180;
    const df = (lat2 - lat1) * Math.PI / 180, dl = (lng2 - lng1) * Math.PI / 180;
    const a  = Math.sin(df/2)**2 + Math.cos(f1)*Math.cos(f2)*Math.sin(dl/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

async function checkTrackReroute(lat, lng) {
    if (!window.trackDestCoords || !window.trackRoutePoints.length) return;
    const now = Date.now();
    if (now - window.trackLastReroute < 25000) return; // cooldown 25s lado passageiro

    let minDist = Infinity;
    for (const pt of window.trackRoutePoints) {
        minDist = Math.min(minDist, haversineM(lat, lng, pt.lat(), pt.lng()));
    }

    if (minDist > 100) { // >100m fora da rota original
        window.trackLastReroute = now;
        await drawTrackRoute({ lat, lng }, window.trackDestCoords, "#f97316");
    }
}

initTrackMap();
</script>
@endif

<script>
const root          = document.getElementById("track-root");
const statusUrl     = root.dataset.statusUrl;
const cancelUrl     = root.dataset.cancelUrl;
let   boardedUrl    = root.dataset.boardedUrl;
const csrfToken     = document.querySelector("meta[name='csrf-token']").content;

let currentStatus    = root.dataset.uiStatus;
let driverArrived    = root.dataset.driverArrived === 'true';
let passengerBoarded = root.dataset.passengerBoarded === 'true';
let polling;

// ── WebSocket (primário) + Polling (fallback a cada 15s) ─────────────────────
const TERMINAL = ["completed", "cancelled", "rejected"];

function startPolling() {
    if (TERMINAL.includes(currentStatus)) return;
    polling = setInterval(fetchStatus, 5000); // fallback a cada 5s
}

async function fetchStatus() {
    try {
        const res  = await fetch(statusUrl, { headers: { "Accept": "application/json" } });
        const data = await res.json();
        applyStatus(data);
    } catch { /* ignora erros de rede temporários */ }
}

// RideAccepted — motorista aceitou
window.addEventListener('echo:RideAccepted', (ev) => {
    applyStatus(ev.detail);
});

// DriverLocationUpdated — move o marcador e atualiza rota se desvio detectado
window.addEventListener('echo:DriverLocationUpdated', async (ev) => {
    const { lat, lng } = ev.detail;
    if (window.carMarker) {
        window.carMarker.setPosition({ lat, lng });
        window.trackMap?.panTo({ lat, lng });
    }
    await checkTrackReroute(lat, lng);
});

// RideCancelledByDriver — motorista cancelou
window.addEventListener('echo:RideCancelledByDriver', () => {
    currentStatus = 'cancelled';
    clearInterval(polling);
    updateBanner('cancelled');
    document.getElementById('cancel-section')?.classList.add('hidden');
    document.getElementById('rate-section')?.classList.add('hidden');
    document.getElementById('board-section')?.classList.add('hidden');
    document.getElementById('boarded-waiting-section')?.classList.add('hidden');
});

// DriverArrived — motorista chegou ao ponto de embarque
window.addEventListener('echo:DriverArrived', () => {
    driverArrived = true;
    currentStatus = 'driver_arrived';
    updateBanner('driver_arrived');
    document.getElementById('board-section')?.classList.remove('hidden');
    document.getElementById('boarded-waiting-section')?.classList.add('hidden');
});

// PassengerBoarded — passageiro confirmou embarque (útil se outra aba estiver aberta)
window.addEventListener('echo:PassengerBoarded', () => {
    passengerBoarded = true;
    currentStatus = 'boarded_waiting';
    updateBanner('boarded_waiting');
    document.getElementById('board-section')?.classList.add('hidden');
    document.getElementById('boarded-waiting-section')?.classList.remove('hidden');
});

// ── Botão Embarquei ──────────────────────────────────────────────────────────
document.getElementById('btn-board')?.addEventListener('click', async () => {
    const btn = document.getElementById('btn-board');
    if (!boardedUrl) return;
    btn.disabled = true; btn.textContent = 'Confirmando...';

    try {
        const res = await fetch(boardedUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (res.ok) {
            passengerBoarded = true;
            currentStatus = 'boarded_waiting';
            updateBanner('boarded_waiting');
            document.getElementById('board-section')?.classList.add('hidden');
            document.getElementById('boarded-waiting-section')?.classList.remove('hidden');
        } else {
            btn.disabled = false; btn.textContent = '✅ Embarquei!';
        }
    } catch {
        btn.disabled = false; btn.textContent = '✅ Embarquei!';
    }
});

function applyStatus(data) {
    const reqStatus  = data.request_status;
    const rideStatus = data.ride?.status ?? null;

    // Sincroniza flags de embarque vindas do polling
    if (data.ride?.driver_arrived   !== undefined) driverArrived    = data.ride.driver_arrived;
    if (data.ride?.passenger_boarded !== undefined) passengerBoarded = data.ride.passenger_boarded;

    // Atualiza boardedUrl quando a ride for criada (página carregou antes da aceitação)
    if (data.ride?.id && !boardedUrl) {
        boardedUrl = `/rides/${data.ride.id}/boarded`;
    }

    let ui;
    if      (reqStatus === "cancelled")                                                               ui = "cancelled";
    else if (reqStatus === "rejected")                                                                ui = "rejected";
    else if (rideStatus === "completed")                                                              ui = "completed";
    else if (rideStatus === "cancelled")                                                              ui = "cancelled";
    else if (rideStatus === "in_progress")                                                            ui = "in_progress";
    else if (rideStatus && ["pending","accepted"].includes(rideStatus) && passengerBoarded)           ui = "boarded_waiting";
    else if (rideStatus && ["pending","accepted"].includes(rideStatus) && driverArrived)              ui = "driver_arrived";
    else if (rideStatus && ["pending","accepted"].includes(rideStatus))                               ui = "driver_found";
    else                                                                                              ui = "waiting";

    if (ui !== currentStatus) {
        currentStatus = ui;
        updateBanner(ui);
        updateDriverCard(data.ride);
        updateCancelSection(reqStatus, rideStatus);
        if (TERMINAL.includes(ui)) clearInterval(polling);
    }

    // Atualiza seção de embarque independente de mudança de status
    updateBoardSection(data.ride);

    // Quando completado: mostra botão de avaliação
    if (ui === "completed") {
        const rateSection = document.getElementById("rate-section");
        if (rateSection) {
            rateSection.classList.remove("hidden");
            const rateLink = document.getElementById("rate-link");
            if (rateLink && data.ride?.rate_url) rateLink.href = data.ride.rate_url;
        }
    }
}

function updateBoardSection(ride) {
    const boardSection   = document.getElementById("board-section");
    const boardedSection = document.getElementById("boarded-waiting-section");
    if (!ride) return;
    // "Embarquei": motorista chegou mas passageiro não confirmou
    boardSection?.classList.toggle("hidden",   !(ride.driver_arrived && !ride.passenger_boarded));
    // "Aguardando início": passageiro confirmou, viagem ainda não iniciou
    boardedSection?.classList.toggle("hidden", !(ride.passenger_boarded && ride.status === "accepted"));
}

const BANNER_CONFIG = {
    waiting:        { label: "⏳ Aguardando motorista",              sub: "Notificamos os motoristas disponíveis. Atualizando automaticamente...", color: "yellow",  pulse: true  },
    driver_found:   { label: "🚗 Motorista a caminho!",              sub: "Um motorista aceitou sua solicitação.",                                  color: "blue",    pulse: true  },
    driver_arrived: { label: "📍 Motorista chegou!",                 sub: "Confirme que você entrou no veículo.",                                   color: "orange",  pulse: true  },
    boarded_waiting:{ label: "✅ Embarcado! Aguardando início...",   sub: "O motorista vai iniciar a viagem em breve.",                             color: "purple",  pulse: true  },
    in_progress:    { label: "📍 Em andamento",                      sub: "",                                                                       color: "green",   pulse: true  },
    completed:      { label: "✅ Viagem concluída",                  sub: "",                                                                       color: "emerald", pulse: false },
    cancelled:      { label: "✖ Carona cancelada",                  sub: "",                                                                       color: "gray",    pulse: false },
    rejected:       { label: "✖ Solicitação recusada",              sub: "",                                                                       color: "red",     pulse: false },
};

const COLOR_MAP = {
    yellow:  { bg: "bg-yellow-50",  border: "border-yellow-300",  text: "text-yellow-800",  dot: "bg-yellow-400"  },
    blue:    { bg: "bg-blue-50",    border: "border-blue-300",    text: "text-blue-800",    dot: "bg-blue-500"    },
    orange:  { bg: "bg-orange-50",  border: "border-orange-300",  text: "text-orange-800",  dot: "bg-orange-500"  },
    purple:  { bg: "bg-purple-50",  border: "border-purple-300",  text: "text-purple-800",  dot: "bg-purple-500"  },
    green:   { bg: "bg-green-50",   border: "border-green-300",   text: "text-green-800",   dot: "bg-green-500"   },
    emerald: { bg: "bg-emerald-50", border: "border-emerald-300", text: "text-emerald-800", dot: "bg-emerald-500" },
    gray:    { bg: "bg-gray-100",   border: "border-gray-300",    text: "text-gray-600",    dot: "bg-gray-400"    },
    red:     { bg: "bg-red-50",     border: "border-red-300",     text: "text-red-700",     dot: "bg-red-500"     },
};

function updateBanner(ui) {
    const cfg = BANNER_CONFIG[ui];
    const col = COLOR_MAP[cfg.color];
    const banner = document.getElementById("status-banner");

    banner.className = `flex items-center gap-3 rounded-2xl border px-4 py-3 ${col.bg} ${col.border}`;

    const dotHtml = cfg.pulse
        ? `<span class="relative flex h-3 w-3 flex-shrink-0">
               <span class="animate-ping absolute inline-flex h-full w-full rounded-full ${col.dot} opacity-75"></span>
               <span class="relative inline-flex rounded-full h-3 w-3 ${col.dot}"></span>
           </span>`
        : `<span class="inline-flex h-3 w-3 rounded-full flex-shrink-0 ${col.dot}"></span>`;

    const subHtml = cfg.sub
        ? `<p class="text-xs ${col.text} opacity-75 mt-0.5">${cfg.sub}</p>`
        : "";

    banner.innerHTML = `${dotHtml}
        <div>
            <p class="font-semibold text-sm ${col.text}">${cfg.label}</p>
            ${subHtml}
        </div>`;
}

function updateDriverCard(ride) {
    const card = document.getElementById("driver-card");
    if (!ride || !["driver_found","driver_arrived","boarded_waiting","in_progress"].includes(currentStatus)) {
        card.classList.add("hidden"); return;
    }
    card.classList.remove("hidden");

    const avatar  = `https://ui-avatars.com/api/?name=${encodeURIComponent(ride.driver.name)}&background=2563eb&color=fff&size=64`;
    const imgSrc  = ride.driver.avatar || avatar;
    const vehicle = ride.vehicle
        ? `${ride.vehicle.model}${ride.vehicle.color ? ' · ' + ride.vehicle.color : ''} · ${ride.vehicle.plate}`
        : "";

    document.getElementById("driver-avatar").src = imgSrc;
    document.getElementById("driver-name").textContent  = ride.driver.name;
    document.getElementById("vehicle-info").textContent = vehicle;

    const inner = document.getElementById("driver-card-inner");
    if (inner) inner.onclick = () => showReputation(ride.driver.id);

    const ratingEl = document.getElementById("driver-rating");
    if (ratingEl) {
        if (ride.driver.total_ratings > 0) {
            ratingEl.className = "text-xs mt-0.5 text-yellow-500";
            ratingEl.innerHTML = `★ ${ride.driver.avg_stars} <span class="text-gray-400">(${ride.driver.total_ratings})</span>`;
        } else {
            ratingEl.className = "text-xs mt-0.5 text-gray-400";
            ratingEl.textContent = "Novo motorista";
        }
    }
}

function updateCancelSection(reqStatus, rideStatus) {
    const section = document.getElementById("cancel-section");
    const can = ["pending","accepted"].includes(reqStatus)
             && !["in_progress","completed","cancelled"].includes(rideStatus ?? "");
    section.classList.toggle("hidden", !can);
}

// ── Cancelamento ─────────────────────────────────────────────────────────────
document.getElementById("cancel-btn").addEventListener("click", async () => {
    const btn = document.getElementById("cancel-btn");
    btn.disabled = true;
    btn.textContent = "Cancelando...";
    btn.className = "w-full border border-gray-300 text-gray-400 bg-gray-50 font-medium py-3 rounded-xl text-sm cursor-not-allowed";

    try {
        const res = await fetch(cancelUrl, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrfToken, "Accept": "application/json" },
        });
        if (res.ok) {
            currentStatus = "cancelled";
            clearInterval(polling);
            updateBanner("cancelled");
            document.getElementById("cancel-section").classList.add("hidden");
        } else {
            btn.disabled = false;
            btn.textContent = "Cancelar carona";
            btn.className = "w-full border border-red-300 text-red-600 hover:bg-red-50 font-medium py-3 rounded-xl transition text-sm";
        }
    } catch {
        btn.disabled = false;
        btn.textContent = "Cancelar carona";
        btn.className = "w-full border border-red-300 text-red-600 hover:bg-red-50 font-medium py-3 rounded-xl transition text-sm";
    }
});

// ── Inicia polling ───────────────────────────────────────────────────────────
startPolling();
</script>
@endpush
