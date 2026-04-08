@extends('layouts.app')

@section('title', 'Solicitar Carona')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}"
           class="text-blue-600 hover:text-blue-800 text-sm transition">← Voltar</a>
        <h2 class="text-xl font-bold text-gray-900">Solicitar Carona</h2>
    </div>

    @if(!$mapsKey)
        <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg px-4 py-3 text-sm">
            <strong>Atenção:</strong> Configure <code>GOOGLE_MAPS_API_KEY</code> no <code>src/.env</code> para habilitar o mapa e o autocomplete de endereços.
        </div>
    @endif

    <div id="alert-success" class="hidden mb-4 bg-green-100 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
        Solicitação enviada com sucesso! Aguarde um motorista aceitar.
        <a href="{{ route('dashboard') }}" class="underline ml-1">Ver minhas solicitações</a>
    </div>
    <div id="alert-error" class="hidden mb-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm"></div>

    <form id="ride-form" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        {{-- Origem --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Origem</label>
            <div class="flex items-center gap-2">
                <div id="origin-ac-container" class="flex-1 min-w-0"></div>
                <button type="button" id="gps-btn"
                        title="Usar minha localização atual"
                        class="flex-shrink-0 p-2 rounded-lg border border-gray-300 text-blue-500 hover:bg-blue-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>
                    </svg>
                </button>
            </div>
            <p id="gps-status" class="text-xs text-gray-400 mt-1 hidden">Obtendo localização...</p>
            <input type="hidden" name="origin" id="origin-value">
            <input type="hidden" name="origin_coords" id="origin-coords">
        </div>

        {{-- Destino --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
            <div id="destination-ac-container" class="w-full"></div>
            <input type="hidden" name="destination" id="destination-value">
            <input type="hidden" name="destination_coords" id="destination-coords">
        </div>

        {{-- Mapa de preview --}}
        <div id="map-container" class="{{ $mapsKey ? '' : 'hidden' }}">
            <div id="map" class="w-full h-52 rounded-xl border border-gray-200 bg-gray-100"></div>
        </div>

        {{-- Data/hora e assentos --}}
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data e Hora</label>
                <input type="datetime-local" name="scheduled_for" id="scheduled_for" required
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assentos Necessários</label>
                <input type="number" name="seats_needed" id="seats_needed" required
                       min="1" max="6" value="1"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <button type="submit" id="submit-btn"
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium py-3 rounded-xl shadow transition text-sm">
            Solicitar Carona
        </button>
    </form>
</div>

{{-- Estilos para os web components do Maps --}}
<style>
    gmp-place-autocomplete {
        width: 100%;
        --gmp-place-autocomplete-background-color: #fff;
        --gmp-place-autocomplete-border-radius: 0.5rem;
        --gmp-place-autocomplete-font-size: 0.875rem;
    }
</style>

@endsection

@push('scripts')
@if($mapsKey)
{{-- Novo padrão de carregamento recomendado pelo Google (importLibrary) --}}
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

// ── Estado ────────────────────────────────────────────────
let map, originMarker, destinationMarker, routePolyline;
let originPlace = null, destinationPlace = null;
let originAC, destAC;

// ── Inicialização ─────────────────────────────────────────
async function initMaps() {
    const { Map }                      = await google.maps.importLibrary("maps");
    const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");
    const { Geocoder }                 = await google.maps.importLibrary("geocoding");

    // Mapa centrado em Lavras/MG
    map = new Map(document.getElementById("map"), {
        center: { lat: -21.2342, lng: -44.9998 },
        zoom: 13,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        gestureHandling: "cooperative",
    });

    // ── PlaceAutocompleteElement — Origem ─────────────────
    originAC = new PlaceAutocompleteElement({ requestedRegion: "br" });
    document.getElementById("origin-ac-container").appendChild(originAC);

    originAC.addEventListener("gmp-select", async (e) => {
        const place = e.placePrediction.toPlace();
        await place.fetchFields(["formattedAddress", "location"]);
        setOrigin(place.formattedAddress, place.location.lat(), place.location.lng());
    });

    // ── PlaceAutocompleteElement — Destino ────────────────
    destAC = new PlaceAutocompleteElement({ requestedRegion: "br" });
    document.getElementById("destination-ac-container").appendChild(destAC);

    destAC.addEventListener("gmp-select", async (e) => {
        const place = e.placePrediction.toPlace();
        await place.fetchFields(["formattedAddress", "location"]);
        setDestination(place.formattedAddress, place.location.lat(), place.location.lng());
    });

    // ── Botão GPS ─────────────────────────────────────────
    document.getElementById("gps-btn").addEventListener("click", () => {
        if (!navigator.geolocation) {
            alert("Seu navegador não suporta geolocalização.");
            return;
        }
        const statusEl = document.getElementById("gps-status");
        statusEl.textContent = "Obtendo localização...";
        statusEl.classList.remove("hidden");

        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                try {
                    const geocoder = new Geocoder();
                    const { results } = await geocoder.geocode({ location: { lat, lng } });
                    const address = results?.[0]?.formatted_address ?? `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    setOrigin(address, lat, lng);
                    originAC.value = address;
                } catch {
                    setOrigin(`${lat.toFixed(5)}, ${lng.toFixed(5)}`, lat, lng);
                } finally {
                    statusEl.classList.add("hidden");
                }
            },
            (err) => {
                statusEl.classList.add("hidden");
                const msgs = { 1: "Permissão negada.", 2: "Localização indisponível.", 3: "Tempo esgotado." };
                alert(msgs[err.code] ?? "Erro ao obter localização.");
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    // ── Helpers ───────────────────────────────────────────
    function setOrigin(address, lat, lng) {
        originPlace = { address, lat, lng };
        document.getElementById("origin-value").value  = address;
        document.getElementById("origin-coords").value = `${lat},${lng}`;
        placeMarker("origin", { lat, lng }, "Origem", "#16a34a");
        fitMap(); drawRoute();
    }

    function setDestination(address, lat, lng) {
        destinationPlace = { address, lat, lng };
        document.getElementById("destination-value").value  = address;
        document.getElementById("destination-coords").value = `${lat},${lng}`;
        placeMarker("dest", { lat, lng }, "Destino", "#dc2626");
        fitMap(); drawRoute();
    }

    window._setOrigin      = setOrigin;
    window._setDestination = setDestination;
}

function placeMarker(key, position, title, color) {
    if (key === "origin" && originMarker)     { originMarker.setMap(null); }
    if (key === "dest"   && destinationMarker) { destinationMarker.setMap(null); }

    const marker = new google.maps.Marker({
        map, position, title,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: color,
            fillOpacity: 1,
            strokeColor: "#ffffff",
            strokeWeight: 2,
        },
    });

    if (key === "origin") originMarker = marker;
    else destinationMarker = marker;
}

function fitMap() {
    if (!map) return;
    if (originPlace && destinationPlace) {
        const bounds = new google.maps.LatLngBounds();
        bounds.extend({ lat: originPlace.lat, lng: originPlace.lng });
        bounds.extend({ lat: destinationPlace.lat, lng: destinationPlace.lng });
        map.fitBounds(bounds, 60);
    } else if (originPlace) {
        map.panTo({ lat: originPlace.lat, lng: originPlace.lng }); map.setZoom(15);
    } else if (destinationPlace) {
        map.panTo({ lat: destinationPlace.lat, lng: destinationPlace.lng }); map.setZoom(15);
    }
}

async function drawRoute() {
    if (!originPlace || !destinationPlace) return;
    if (routePolyline) { routePolyline.setMap(null); routePolyline = null; }
    try {
        const ds = new google.maps.DirectionsService();
        const result = await ds.route({
            origin:      { lat: originPlace.lat, lng: originPlace.lng },
            destination: { lat: destinationPlace.lat, lng: destinationPlace.lng },
            travelMode:  google.maps.TravelMode.DRIVING,
        });
        routePolyline = new google.maps.Polyline({
            path: result.routes[0].overview_path,
            strokeColor: "#2563EB",
            strokeWeight: 4,
            strokeOpacity: 0.8,
            map,
        });
    } catch { /* rota não disponível */ }
}

initMaps();
</script>

@else
{{-- Fallback sem API key: GPS simples --}}
<script>
document.getElementById("gps-btn").addEventListener("click", () => {
    if (!navigator.geolocation) { alert("Geolocalização não suportada."); return; }
    const statusEl = document.getElementById("gps-status");
    statusEl.textContent = "Obtendo localização...";
    statusEl.classList.remove("hidden");
    navigator.geolocation.getCurrentPosition(pos => {
        statusEl.classList.add("hidden");
        const val = `${pos.coords.latitude.toFixed(6)},${pos.coords.longitude.toFixed(6)}`;
        document.getElementById("origin-value").value  = val;
        document.getElementById("origin-coords").value = val;
        // Injeta um input visível simples caso não exista
        const container = document.getElementById("origin-ac-container");
        let input = container.querySelector("input");
        if (!input) {
            input = document.createElement("input");
            input.className = "w-full border border-gray-300 rounded-lg px-3 py-2 text-sm";
            container.appendChild(input);
        }
        input.value = val;
    }, () => { statusEl.classList.add("hidden"); alert("Não foi possível obter a localização."); });
});

// Injeta inputs simples de texto no fallback
["origin-ac-container","destination-ac-container"].forEach((id, i) => {
    const input = document.createElement("input");
    input.type = "text";
    input.placeholder = i === 0 ? "Endereço de origem" : "Endereço de destino";
    input.className = "w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500";
    input.addEventListener("input", () => {
        document.getElementById(i === 0 ? "origin-value" : "destination-value").value = input.value;
    });
    document.getElementById(id).appendChild(input);
});
</script>
@endif

{{-- Submit --}}
<script>
document.getElementById("ride-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    const alertSuccess = document.getElementById("alert-success");
    const alertError   = document.getElementById("alert-error");
    alertSuccess.classList.add("hidden");
    alertError.classList.add("hidden");

    // Garante que coords tenham valor mesmo sem Maps
    if (!document.getElementById("origin-coords").value)      document.getElementById("origin-coords").value = "0,0";
    if (!document.getElementById("destination-coords").value) document.getElementById("destination-coords").value = "0,0";

    const payload = {
        origin:             document.getElementById("origin-value").value.trim(),
        destination:        document.getElementById("destination-value").value.trim(),
        origin_coords:      document.getElementById("origin-coords").value,
        destination_coords: document.getElementById("destination-coords").value,
        scheduled_for:      new Date(document.getElementById("scheduled_for").value).toISOString(),
        seats_needed:       parseInt(document.getElementById("seats_needed").value),
    };

    if (!payload.origin || !payload.destination) {
        alertError.textContent = "Selecione a origem e o destino.";
        alertError.classList.remove("hidden");
        return;
    }

    const btn = document.getElementById("submit-btn");
    btn.disabled = true; btn.textContent = "Enviando...";

    const res = await fetch("/rides/request", {
        method:  "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content,
            "Content-Type": "application/json",
            "Accept":       "application/json",
        },
        body: JSON.stringify(payload),
    });

    btn.disabled = false; btn.textContent = "Solicitar Carona";

    if (res.status === 201) {
        alertSuccess.classList.remove("hidden");
        alertSuccess.scrollIntoView({ behavior: "smooth" });
        document.getElementById("ride-form").reset();
        // Limpa estado do mapa
        originPlace = null; destinationPlace = null;
        if (typeof originMarker !== "undefined" && originMarker)     originMarker.setMap(null);
        if (typeof destinationMarker !== "undefined" && destinationMarker) destinationMarker.setMap(null);
        if (typeof routePolyline !== "undefined" && routePolyline)   routePolyline.setMap(null);
        if (typeof originAC !== "undefined" && originAC) originAC.value = "";
        if (typeof destAC   !== "undefined" && destAC)   destAC.value   = "";
    } else {
        const data = await res.json().catch(() => ({}));
        alertError.textContent = data.errors
            ? Object.values(data.errors).flat().join(" ")
            : (data.message ?? "Erro ao enviar solicitação.");
        alertError.classList.remove("hidden");
    }
});
</script>
@endpush
