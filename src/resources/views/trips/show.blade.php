@extends('layouts.app')

@section('title', 'Minha Viagem')

@section('content')

@php
    $pending  = $trip->requests->where('status', 'pending');
    $accepted = $trip->requests->where('status', 'accepted');
    $seatsAvail = max(0, $trip->seats_total - $accepted->count());

    $statusConfig = [
        'open'      => ['label' => 'Aberta',    'bg' => 'bg-green-100',  'text' => 'text-green-700'],
        'full'      => ['label' => 'Lotada',    'bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
        'departed'  => ['label' => 'Partiu',    'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
        'cancelled' => ['label' => 'Cancelada', 'bg' => 'bg-gray-100',   'text' => 'text-gray-600'],
    ];
    $sc = $statusConfig[$trip->status];
@endphp

<div class="max-w-xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Dashboard</a>
        <h2 class="text-xl font-bold text-gray-900 flex-1">Minha Viagem</h2>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">
            {{ $sc['label'] }}
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mapa --}}
    @if($mapsKey)
    <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
        <div id="trip-map" class="w-full h-48 bg-gray-100"></div>
    </div>
    @endif

    {{-- Detalhes --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-shrink-0 mt-1.5"></span>
            <div><p class="text-xs text-gray-400">Origem</p><p class="text-sm font-medium text-gray-800">{{ $trip->origin }}</p></div>
        </div>
        <div class="ml-[4.5px] h-3 border-l-2 border-dashed border-gray-200"></div>
        <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-sm bg-gray-800 flex-shrink-0 mt-1.5"></span>
            <div><p class="text-xs text-gray-400">Destino</p><p class="text-sm font-medium text-gray-800">{{ $trip->destination }}</p></div>
        </div>
        <div class="border-t border-gray-100 pt-3 flex gap-6 text-sm flex-wrap">
            <div><p class="text-xs text-gray-400">Partida</p><p class="font-medium text-gray-800">{{ $trip->departs_at->format('d/m H:i') }}</p></div>
            <div><p class="text-xs text-gray-400">Vagas totais</p><p class="font-medium text-gray-800">{{ $trip->seats_total }}</p></div>
            <div><p class="text-xs text-gray-400">Vagas livres</p><p class="font-medium {{ $seatsAvail > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $seatsAvail }}</p></div>
            @if($trip->vehicle)
            <div><p class="text-xs text-gray-400">Veículo</p><p class="font-medium text-gray-800">{{ $trip->vehicle->model }} · {{ $trip->vehicle->plate }}</p></div>
            @endif
        </div>
    </div>

    {{-- Passageiros aceitos --}}
    @if($accepted->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Passageiros confirmados ({{ $accepted->count() }})</h3>
        <div class="space-y-2">
            @foreach($accepted as $req)
            <div class="flex items-center gap-3">
                <img src="{{ $req->passenger->avatar ?? '' }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($req->passenger->name) }}&background=2563eb&color=fff&size=40'"
                     class="w-9 h-9 rounded-full object-cover border border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $req->passenger->name }}</p>
                    <p class="text-xs text-gray-400">{{ $req->passenger->email }}</p>
                </div>
                <span class="ml-auto text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">Confirmado</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Solicitações pendentes --}}
    @if($pending->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Solicitações pendentes ({{ $pending->count() }})
        </h3>
        <div class="space-y-3" id="pending-list">
            @foreach($pending as $req)
            <div class="flex items-center gap-3" id="req-{{ $req->id }}">
                <img src="{{ $req->passenger->avatar ?? '' }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($req->passenger->name) }}&background=e5e7eb&color=374151&size=40'"
                     class="w-9 h-9 rounded-full object-cover border border-gray-200">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $req->passenger->name }}</p>
                    <p class="text-xs text-gray-400">{{ $req->passenger->email }}</p>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button onclick="acceptReq({{ $trip->id }}, {{ $req->id }})"
                            class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                        Aceitar
                    </button>
                    <button onclick="rejectReq({{ $trip->id }}, {{ $req->id }})"
                            class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium rounded-lg transition">
                        Recusar
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($pending->isEmpty() && $accepted->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-400 text-sm">
        Nenhum passageiro ainda. Compartilhe a viagem para receber solicitações!
    </div>
    @endif

    {{-- Cancelar viagem --}}
    @if(in_array($trip->status, ['open','full']))
    <div id="cancel-section">
        <button id="cancel-btn"
                class="w-full border border-red-300 text-red-600 hover:bg-red-50 font-medium py-3 rounded-xl transition text-sm">
            Cancelar viagem
        </button>
        <div id="cancel-confirm" class="hidden mt-3 bg-red-50 border border-red-200 rounded-xl p-4 space-y-3">
            <p class="text-sm text-red-700 font-medium">Cancelar a viagem notificará todos os passageiros confirmados. Continuar?</p>
            <div class="flex gap-2">
                <button id="cancel-yes"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-lg transition">
                    Sim, cancelar
                </button>
                <button id="cancel-no"
                        class="flex-1 border border-gray-300 text-gray-600 text-sm font-medium py-2 rounded-lg hover:bg-gray-50 transition">
                    Não
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

@endsection

@push('scripts')
@if($mapsKey)
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

async function initMap() {
    const { Map } = await google.maps.importLibrary("maps");
    const origin = "{{ $trip->origin_coords }}".split(",").map(Number);
    const dest   = "{{ $trip->destination_coords ?? '' }}".split(",").map(Number);
    const o = origin.length === 2 && origin[0] ? { lat: origin[0], lng: origin[1] } : null;
    const d = dest.length   === 2 && dest[0]   ? { lat: dest[0],   lng: dest[1]   } : null;
    const map = new Map(document.getElementById("trip-map"), {
        center: o ?? d ?? { lat: -21.2342, lng: -44.9998 }, zoom: 13,
        mapTypeControl: false, streetViewControl: false, fullscreenControl: false,
        zoomControl: false, gestureHandling: "cooperative",
    });
    if (o) new google.maps.Marker({ map, position: o, icon: { path: google.maps.SymbolPath.CIRCLE, scale: 9, fillColor: "#16a34a", fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 } });
    if (d) new google.maps.Marker({ map, position: d, icon: { path: google.maps.SymbolPath.CIRCLE, scale: 9, fillColor: "#dc2626", fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 } });
    if (o && d) {
        const b = new google.maps.LatLngBounds(); b.extend(o); b.extend(d); map.fitBounds(b, 48);
        try {
            const r = await new google.maps.DirectionsService().route({ origin: o, destination: d, travelMode: google.maps.TravelMode.DRIVING });
            new google.maps.Polyline({ path: r.routes[0].overview_path, strokeColor: "#2563EB", strokeWeight: 4, strokeOpacity: 0.8, map });
        } catch {}
    }
}
initMap();
</script>
@endif

<script>
const csrf = document.querySelector("meta[name='csrf-token']").content;

async function acceptReq(tripId, reqId) {
    const res = await fetch(`/trips/${tripId}/requests/${reqId}/accept`, {
        method: "POST", headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });
    if (res.ok) { location.reload(); }
    else { const d = await res.json(); alert(d.message ?? "Erro ao aceitar."); }
}

async function rejectReq(tripId, reqId) {
    const res = await fetch(`/trips/${tripId}/requests/${reqId}/reject`, {
        method: "POST", headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });
    if (res.ok) { document.getElementById(`req-${reqId}`)?.remove(); }
    else { alert("Erro ao recusar."); }
}

@if(in_array($trip->status, ['open','full']))
document.getElementById("cancel-btn").addEventListener("click", () => {
    document.getElementById("cancel-confirm").classList.remove("hidden");
    document.getElementById("cancel-btn").classList.add("hidden");
});
document.getElementById("cancel-no").addEventListener("click", () => {
    document.getElementById("cancel-confirm").classList.add("hidden");
    document.getElementById("cancel-btn").classList.remove("hidden");
});
document.getElementById("cancel-yes").addEventListener("click", async () => {
    const btn = document.getElementById("cancel-yes");
    btn.disabled = true; btn.textContent = "Cancelando...";
    const res = await fetch("{{ route('trips.cancel', $trip) }}", {
        method: "POST", headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });
    if (res.ok) { window.location.href = "{{ route('dashboard') }}"; }
    else { btn.disabled = false; btn.textContent = "Sim, cancelar"; alert("Erro ao cancelar."); }
});
@endif
</script>
@endpush
