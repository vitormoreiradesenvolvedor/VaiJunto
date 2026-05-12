@extends('layouts.app')

@section('title', 'Gerenciar Carona')

@section('content')

@php
    $req    = $ride->rideRequest;
    $origin = $req?->origin_coords ?? '0,0';
    $dest   = $req?->destination_coords ?? '0,0';
@endphp

<div class="max-w-xl mx-auto space-y-4" id="drive-root"
     data-ride-id="{{ $ride->id }}"
     data-status="{{ $ride->status }}"
     data-arrived="{{ $ride->arrived_at ? 'true' : 'false' }}"
     data-boarded="{{ $ride->passenger_boarded_at ? 'true' : 'false' }}"
     data-arrived-url="{{ route('rides.arrived', $ride) }}"
     data-status-driver-url="{{ route('rides.status-driver', $ride) }}"
     data-start-url="{{ route('rides.start', $ride) }}"
     data-finish-url="{{ route('rides.finish', $ride) }}"
     data-location-url="{{ route('rides.location', $ride) }}"
     data-cancel-url="{{ url('/rides/' . $ride->id . '/cancel') }}">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Dashboard</a>
        <h2 class="text-xl font-bold text-gray-900">Gerenciar Carona</h2>
    </div>

    {{-- Status banner --}}
    <div id="status-banner" class="flex items-center gap-3 rounded-2xl border px-4 py-3
        {{ $ride->status === 'accepted' ? 'bg-blue-50 border-blue-300' : 'bg-green-50 border-green-300' }}">
        <span class="relative flex h-3 w-3 flex-shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full
                {{ $ride->status === 'accepted' ? 'bg-blue-500' : 'bg-green-500' }} opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3
                {{ $ride->status === 'accepted' ? 'bg-blue-500' : 'bg-green-500' }}"></span>
        </span>
        <p id="status-label" class="font-semibold text-sm
            {{ $ride->status === 'accepted' ? 'text-blue-800' : 'text-green-800' }}">
            {{ $ride->status === 'accepted' ? '🚗 Passageiro aguardando — a caminho do local' : '📍 Viagem em andamento' }}
        </p>
    </div>

    {{-- Mapa --}}
    @if($mapsKey)
    <div id="drive-map-container" class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm" style="transition: all 0.5s ease;">
        <div id="drive-map" class="w-full bg-gray-100" style="height: 288px; transition: height 0.5s ease;"></div>
    </div>
    @endif

    {{-- Informações da rota --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Rota</h3>
        <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-shrink-0 mt-1.5"></span>
            <div>
                <p class="text-xs text-gray-400">Origem</p>
                <p class="text-sm font-medium text-gray-800">{{ $req?->origin ?? '—' }}</p>
            </div>
        </div>
        <div class="ml-[4.5px] border-l-2 border-dashed border-gray-200 h-4"></div>
        <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0 mt-1.5"></span>
            <div>
                <p class="text-xs text-gray-400">Destino</p>
                <p class="text-sm font-medium text-gray-800">{{ $req?->destination ?? '—' }}</p>
            </div>
        </div>
        <div class="border-t border-gray-100 pt-3 flex gap-6 text-sm">
            <div>
                <p class="text-xs text-gray-400">Horário</p>
                <p class="font-medium text-gray-800">{{ $req?->scheduled_for?->format('d/m H:i') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Assentos</p>
                <p class="font-medium text-gray-800">{{ $req?->seats_needed ?? 1 }}</p>
            </div>
        </div>
    </div>

    {{-- Passageiro --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Passageiro</h3>
        <div class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition"
             onclick="showReputation({{ $ride->passenger_id }})"
             title="Ver reputação">
            <img src="{{ $ride->passenger?->avatar ?? '' }}"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($ride->passenger?->name ?? 'P') }}&background=e5e7eb&color=374151&size=64'"
                 alt="Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
            <div>
                <p class="font-semibold text-gray-900 text-sm">{{ $ride->passenger?->name ?? '—' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $ride->passenger?->email ?? '' }}</p>
            </div>
        </div>
    </div>

    {{-- Indicador de GPS (sempre visível) --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 px-1">
        <span id="gps-dot" class="w-2 h-2 rounded-full bg-gray-300"></span>
        <span id="gps-label">GPS iniciando...</span>
    </div>

    {{-- Botões de ação --}}
    <div id="action-section" class="space-y-2">

        {{-- Fase 1: a caminho do passageiro (accepted, ainda não chegou) --}}
        <button id="btn-arrived"
                class="{{ ($ride->status === 'accepted' && !$ride->arrived_at) ? '' : 'hidden' }}
                       w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-xl transition text-sm shadow">
            📍 Cheguei ao ponto de embarque
        </button>

        {{-- Fase 2: aguardando passageiro embarcar --}}
        <div id="waiting-board"
             class="{{ ($ride->status === 'accepted' && $ride->arrived_at && !$ride->passenger_boarded_at) ? '' : 'hidden' }}
                    bg-yellow-50 border border-yellow-300 rounded-xl p-4 text-center">
            <p class="text-sm font-semibold text-yellow-800">⏳ Aguardando passageiro confirmar embarque...</p>
            <p class="text-xs text-yellow-600 mt-1">O passageiro precisa clicar em "Embarquei" na tela dele.</p>
        </div>

        {{-- Fase 3: passageiro embarcou — pode iniciar --}}
        <button id="btn-start"
                class="{{ ($ride->status === 'accepted' && $ride->passenger_boarded_at) ? '' : 'hidden' }}
                       w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3.5 rounded-xl transition text-sm shadow">
            ▶ Iniciar Viagem
        </button>

        {{-- Fase 4: em andamento — finalizar --}}
        <div id="gps-section" class="{{ $ride->status === 'in_progress' ? '' : 'hidden' }}">
            <button id="btn-finish"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-xl transition text-sm shadow">
                ✓ Finalizar Viagem
            </button>
        </div>

        {{-- Cancelar (visível quando accepted ou in_progress) --}}
        <div id="cancel-section">
            <button id="cancel-btn"
                    class="w-full border border-red-300 text-red-600 hover:bg-red-50 font-medium py-3 rounded-xl transition text-sm">
                Cancelar carona
            </button>
            <div id="cancel-confirm" class="hidden mt-3 bg-red-50 border border-red-200 rounded-xl p-4 space-y-3">
                <p class="text-sm text-red-700 font-medium">Confirmar cancelamento? O passageiro será notificado.</p>
                <textarea id="cancel-reason" rows="2" placeholder="Motivo (obrigatório)..."
                          class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                <div class="flex gap-2">
                    <button id="cancel-yes"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-lg transition">
                        Cancelar carona
                    </button>
                    <button id="cancel-no"
                            class="flex-1 border border-gray-300 text-gray-700 text-sm font-medium py-2 rounded-lg hover:bg-gray-50 transition">
                        Voltar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
@if($mapsKey)
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

let driveMap, driverMarker, driveRouteLine, driveFallbackLine;
let routePoints     = [];   // pontos da rota atual (overview_path)
let lastRerouteTime = 0;
const REROUTE_COOLDOWN  = 20000; // ms entre recálculos
const DEVIATION_THRESHOLD = 80;  // metros fora da rota para recalcular

async function initDriveMap() {
    const { Map } = await google.maps.importLibrary("maps");

    const originParts = "{{ $origin }}".split(",").map(Number);
    const destParts   = "{{ $dest }}".split(",").map(Number);

    const origin = originParts.length === 2 && originParts[0] !== 0
        ? { lat: originParts[0], lng: originParts[1] } : null;
    const dest   = destParts.length === 2 && destParts[0] !== 0
        ? { lat: destParts[0], lng: destParts[1] } : null;

    const center = origin ?? dest ?? { lat: -21.2342, lng: -44.9998 };

    driveMap = new Map(document.getElementById("drive-map"), {
        center, zoom: 13,
        mapTypeControl: false, streetViewControl: false,
        fullscreenControl: false, zoomControl: false,
        gestureHandling: "cooperative",
    });

    // Marcador de destino (vermelho — âncora fixa)
    if (dest) {
        new google.maps.Marker({
            map: driveMap, position: dest, title: "Destino",
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10,
                    fillColor: "#dc2626", fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 },
        });
    }

    // Marcador de origem/pickup (verde — âncora fixa)
    if (origin) {
        new google.maps.Marker({
            map: driveMap, position: origin, title: "Ponto de embarque",
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10,
                    fillColor: "#16a34a", fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 },
        });
    }

    // Marcador do carro do motorista
    driverMarker = new google.maps.Marker({
        map: driveMap, title: "Você",
        icon: {
            url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40">
                    <circle cx="20" cy="20" r="18" fill="#2563EB" stroke="white" stroke-width="2.5"/>
                    <text x="20" y="26" text-anchor="middle" font-size="18" fill="white">🚗</text>
                </svg>`),
            scaledSize: new google.maps.Size(40, 40),
            anchor: new google.maps.Point(20, 20),
        },
        position: center,
    });

    if (origin && dest) {
        await drawRoute(origin, dest, "#2563EB", true);
    }

    // Aguarda o mapa terminar de renderizar e reenquadra a rota
    google.maps.event.addListenerOnce(driveMap, 'tilesloaded', () => fitAllRoute());
    startAutoZoom();
}

// ── Auto-zoom: reenquadra rota + motorista a cada 5s ──────────────────────────
let autoZoomTimer = null;

function startAutoZoom() {
    if (autoZoomTimer) return;
    fitAllRoute();
    autoZoomTimer = setInterval(fitAllRoute, 5000);
}

function stopAutoZoom() {
    clearInterval(autoZoomTimer);
    autoZoomTimer = null;
}

function fitAllRoute() {
    if (!driveMap) return;
    const bounds = new google.maps.LatLngBounds();
    if (routePoints.length) {
        for (const pt of routePoints) bounds.extend(pt);
    } else {
        bounds.extend(pickup);
        bounds.extend(dropoff);
    }
    const pos = driverMarker?.getPosition?.();
    if (pos) bounds.extend(pos);
    if (!bounds.isEmpty()) driveMap.fitBounds(bounds, 48);
}

// ── Expande o mapa ao iniciar a viagem ────────────────────────────────────────
function expandMapForRide() {
    const mapEl = document.getElementById('drive-map');
    if (!mapEl) return;
    mapEl.style.height = 'calc(100vh - 220px)';
    mapEl.style.minHeight = '400px';
    google.maps.event.trigger(driveMap, 'resize');
    setTimeout(fitAllRoute, 350);
}

async function drawRoute(from, to, color = "#2563EB", fitRoute = false) {
    try {
        const res = await fetch(
            `/api/directions?origin=${from.lat},${from.lng}&destination=${to.lat},${to.lng}`
        );
        if (!res.ok) throw new Error("directions_error");
        const { path } = await res.json();
        routePoints = path;
        if (driveFallbackLine) { driveFallbackLine.setMap(null); driveFallbackLine = null; }
        if (!driveRouteLine) {
            driveRouteLine = new google.maps.Polyline({
                path, strokeColor: color, strokeWeight: 5, strokeOpacity: 0.9,
                geodesic: false, map: driveMap,
            });
        } else {
            driveRouteLine.setPath(path);
            driveRouteLine.setOptions({ strokeColor: color });
        }
        if (fitRoute) {
            const bounds = new google.maps.LatLngBounds();
            for (const pt of path) bounds.extend(pt);
            driveMap.fitBounds(bounds, 48);
        }
    } catch {
        if (driveRouteLine) { driveRouteLine.setMap(null); driveRouteLine = null; }
        if (driveFallbackLine) driveFallbackLine.setMap(null);
        routePoints = [];
        driveFallbackLine = new google.maps.Polyline({
            path: [from, to],
            strokeColor: color, strokeWeight: 4, strokeOpacity: 0.75, geodesic: true,
            map: driveMap,
        });
        if (fitRoute) {
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(from); bounds.extend(to);
            driveMap.fitBounds(bounds, 48);
        }
    }
}

initDriveMap();

// ── Utilitário: distância em metros (Haversine) ───────────────────────────────
function haversineM(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const f1 = lat1 * Math.PI / 180, f2 = lat2 * Math.PI / 180;
    const df = (lat2 - lat1) * Math.PI / 180;
    const dl = (lng2 - lng1) * Math.PI / 180;
    const a  = Math.sin(df/2)**2 + Math.cos(f1)*Math.cos(f2)*Math.sin(dl/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function minDistToRoute(lat, lng) {
    if (!routePoints.length) return 0;
    let min = Infinity;
    for (const pt of routePoints) {
        min = Math.min(min, haversineM(lat, lng, pt.lat, pt.lng));
    }
    return min;
}

// ── Recálculo de rota em tempo real ──────────────────────────────────────────
async function checkAndReroute(lat, lng) {
    if (rideStatus !== 'in_progress') return; // só recalcula durante a viagem
    const now = Date.now();
    if (now - lastRerouteTime < REROUTE_COOLDOWN) return;

    const devDist = minDistToRoute(lat, lng);
    if (devDist > DEVIATION_THRESHOLD) {
        lastRerouteTime = now;
        const dest = dropoff;
        setGpsStatus("🔄 Recalculando rota...", "bg-orange-400");
        await drawRoute({ lat, lng }, dest, "#f97316"); // laranja = rota recalculada
        setGpsStatus("🔄 Rota atualizada", "bg-orange-400");
        setTimeout(() => setGpsStatus("GPS ativo", "bg-green-500"), 3000);
    }
}

// Atualiza posição do marcador do motorista no mapa
function moveDriverMarker(lat, lng) {
    if (!driverMarker) return;
    driverMarker.setPosition({ lat, lng });
}
</script>
@endif

<script>
const root        = document.getElementById("drive-root");
const csrf        = document.querySelector("meta[name='csrf-token']").content;
const arrivedUrl  = root.dataset.arrivedUrl;
const startUrl    = root.dataset.startUrl;
const finishUrl   = root.dataset.finishUrl;
const locationUrl = root.dataset.locationUrl;
const cancelUrl   = root.dataset.cancelUrl;

let rideStatus  = root.dataset.status;           // 'accepted' | 'in_progress'
let driverArrived  = root.dataset.arrived === 'true';
let passengerBoarded = root.dataset.boarded === 'true';
let locationTimer   = null;
let statusPoller    = null;
let simPhase        = null; // 'to_pickup' | 'to_dest'

// ── Polling de status (fallback para Echo falhar) ─────────────────────────────
const statusDriverUrl = root.dataset.statusDriverUrl;

function startStatusPolling() {
    if (statusPoller) return;
    statusPoller = setInterval(async () => {
        try {
            const res  = await fetch(statusDriverUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (data.passenger_boarded && !passengerBoarded) {
                passengerBoarded = true;
                document.getElementById("waiting-board").classList.add("hidden");
                document.getElementById("btn-start").classList.remove("hidden");
                updateStatusBanner("boarded");
                stopStatusPolling();
            }
        } catch { /* ignora */ }
    }, 5000);
}

function stopStatusPolling() {
    clearInterval(statusPoller);
    statusPoller = null;
}

// ── Cheguei ao ponto de embarque ──────────────────────────────────────────────
document.getElementById("btn-arrived").addEventListener("click", async () => {
    const btn = document.getElementById("btn-arrived");
    btn.disabled = true; btn.textContent = "Registrando...";

    const res = await fetch(arrivedUrl, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });

    if (res.ok) {
        driverArrived = true;
        stopGPS(); // para simulação de ida
        btn.classList.add("hidden");
        document.getElementById("waiting-board").classList.remove("hidden");
        updateStatusBanner("arrived");
        startStatusPolling(); // fallback caso Echo não entregue PassengerBoarded
    } else {
        btn.disabled = false; btn.textContent = "📍 Cheguei ao ponto de embarque";
    }
});

// ── Passageiro cancelou a carona ──────────────────────────────────────────────
window.addEventListener('echo:RideCancelledByPassenger', () => {
    stopGPS();
    stopStatusPolling();
    document.getElementById('action-section').innerHTML =
        '<div class="bg-red-50 border border-red-300 rounded-xl p-4 text-center text-red-700 text-sm font-medium">❌ O passageiro cancelou a carona.</div>';
    updateStatusBanner('cancelled');
    setTimeout(() => { window.location.href = "{{ route('dashboard') }}"; }, 3000);
});

// ── Passageiro embarcou (recebido via Echo) ───────────────────────────────────
window.addEventListener('echo:PassengerBoarded', () => {
    if (passengerBoarded) return; // já tratado pelo polling
    passengerBoarded = true;
    stopStatusPolling();
    document.getElementById("waiting-board").classList.add("hidden");
    document.getElementById("btn-start").classList.remove("hidden");
    updateStatusBanner("boarded");
});

// ── Iniciar viagem ────────────────────────────────────────────────────────────
document.getElementById("btn-start").addEventListener("click", async () => {
    const btn = document.getElementById("btn-start");
    btn.disabled = true; btn.textContent = "Iniciando...";

    const res = await fetch(startUrl, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });

    if (res.ok) {
        rideStatus = "in_progress";
        btn.classList.add("hidden");
        document.getElementById("gps-section").classList.remove("hidden");
        updateStatusBanner("in_progress");
        if (typeof expandMapForRide === 'function') expandMapForRide();
        startGPS("to_dest"); // fase 2: pickup → destino
    } else {
        btn.disabled = false; btn.textContent = "▶ Iniciar Viagem";
        alert("Erro ao iniciar viagem.");
    }
});

// ── Finalizar viagem ──────────────────────────────────────────────────────────
document.getElementById("btn-finish").addEventListener("click", async () => {
    const btn = document.getElementById("btn-finish");
    btn.disabled = true; btn.textContent = "Finalizando...";

    const res = await fetch(finishUrl, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });

    if (res.ok) {
        stopGPS();
        if (typeof stopAutoZoom === 'function') stopAutoZoom();
        const data = await res.json();
        document.getElementById("action-section").classList.add("hidden");
        updateStatusBanner("completed");
        setTimeout(() => { window.location.href = data.rate_url; }, 1200);
    } else {
        btn.disabled = false; btn.textContent = "✓ Finalizar Viagem";
        alert("Erro ao finalizar viagem.");
    }
});

// ── GPS / Simulação ───────────────────────────────────────────────────────────
const DEV_MODE      = {{ $devMode ? 'true' : 'false' }};
const PICKUP_COORDS = "{{ $origin }}".split(",").map(Number); // onde buscar passageiro
const DROP_COORDS   = "{{ $dest }}".split(",").map(Number);   // destino final

const pickup = PICKUP_COORDS.length === 2 && PICKUP_COORDS[0]
    ? { lat: PICKUP_COORDS[0], lng: PICKUP_COORDS[1] }
    : { lat: -21.2342, lng: -44.9998 };

const dropoff = DROP_COORDS.length === 2 && DROP_COORDS[0]
    ? { lat: DROP_COORDS[0], lng: DROP_COORDS[1] }
    : { lat: pickup.lat + 0.015, lng: pickup.lng + 0.015 };

// Ponto de partida simulado do motorista: offset a partir do pickup
const startPoint = { lat: pickup.lat + 0.02, lng: pickup.lng - 0.02 };

// phase: 'to_pickup' (accepted) | 'to_dest' (in_progress)
function startGPS(phase = 'to_pickup') {
    stopGPS(); // garante que não há timer anterior rodando
    simPhase = phase;
    if (DEV_MODE) {
        startSimulation(phase);
    } else {
        startRealGPS();
    }
}

function stopGPS() {
    clearInterval(locationTimer);
    locationTimer = null;
}

// ── GPS real (produção) ───────────────────────────────────────────────────────
function startRealGPS() {
    if (!navigator.geolocation) { setGpsStatus("GPS indisponível", "bg-red-400"); return; }
    setGpsStatus("Obtendo localização...", "bg-yellow-400");
    sendRealLocation();
    locationTimer = setInterval(sendRealLocation, 5000);
}

function sendRealLocation() {
    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            const { latitude: lat, longitude: lng } = pos.coords;
            if (typeof moveDriverMarker === 'function') moveDriverMarker(lat, lng);
            postLocation(lat, lng);
            setGpsStatus("GPS ativo", "bg-green-500");
            await checkAndReroute(lat, lng);
        },
        () => { setGpsStatus("Sem sinal GPS", "bg-orange-400"); },
        { enableHighAccuracy: true, timeout: 8000 }
    );
}

// ── Simulação de movimento em duas fases ──────────────────────────────────────
function startSimulation(phase) {
    const from = phase === 'to_pickup' ? startPoint : pickup;
    const to   = phase === 'to_pickup' ? pickup     : dropoff;
    const STEPS = 25;
    const INTERVAL = 2000;
    let step = 0;

    setGpsStatus(
        phase === 'to_pickup'
            ? "🧪 Simulando ida ao passageiro..."
            : "🧪 Simulando corrida até o destino...",
        "bg-purple-500"
    );

    const sendStep = () => {
        if (step > STEPS) {
            stopGPS();
            setGpsStatus("🧪 Fase concluída", "bg-gray-400");
            return;
        }
        const t   = step / STEPS;
        const lat = from.lat + (to.lat - from.lat) * t;
        const lng = from.lng + (to.lng - from.lng) * t;
        if (typeof moveDriverMarker === 'function') moveDriverMarker(lat, lng);
        postLocation(lat, lng);
        step++;
    };

    sendStep();
    locationTimer = setInterval(sendStep, INTERVAL);
}

// ── Envia coordenadas ao servidor ─────────────────────────────────────────────
function postLocation(lat, lng) {
    fetch(locationUrl, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({ lat, lng }),
    }).then(res => {
        // Ride cancelada/finalizada: para GPS imediatamente
        if (res.status === 422 || res.status === 403) stopGPS();
    });
}

function setGpsStatus(text, dotClass) {
    const dot   = document.getElementById("gps-dot");
    const label = document.getElementById("gps-label");
    dot.className   = `w-2 h-2 rounded-full ${dotClass}`;
    label.textContent = text;
}

// Inicia GPS imediatamente — transmite localização tanto em 'accepted'
// (a caminho do passageiro) quanto em 'in_progress' (durante a corrida)
startGPS();

// Se a página carregou já em in_progress, expande o mapa imediatamente
if (rideStatus === 'in_progress' && typeof expandMapForRide === 'function') {
    expandMapForRide();
}

// Se ao carregar, motorista já chegou mas passageiro não embarcou: inicia polling de embarque
if (driverArrived && !passengerBoarded && rideStatus === 'accepted') {
    startStatusPolling();
}

// ── Polling geral de status da corrida (detecta cancelamento pelo passageiro) ──
let rideStatusPoller = setInterval(async () => {
    if (!['accepted', 'in_progress'].includes(rideStatus)) {
        clearInterval(rideStatusPoller);
        return;
    }
    try {
        const res  = await fetch(statusDriverUrl, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        if (data.status === 'cancelled') {
            clearInterval(rideStatusPoller);
            stopGPS();
            stopStatusPolling();
            rideStatus = 'cancelled';
            updateStatusBanner('cancelled');
            document.getElementById('action-section').innerHTML =
                '<div class="bg-red-50 border border-red-300 rounded-xl p-4 text-center text-red-700 text-sm font-medium">❌ O passageiro cancelou a carona.</div>';
            setTimeout(() => { window.location.href = "{{ route('dashboard') }}"; }, 3000);
        }
    } catch { /* ignora */ }
}, 2000);

// ── Status banner ─────────────────────────────────────────────────────────────
function updateStatusBanner(status) {
    const banner = document.getElementById("status-banner");
    const label  = document.getElementById("status-label");

    const cfg = {
        accepted:    { bg: "bg-blue-50",    border: "border-blue-300",   dot: "bg-blue-500",   text: "text-blue-800",   msg: "🚗 A caminho do passageiro" },
        arrived:     { bg: "bg-orange-50",  border: "border-orange-300", dot: "bg-orange-400", text: "text-orange-800", msg: "📍 Chegou ao ponto — aguardando passageiro embarcar" },
        boarded:     { bg: "bg-purple-50",  border: "border-purple-300", dot: "bg-purple-500", text: "text-purple-800", msg: "✅ Passageiro embarcou — pode iniciar a viagem!" },
        in_progress: { bg: "bg-green-50",   border: "border-green-300",  dot: "bg-green-500",  text: "text-green-800",  msg: "📍 Viagem em andamento" },
        completed:   { bg: "bg-emerald-50", border: "border-emerald-300",dot: "bg-emerald-500",text: "text-emerald-800",msg: "✅ Viagem concluída! Redirecionando para avaliação..." },
        cancelled:   { bg: "bg-red-50",     border: "border-red-300",    dot: "bg-red-500",    text: "text-red-800",    msg: "❌ Carona cancelada." },
    };
    const c = cfg[status] ?? cfg.accepted;

    banner.className = `flex items-center gap-3 rounded-2xl border px-4 py-3 ${c.bg} ${c.border}`;
    label.className  = `font-semibold text-sm ${c.text}`;
    label.textContent = c.msg;
}

// ── Cancelar ──────────────────────────────────────────────────────────────────
document.getElementById("cancel-btn").addEventListener("click", () => {
    document.getElementById("cancel-confirm").classList.remove("hidden");
    document.getElementById("cancel-btn").classList.add("hidden");
});

document.getElementById("cancel-no").addEventListener("click", () => {
    document.getElementById("cancel-confirm").classList.add("hidden");
    document.getElementById("cancel-btn").classList.remove("hidden");
});

document.getElementById("cancel-yes").addEventListener("click", async () => {
    const reason = document.getElementById("cancel-reason").value.trim();
    if (!reason) { alert("Informe o motivo."); return; }

    const btn = document.getElementById("cancel-yes");
    btn.disabled = true; btn.textContent = "Cancelando...";

    const res = await fetch(cancelUrl, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrf,
            "Accept": "application/json",
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ reason }),
    });

    if (res.ok) {
        stopGPS();
        window.location.href = "{{ route('dashboard') }}";
    } else {
        btn.disabled = false; btn.textContent = "Cancelar carona";
        alert("Erro ao cancelar.");
    }
});
</script>
@endpush
