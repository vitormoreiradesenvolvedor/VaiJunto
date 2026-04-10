@extends('layouts.app')

@section('title', 'Minha Rota Fixa')

@section('content')

@php
    $pending  = $route->requests->where('status', 'pending');
    $accepted = $route->requests->where('status', 'accepted');
    $dayNames = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];

    $statusCfg = [
        'active' => ['label'=>'Ativa',  'bg'=>'bg-green-100','text'=>'text-green-700'],
        'paused' => ['label'=>'Pausada','bg'=>'bg-orange-100','text'=>'text-orange-700'],
    ];
    $sc = $statusCfg[$route->status] ?? $statusCfg['paused'];
@endphp

<div class="max-w-xl mx-auto space-y-4">

    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Dashboard</a>
        <h2 class="text-xl font-bold text-gray-900 flex-1">Rota Fixa</h2>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">{{ $sc['label'] }}</span>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Mapa --}}
    @if($mapsKey)
    <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
        <div id="route-map" class="w-full h-48 bg-gray-100"></div>
    </div>
    @endif

    {{-- Detalhes --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-shrink-0 mt-1.5"></span>
            <div><p class="text-xs text-gray-400">Origem</p><p class="text-sm font-medium text-gray-800">{{ $route->origin }}</p></div>
        </div>
        <div class="ml-[4.5px] h-3 border-l-2 border-dashed border-gray-200"></div>
        <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0 mt-1.5"></span>
            <div><p class="text-xs text-gray-400">Destino</p><p class="text-sm font-medium text-gray-800">{{ $route->destination }}</p></div>
        </div>
        <div class="border-t border-gray-100 pt-3 grid grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">Horário</p>
                <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($route->departure_time)->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Vagas</p>
                <p class="font-medium text-gray-800">{{ $route->available_seats }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dias</p>
                <p class="font-medium text-gray-800">{{ $route->days_label }}</p>
            </div>
        </div>
    </div>

    {{-- Passageiros confirmados --}}
    @if($accepted->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Confirmados ({{ $accepted->count() }})</h3>
        <div class="space-y-2">
            @foreach($accepted as $req)
            <div class="flex items-center gap-3">
                <img src="{{ $req->passenger->avatar ?? '' }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($req->passenger->name) }}&background=2563eb&color=fff&size=40'"
                     class="w-9 h-9 rounded-full object-cover border border-gray-200">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $req->passenger->name }}</p>
                    <p class="text-xs text-gray-400">{{ $req->scheduled_for->format('d/m H:i') }}</p>
                </div>
                <span class="text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">Confirmado</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Solicitações pendentes --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Solicitações pendentes ({{ $pending->count() }})
        </h3>
        <div class="space-y-3" id="pending-list">
            @forelse($pending as $req)
            <div class="flex items-center gap-3" id="req-{{ $req->id }}">
                <img src="{{ $req->passenger->avatar ?? '' }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($req->passenger->name) }}&background=e5e7eb&color=374151&size=40'"
                     class="w-9 h-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $req->passenger->name }}</p>
                    <p class="text-xs text-gray-400">Para {{ $req->scheduled_for->format('d/m H:i') }}</p>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button onclick="acceptReq({{ $route->id }}, {{ $req->id }})"
                            class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                        Aceitar
                    </button>
                    <button onclick="rejectReq({{ $route->id }}, {{ $req->id }})"
                            class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium rounded-lg transition">
                        Recusar
                    </button>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-2" id="empty-msg">Nenhuma solicitação pendente.</p>
            @endforelse
        </div>
    </div>

    {{-- Pausar / Reativar --}}
    <button id="toggle-btn"
            onclick="toggleStatus({{ $route->id }})"
            class="w-full font-medium py-3 rounded-xl transition text-sm border
                {{ $route->status === 'active'
                    ? 'border-orange-300 text-orange-600 hover:bg-orange-50'
                    : 'border-green-300 text-green-600 hover:bg-green-50' }}">
        {{ $route->status === 'active' ? '⏸ Pausar rota' : '▶ Reativar rota' }}
    </button>

</div>

@endsection

@push('scripts')
@if($mapsKey)
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

async function initMap() {
    const { Map } = await google.maps.importLibrary("maps");
    const o = "{{ $route->origin_coords }}".split(",").map(Number);
    const d = "{{ $route->destination_coords }}".split(",").map(Number);
    const origin = o[0] ? { lat: o[0], lng: o[1] } : null;
    const dest   = d[0] ? { lat: d[0], lng: d[1] } : null;

    const map = new Map(document.getElementById("route-map"), {
        center: origin ?? dest ?? { lat: -21.2342, lng: -44.9998 }, zoom: 13,
        mapTypeControl: false, streetViewControl: false,
        fullscreenControl: false, zoomControl: false,
        gestureHandling: "cooperative",
    });

    const mkr = (color) => ({ path: google.maps.SymbolPath.CIRCLE, scale: 9,
        fillColor: color, fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 });

    if (origin) new google.maps.Marker({ map, position: origin, icon: mkr("#16a34a") });
    if (dest)   new google.maps.Marker({ map, position: dest,   icon: mkr("#dc2626") });

    if (origin && dest) {
        const b = new google.maps.LatLngBounds();
        b.extend(origin); b.extend(dest); map.fitBounds(b, 48);
        try {
            const r = await new google.maps.DirectionsService().route({
                origin, destination: dest, travelMode: google.maps.TravelMode.DRIVING,
            });
            new google.maps.Polyline({ path: r.routes[0].overview_path,
                strokeColor: "#2563EB", strokeWeight: 4, strokeOpacity: 0.75, map });
        } catch {}
    }
}
initMap();
</script>
@endif

<script>
const csrf = document.querySelector("meta[name='csrf-token']").content;
let routeStatus = "{{ $route->status }}";

async function acceptReq(routeId, reqId) {
    const res = await fetch(`/routes/${routeId}/requests/${reqId}/accept`, {
        method: "POST", headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });
    if (res.ok) { document.getElementById(`req-${reqId}`)?.remove(); checkEmpty(); }
    else { alert("Erro ao aceitar."); }
}

async function rejectReq(routeId, reqId) {
    const res = await fetch(`/routes/${routeId}/requests/${reqId}/reject`, {
        method: "POST", headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });
    if (res.ok) { document.getElementById(`req-${reqId}`)?.remove(); checkEmpty(); }
    else { alert("Erro ao recusar."); }
}

function checkEmpty() {
    const list = document.getElementById("pending-list");
    if (list && list.children.length === 0) {
        const p = document.createElement("p");
        p.id = "empty-msg";
        p.className = "text-sm text-gray-400 text-center py-2";
        p.textContent = "Nenhuma solicitação pendente.";
        list.appendChild(p);
    }
}

async function toggleStatus(routeId) {
    const btn = document.getElementById("toggle-btn");
    btn.disabled = true;
    const res = await fetch(`/routes/${routeId}/toggle`, {
        method: "PATCH", headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });
    if (res.ok) {
        const data = await res.json();
        routeStatus = data.status;
        if (data.status === "active") {
            btn.textContent = "⏸ Pausar rota";
            btn.className = btn.className.replace(/border-green-\d+|text-green-\d+|hover:bg-green-\d+/g, "")
                + " border-orange-300 text-orange-600 hover:bg-orange-50";
        } else {
            btn.textContent = "▶ Reativar rota";
            btn.className = btn.className.replace(/border-orange-\d+|text-orange-\d+|hover:bg-orange-\d+/g, "")
                + " border-green-300 text-green-600 hover:bg-green-50";
        }
    }
    btn.disabled = false;
}

// Recebe nova solicitação em tempo real
window.addEventListener('echo:NewRideRequestForDriver', (ev) => {
    const e = ev.detail;
    if (e.fixed_route_id != {{ $route->id }}) return; // não é desta rota

    const list  = document.getElementById("pending-list");
    const empty = document.getElementById("empty-msg");
    if (empty) empty.remove();

    const el = document.createElement("div");
    el.id = `req-${e.id}`;
    el.className = "flex items-center gap-3";
    el.innerHTML = `
        <img src="${e.passenger?.avatar ?? ''}"
             onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(e.passenger?.name ?? '?')}&background=e5e7eb&color=374151&size=40'"
             class="w-9 h-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">${e.passenger?.name ?? '—'}</p>
            <p class="text-xs text-gray-400">Solicitação nova</p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <button onclick="acceptReq({{ $route->id }}, ${e.id})"
                    class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">Aceitar</button>
            <button onclick="rejectReq({{ $route->id }}, ${e.id})"
                    class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium rounded-lg transition">Recusar</button>
        </div>`;
    list.prepend(el);
});
</script>
@endpush
