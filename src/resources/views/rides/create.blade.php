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
            <strong>Atenção:</strong> Configure <code>GOOGLE_MAPS_API_KEY</code> no <code>src/.env</code>.
        </div>
    @endif

    <div id="alert-success" class="hidden mb-4 bg-green-100 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
        Solicitação enviada! Aguarde um motorista aceitar.
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
            <p id="gps-status" class="text-xs text-gray-400 mt-1 hidden"></p>
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

        {{-- Mapa --}}
        @if($mapsKey)
        <div>
            {{-- Botões de modo: clique no mapa define origem ou destino --}}
            <div class="flex gap-2 mb-2" id="map-mode-bar">
                <button type="button" id="mode-origin-btn"
                        class="flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 border-green-500 bg-green-50 text-green-700 transition">
                    📍 Definir Origem
                </button>
                <button type="button" id="mode-dest-btn"
                        class="flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:border-red-400 hover:text-red-600">
                    🏁 Definir Destino
                </button>
            </div>
            <div id="map" class="w-full h-56 rounded-xl border border-gray-200 bg-gray-100 cursor-crosshair"></div>
            <p class="text-xs text-gray-400 mt-1 text-center">
                Clique no mapa ou use a busca acima para definir os pontos
            </p>
        </div>
        @endif

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

{{-- Fix 3: força modo claro nos web components do Maps --}}
<style>
    gmp-place-autocomplete {
        width: 100%;
        color-scheme: light;
        --gmp-place-autocomplete-background-color: #ffffff;
        --gmp-place-autocomplete-border-radius: 0.5rem;
        --gmp-place-autocomplete-font-size: 0.875rem;
    }
</style>

@endsection

@push('scripts')
@if($mapsKey)
{{-- Bootstrap loader recomendado pelo Google --}}
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

let map, originMarker, destinationMarker, routePolyline;
let originPlace = null, destinationPlace = null;
let originAC, destAC;
let mapClickMode = "origin"; // "origin" | "destination"

async function initMaps() {
    const { Map }                      = await google.maps.importLibrary("maps");
    const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");
    const { Geocoder }                 = await google.maps.importLibrary("geocoding");

    // ── Mapa ─────────────────────────────────────────────
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

    // ── Clique no mapa (estilo Uber) ──────────────────────
    const geocoder = new Geocoder();

    map.addListener("click", async (e) => {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();
        let address = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        try {
            const { results } = await geocoder.geocode({ location: { lat, lng } });
            if (results?.[0]) address = results[0].formatted_address;
        } catch { /* usa coords brutas */ }

        if (mapClickMode === "origin") {
            setOrigin(address, lat, lng);
            originAC.value = address;
            setMapMode("destination"); // avança para destino automaticamente
        } else {
            setDestination(address, lat, lng);
            destAC.value = address;
        }
    });

    // ── Botões de modo ───────────────────────────────────
    document.getElementById("mode-origin-btn").addEventListener("click", () => setMapMode("origin"));
    document.getElementById("mode-dest-btn").addEventListener("click",   () => setMapMode("destination"));

    // ── Botão GPS ─────────────────────────────────────────
    document.getElementById("gps-btn").addEventListener("click", () => {
        // Fix 2: GPS exige contexto seguro (HTTPS ou localhost)
        if (!window.isSecureContext) {
            document.getElementById("gps-status").textContent =
                "GPS indisponível em HTTP. Use a busca ou clique no mapa para definir a origem.";
            document.getElementById("gps-status").classList.remove("hidden");
            return;
        }
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
                let address = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                try {
                    const { results } = await geocoder.geocode({ location: { lat, lng } });
                    if (results?.[0]) address = results[0].formatted_address;
                } catch { /* usa coords brutas */ }
                setOrigin(address, lat, lng);
                originAC.value = address;
                statusEl.classList.add("hidden");
            },
            (err) => {
                statusEl.classList.add("hidden");
                const msgs = {
                    1: "Permissão de localização negada. Use a busca ou clique no mapa.",
                    2: "Localização indisponível.",
                    3: "Tempo esgotado ao obter localização.",
                };
                statusEl.textContent = msgs[err.code] ?? "Erro ao obter localização.";
                statusEl.classList.remove("hidden");
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

// ── Modo de clique no mapa ────────────────────────────────
function setMapMode(mode) {
    mapClickMode = mode;
    const oBtn = document.getElementById("mode-origin-btn");
    const dBtn = document.getElementById("mode-dest-btn");
    if (mode === "origin") {
        oBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 border-green-500 bg-green-50 text-green-700 transition";
        dBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:border-red-400 hover:text-red-600";
    } else {
        oBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:border-green-400 hover:text-green-600";
        dBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 border-red-500 bg-red-50 text-red-700 transition";
    }
}

// ── Marcadores ────────────────────────────────────────────
function placeMarker(key, position, title, color) {
    if (key === "origin" && originMarker)      originMarker.setMap(null);
    if (key === "dest"   && destinationMarker) destinationMarker.setMap(null);

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

// ── Ajuste de câmera ──────────────────────────────────────
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

// ── Rota ──────────────────────────────────────────────────
async function drawRoute() {
    if (!originPlace || !destinationPlace) return;
    if (routePolyline) { routePolyline.setMap(null); routePolyline = null; }
    try {
        const result = await new google.maps.DirectionsService().route({
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
{{-- Fallback sem API key --}}
<script>
document.getElementById("gps-btn").addEventListener("click", () => {
    const statusEl = document.getElementById("gps-status");
    if (!window.isSecureContext) {
        statusEl.textContent = "GPS indisponível em HTTP. Digite o endereço manualmente.";
        statusEl.classList.remove("hidden"); return;
    }
    if (!navigator.geolocation) { alert("Geolocalização não suportada."); return; }
    statusEl.textContent = "Obtendo localização...";
    statusEl.classList.remove("hidden");
    navigator.geolocation.getCurrentPosition(pos => {
        statusEl.classList.add("hidden");
        const val = `${pos.coords.latitude.toFixed(6)},${pos.coords.longitude.toFixed(6)}`;
        document.getElementById("origin-value").value  = val;
        document.getElementById("origin-coords").value = val;
        const input = document.querySelector("#origin-ac-container input");
        if (input) input.value = val;
    }, () => { statusEl.classList.add("hidden"); alert("Não foi possível obter a localização."); });
});

["origin-ac-container", "destination-ac-container"].forEach((id, i) => {
    const input = Object.assign(document.createElement("input"), {
        type: "text",
        placeholder: i === 0 ? "Endereço de origem" : "Endereço de destino",
        className: "w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500",
    });
    const hiddenId = i === 0 ? "origin-value" : "destination-value";
    input.addEventListener("input", () => { document.getElementById(hiddenId).value = input.value; });
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
        alertError.classList.remove("hidden"); return;
    }

    const btn = document.getElementById("submit-btn");
    btn.disabled = true; btn.textContent = "Enviando...";

    const res = await fetch("/rides/request", {
        method: "POST",
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
        originPlace = null; destinationPlace = null;
        if (typeof originMarker !== "undefined" && originMarker)      originMarker.setMap(null);
        if (typeof destinationMarker !== "undefined" && destinationMarker) destinationMarker.setMap(null);
        if (typeof routePolyline !== "undefined" && routePolyline)    routePolyline.setMap(null);
        if (typeof originAC !== "undefined" && originAC) originAC.value = "";
        if (typeof destAC   !== "undefined" && destAC)   destAC.value   = "";
        if (typeof setMapMode !== "undefined") setMapMode("origin");
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
