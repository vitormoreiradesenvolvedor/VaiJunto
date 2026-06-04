@extends('layouts.app')

@section('title', 'Solicitar Carona')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Voltar</a>
        <h2 class="text-xl font-bold text-gray-900">Solicitar Carona</h2>
    </div>

    @if(!$mapsKey)
        <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg px-4 py-3 text-sm">
            <strong>Atenção:</strong> Configure <code>GOOGLE_MAPS_API_KEY</code> no <code>src/.env</code>.
        </div>
    @endif

    <div id="alert-error" class="hidden mb-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm"></div>

    <form id="ride-form" class="space-y-4">
        @csrf

        {{-- ── Campos de endereço estilo Uber ────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-visible relative">

            {{-- Origem --}}
            <div class="flex items-center px-4 py-3 border-b border-gray-100">
                <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0 mr-3"></span>
                <input id="origin-input" type="text" autocomplete="off"
                       placeholder="De onde você vai sair?"
                       class="flex-1 text-sm text-gray-800 outline-none placeholder-gray-400 bg-transparent min-w-0">
                <button type="button" id="gps-btn" title="Usar minha localização"
                        class="ml-2 flex-shrink-0 text-blue-500 hover:text-blue-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>
                    </svg>
                </button>
            </div>
            <p id="gps-status" class="hidden text-xs text-gray-500 px-4 pb-1"></p>

            {{-- Linha pontilhada entre os campos --}}
            <div class="flex items-center px-4">
                <div class="w-3 flex justify-center mr-3">
                    <div class="w-px h-3 border-l-2 border-dashed border-gray-300"></div>
                </div>
            </div>

            {{-- Destino --}}
            <div class="flex items-center px-4 py-3">
                <span class="w-3 h-3 rounded-sm bg-gray-800 flex-shrink-0 mr-3"></span>
                <input id="destination-input" type="text" autocomplete="off"
                       placeholder="Para onde você vai?"
                       class="flex-1 text-sm text-gray-800 outline-none placeholder-gray-400 bg-transparent min-w-0">
            </div>

            <input type="hidden" name="origin"             id="origin-value">
            <input type="hidden" name="origin_coords"      id="origin-coords">
            <input type="hidden" name="destination"        id="destination-value">
            <input type="hidden" name="destination_coords" id="destination-coords">
        </div>

        {{-- Dropdown de sugestões --}}
        <div id="suggestions-dropdown"
             class="hidden bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
        </div>

        {{-- Mapa --}}
        @if($mapsKey)
        <div>
            <div class="flex gap-2 mb-2">
                <button type="button" id="mode-origin-btn"
                        class="flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 border-green-500 bg-green-50 text-green-700 transition">
                    📍 Definir Origem
                </button>
                <button type="button" id="mode-dest-btn"
                        class="flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:border-red-400 hover:text-red-600">
                    🏁 Definir Destino
                </button>
            </div>
            <div id="map" class="w-full h-52 rounded-xl border border-gray-300 bg-gray-100 cursor-crosshair"></div>
            <p class="text-xs text-gray-400 mt-1 text-center">Clique no mapa para definir os pontos</p>
        </div>
        @endif

        {{-- Quando? --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Quando?</p>
            <div class="flex gap-2 mb-3">
                <button type="button" id="btn-now"
                        class="flex-1 py-2 text-sm font-medium rounded-lg border-2 border-blue-500 bg-blue-50 text-blue-700 transition">
                    Agora
                </button>
                <button type="button" id="btn-schedule"
                        class="flex-1 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-600 transition hover:border-blue-400 hover:text-blue-600">
                    Agendar
                </button>
            </div>
            <div id="datetime-field" class="hidden">
                <input type="datetime-local" id="scheduled_for_input"
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <input type="hidden" name="scheduled_for" id="scheduled_for">
        </div>

        {{-- Assentos --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Assentos necessários</p>
            <div class="flex items-center gap-4">
                <button type="button" id="seats-dec"
                        class="w-9 h-9 rounded-full border border-gray-300 text-gray-600 font-bold text-lg flex items-center justify-center hover:bg-gray-50 transition">−</button>
                <span id="seats-display" class="text-xl font-bold text-gray-900 w-6 text-center">1</span>
                <button type="button" id="seats-inc"
                        class="w-9 h-9 rounded-full border border-gray-300 text-gray-600 font-bold text-lg flex items-center justify-center hover:bg-gray-50 transition">+</button>
                <input type="hidden" name="seats_needed" id="seats_needed" value="1">
            </div>
        </div>

        <button type="submit" id="submit-btn"
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-3.5 rounded-xl shadow transition">
            Solicitar Carona
        </button>
    </form>
</div>

@endsection

@push('scripts')

{{-- Toggle Agora / Agendar --}}
<script>
let scheduleMode = "now";

document.getElementById("btn-now").addEventListener("click", () => {
    scheduleMode = "now";
    document.getElementById("datetime-field").classList.add("hidden");
    document.getElementById("btn-now").className     = "flex-1 py-2 text-sm font-medium rounded-lg border-2 border-blue-500 bg-blue-50 text-blue-700 transition";
    document.getElementById("btn-schedule").className = "flex-1 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-600 transition hover:border-blue-400 hover:text-blue-600";
});
document.getElementById("btn-schedule").addEventListener("click", () => {
    scheduleMode = "later";
    document.getElementById("datetime-field").classList.remove("hidden");
    document.getElementById("btn-schedule").className = "flex-1 py-2 text-sm font-medium rounded-lg border-2 border-blue-500 bg-blue-50 text-blue-700 transition";
    document.getElementById("btn-now").className      = "flex-1 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-600 transition hover:border-blue-400 hover:text-blue-600";
});

// Assentos
let seats = 1;
document.getElementById("seats-dec").addEventListener("click", () => {
    if (seats > 1) { seats--; document.getElementById("seats-display").textContent = seats; document.getElementById("seats_needed").value = seats; }
});
document.getElementById("seats-inc").addEventListener("click", () => {
    if (seats < 6) { seats++; document.getElementById("seats-display").textContent = seats; document.getElementById("seats_needed").value = seats; }
});
</script>

@if($mapsKey)
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

let map, originMarker, destinationMarker, directionsRenderer;
let originPlace = null, destinationPlace = null;
let mapClickMode = "origin";

async function initMaps() {
    const { Map }      = await google.maps.importLibrary("maps");
    const { Geocoder } = await google.maps.importLibrary("geocoding");
    await google.maps.importLibrary("places");

    map = new Map(document.getElementById("map"), {
        center: { lat: -21.2342, lng: -44.9998 },
        zoom: 13,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        gestureHandling: "cooperative",
    });

    const acService     = new google.maps.places.AutocompleteService();
    const placesService = new google.maps.places.PlacesService(map);
    const geocoder      = new Geocoder();

    setupAutocomplete("origin-input",      acService, placesService, setOrigin);
    setupAutocomplete("destination-input", acService, placesService, setDestination);

    // Clique no mapa
    map.addListener("click", async (e) => {
        const lat = e.latLng.lat(), lng = e.latLng.lng();
        let address = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        try {
            const { results } = await geocoder.geocode({ location: { lat, lng } });
            if (results?.[0]) address = results[0].formatted_address;
        } catch {}
        if (mapClickMode === "origin") {
            setOrigin(address, lat, lng);
            document.getElementById("origin-input").value = address;
            setMapMode("destination");
        } else {
            setDestination(address, lat, lng);
            document.getElementById("destination-input").value = address;
        }
    });

    document.getElementById("mode-origin-btn").addEventListener("click", () => setMapMode("origin"));
    document.getElementById("mode-dest-btn").addEventListener("click",   () => setMapMode("destination"));

    // GPS — botão manual
    document.getElementById("gps-btn").addEventListener("click", () => requestGPS(geocoder, true));

    // GPS — ativação automática ao carregar (usa permissão já concedida ou solicita)
    requestGPS(geocoder, false);
}

function requestGPS(geocoder, showStatus) {
    const statusEl = document.getElementById("gps-status");
    if (!window.isSecureContext) {
        if (showStatus) { statusEl.textContent = "GPS indisponível em HTTP."; statusEl.classList.remove("hidden"); }
        return;
    }
    if (!navigator.geolocation) {
        if (showStatus) alert("Geolocalização não suportada.");
        return;
    }
    if (showStatus) { statusEl.textContent = "Obtendo localização..."; statusEl.classList.remove("hidden"); }

    navigator.geolocation.getCurrentPosition(async (pos) => {
        const lat = pos.coords.latitude, lng = pos.coords.longitude;
        let address = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        try {
            const { results } = await geocoder.geocode({ location: { lat, lng } });
            if (results?.[0]) address = results[0].formatted_address;
        } catch {}
        setOrigin(address, lat, lng);
        document.getElementById("origin-input").value = address;
        statusEl.classList.add("hidden");
        setMapMode("destination");
    }, (err) => {
        if (showStatus) {
            statusEl.classList.add("hidden");
            const msgs = { 1: "Permissão negada.", 2: "Localização indisponível.", 3: "Tempo esgotado." };
            statusEl.textContent = msgs[err.code] ?? "Erro ao obter localização.";
            statusEl.classList.remove("hidden");
        }
    }, { enableHighAccuracy: true, timeout: 10000 });
}

// ── Autocomplete custom ───────────────────────────────────────────────────────
function setupAutocomplete(inputId, acService, placesService, onSelect) {
    const input    = document.getElementById(inputId);
    const dropdown = document.getElementById("suggestions-dropdown");
    let debounce;
    let activeIndex = -1;

    input.addEventListener("input", () => {
        clearTimeout(debounce);
        const val = input.value.trim();
        if (val.length < 2) { hideDropdown(); return; }
        debounce = setTimeout(() => {
            acService.getPlacePredictions(
                { input: val, componentRestrictions: { country: "br" } },
                (predictions, status) => {
                    if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions?.length) {
                        hideDropdown(); return;
                    }
                    renderDropdown(predictions, placesService, onSelect, input);
                }
            );
        }, 300);
    });

    // Navegação com teclado
    input.addEventListener("keydown", (e) => {
        const items = dropdown.querySelectorAll(".sug-item");
        if (!items.length) return;
        if (e.key === "ArrowDown") { e.preventDefault(); activeIndex = Math.min(activeIndex + 1, items.length - 1); highlightItem(items, activeIndex); }
        if (e.key === "ArrowUp")   { e.preventDefault(); activeIndex = Math.max(activeIndex - 1, 0); highlightItem(items, activeIndex); }
        if (e.key === "Enter" && activeIndex >= 0) { e.preventDefault(); items[activeIndex].dispatchEvent(new MouseEvent("mousedown")); }
        if (e.key === "Escape") hideDropdown();
    });

    input.addEventListener("blur", () => setTimeout(hideDropdown, 200));
    input.addEventListener("focus", () => { activeIndex = -1; });
}

function renderDropdown(predictions, placesService, onSelect, input) {
    const dropdown = document.getElementById("suggestions-dropdown");
    activeIndex = -1;

    dropdown.innerHTML = predictions.slice(0, 5).map((p, i) => `
        <div class="sug-item flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 transition
                    ${i < predictions.slice(0,5).length - 1 ? 'border-b border-gray-100' : ''}"
             data-place-id="${p.place_id}">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">${p.structured_formatting.main_text}</p>
                <p class="text-xs text-gray-400 truncate">${p.structured_formatting.secondary_text ?? ''}</p>
            </div>
        </div>
    `).join("");

    dropdown.querySelectorAll(".sug-item").forEach(item => {
        item.addEventListener("mousedown", () => {
            placesService.getDetails(
                { placeId: item.dataset.placeId, fields: ["geometry", "formatted_address"] },
                (place, status) => {
                    if (status === google.maps.places.PlacesServiceStatus.OK) {
                        const lat = place.geometry.location.lat();
                        const lng = place.geometry.location.lng();
                        input.value = place.formatted_address;
                        onSelect(place.formatted_address, lat, lng);
                    }
                    hideDropdown();
                }
            );
        });
    });

    dropdown.classList.remove("hidden");
}

function hideDropdown() {
    document.getElementById("suggestions-dropdown").classList.add("hidden");
}

function highlightItem(items, index) {
    items.forEach((el, i) => {
        el.classList.toggle("bg-gray-50", i === index);
    });
}

// ── Helpers de mapa ───────────────────────────────────────────────────────────
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

function placeMarker(key, position, title, color) {
    if (key === "origin" && originMarker)      originMarker.setMap(null);
    if (key === "dest"   && destinationMarker) destinationMarker.setMap(null);
    const marker = new google.maps.Marker({
        map, position, title,
        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8,
                fillColor: color, fillOpacity: 1,
                strokeColor: "#fff", strokeWeight: 2 },
    });
    if (key === "origin") originMarker = marker;
    else destinationMarker = marker;
}

function fitMap() {
    if (originPlace && destinationPlace) {
        const b = new google.maps.LatLngBounds();
        b.extend({ lat: originPlace.lat, lng: originPlace.lng });
        b.extend({ lat: destinationPlace.lat, lng: destinationPlace.lng });
        map.fitBounds(b, 60);
    } else if (originPlace) {
        map.panTo({ lat: originPlace.lat, lng: originPlace.lng }); map.setZoom(15);
    } else if (destinationPlace) {
        map.panTo({ lat: destinationPlace.lat, lng: destinationPlace.lng }); map.setZoom(15);
    }
}

async function drawRoute() {
    if (!originPlace || !destinationPlace) return;
    if (!directionsRenderer) {
        directionsRenderer = new google.maps.DirectionsRenderer({
            map,
            suppressMarkers: true,
            preserveViewport: true,
            polylineOptions: { strokeColor: "#2563EB", strokeWeight: 5, strokeOpacity: 0.9 },
        });
    }
    try {
        const result = await new google.maps.DirectionsService().route({
            origin: { lat: originPlace.lat, lng: originPlace.lng },
            destination: { lat: destinationPlace.lat, lng: destinationPlace.lng },
            travelMode: google.maps.TravelMode.DRIVING,
        });
        directionsRenderer.setDirections(result);
    } catch {}
}

initMaps();
</script>

@else
<script>
// Fallback sem Maps
["origin-input","destination-input"].forEach((id, i) => {
    document.getElementById(id).addEventListener("input", function () {
        const hiddenId = i === 0 ? "origin-value" : "destination-value";
        document.getElementById(hiddenId).value = this.value;
    });
});

function fallbackGPS(showStatus) {
    const statusEl = document.getElementById("gps-status");
    if (!window.isSecureContext) {
        if (showStatus) { statusEl.textContent = "GPS indisponível em HTTP."; statusEl.classList.remove("hidden"); }
        return;
    }
    if (!navigator.geolocation) { if (showStatus) alert("Geolocalização não suportada."); return; }
    if (showStatus) { statusEl.textContent = "Obtendo localização..."; statusEl.classList.remove("hidden"); }
    navigator.geolocation.getCurrentPosition(pos => {
        statusEl.classList.add("hidden");
        const val = `${pos.coords.latitude.toFixed(6)},${pos.coords.longitude.toFixed(6)}`;
        document.getElementById("origin-input").value  = val;
        document.getElementById("origin-value").value  = val;
        document.getElementById("origin-coords").value = val;
    }, () => { if (showStatus) { statusEl.classList.add("hidden"); alert("Não foi possível obter a localização."); } },
    { enableHighAccuracy: true, timeout: 10000 });
}

document.getElementById("gps-btn").addEventListener("click", () => fallbackGPS(true));
// Auto-trigger na carga da página
fallbackGPS(false);
</script>
@endif

{{-- Submit --}}
<script>
document.getElementById("ride-form").addEventListener("submit", async function (e) {
    e.preventDefault();
    const alertError = document.getElementById("alert-error");
    alertError.classList.add("hidden");

    let scheduledFor;
    if (scheduleMode === "now") {
        scheduledFor = new Date(Date.now() + 2 * 60 * 1000).toISOString();
    } else {
        const inputVal = document.getElementById("scheduled_for_input").value;
        if (!inputVal) {
            alertError.textContent = "Selecione a data e hora.";
            alertError.classList.remove("hidden"); return;
        }
        scheduledFor = new Date(inputVal).toISOString();
    }

    if (!document.getElementById("origin-coords").value)      document.getElementById("origin-coords").value = "0,0";
    if (!document.getElementById("destination-coords").value) document.getElementById("destination-coords").value = "0,0";

    const payload = {
        origin:             document.getElementById("origin-value").value.trim()      || document.getElementById("origin-input").value.trim(),
        destination:        document.getElementById("destination-value").value.trim() || document.getElementById("destination-input").value.trim(),
        origin_coords:      document.getElementById("origin-coords").value,
        destination_coords: document.getElementById("destination-coords").value,
        scheduled_for:      scheduledFor,
        seats_needed:       parseInt(document.getElementById("seats_needed").value),
    };

    if (!payload.origin || !payload.destination) {
        alertError.textContent = "Selecione a origem e o destino.";
        alertError.classList.remove("hidden"); return;
    }

    @if($mapsKey)
    if (payload.origin_coords === "0,0" || payload.destination_coords === "0,0") {
        alertError.textContent = "Selecione os endereços a partir das sugestões do mapa para garantir a localização correta.";
        alertError.classList.remove("hidden"); return;
    }
    @endif

    const btn = document.getElementById("submit-btn");
    btn.disabled = true; btn.textContent = "Enviando...";

    const res = await fetch("/rides/request", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content,
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
        body: JSON.stringify(payload),
    });

    btn.disabled = false; btn.textContent = "Solicitar Carona";

    if (res.status === 201) {
        const data = await res.json();
        window.location.href = data.track_url;
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
