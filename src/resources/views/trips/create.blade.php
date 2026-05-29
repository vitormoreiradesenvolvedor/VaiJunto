@extends('layouts.app')

@section('title', 'Oferecer Carona')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Voltar</a>
        <h2 class="text-xl font-bold text-gray-900">Oferecer Carona</h2>
    </div>

    @if(!$vehicle)
        <div class="mb-5 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 text-sm flex items-center gap-3">
            <span class="text-xl">🚗</span>
            <div>
                Você ainda não tem um veículo cadastrado.
                <a href="{{ route('vehicles.create') }}" class="font-semibold underline ml-1">Cadastre agora</a>
                para oferecer caronas.
            </div>
        </div>
    @endif

    @if(!$mapsKey)
        <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg px-4 py-3 text-sm">
            <strong>Atenção:</strong> Configure <code>GOOGLE_MAPS_API_KEY</code> no <code>.env</code>.
        </div>
    @endif

    <div id="alert-error" class="hidden mb-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm"></div>

    <form id="trip-form" method="POST" action="{{ route('trips.store') }}" class="space-y-4">
        @csrf

        {{-- Endereços --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-visible">
            <div class="flex items-center px-4 py-3 border-b border-gray-100">
                <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0 mr-3"></span>
                <input id="origin-input" type="text" autocomplete="off"
                       placeholder="De onde você vai sair?"
                       class="flex-1 text-sm text-gray-800 outline-none placeholder-gray-400 bg-transparent min-w-0">
            </div>
            <div class="flex items-center px-4">
                <div class="w-3 flex justify-center mr-3">
                    <div class="w-px h-3 border-l-2 border-dashed border-gray-300"></div>
                </div>
            </div>
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

        {{-- Dropdown sugestões --}}
        <div id="suggestions-dropdown" class="hidden bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden"></div>

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
            <div id="map" class="w-full h-48 rounded-xl border border-gray-300 bg-gray-100 cursor-crosshair"></div>
        </div>
        @endif

        {{-- Partida --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Quando você parte?</p>
            <input type="datetime-local" name="departs_at" id="departs_at" required
                   min="{{ now()->addMinutes(10)->format('Y-m-d\TH:i') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Vagas --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Quantas vagas você oferece?</p>
            <div class="flex items-center gap-4">
                <button type="button" id="seats-dec"
                        class="w-9 h-9 rounded-full border border-gray-300 text-gray-600 font-bold text-lg flex items-center justify-center hover:bg-gray-50 transition">−</button>
                <span id="seats-display" class="text-xl font-bold text-gray-900 w-6 text-center">3</span>
                <button type="button" id="seats-inc"
                        class="w-9 h-9 rounded-full border border-gray-300 text-gray-600 font-bold text-lg flex items-center justify-center hover:bg-gray-50 transition">+</button>
                <input type="hidden" name="seats_total" id="seats_total" value="3">
            </div>
        </div>

        {{-- Veículo --}}
        @if($vehicle)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
            <span class="text-2xl">🚗</span>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800">{{ $vehicle->model }}</p>
                <p class="text-xs text-gray-500">{{ $vehicle->color }} · {{ $vehicle->plate }}</p>
            </div>
            <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-xs text-blue-600 hover:underline">Editar</a>
        </div>
        @endif

        <button type="submit" id="submit-btn"
                {{ !$vehicle ? 'disabled' : '' }}
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold py-3.5 rounded-xl shadow transition">
            Publicar Viagem
        </button>
    </form>
</div>

@endsection

@push('scripts')
<script>
// Assentos
let seats = 3;
document.getElementById("seats-dec").addEventListener("click", () => {
    if (seats > 1) { seats--; document.getElementById("seats-display").textContent = seats; document.getElementById("seats_total").value = seats; }
});
document.getElementById("seats-inc").addEventListener("click", () => {
    if (seats < 8) { seats++; document.getElementById("seats-display").textContent = seats; document.getElementById("seats_total").value = seats; }
});

// Validação antes de submeter (fields dos endereços)
document.getElementById("trip-form").addEventListener("submit", function (e) {
    const origin = document.getElementById("origin-value").value.trim() || document.getElementById("origin-input").value.trim();
    const dest   = document.getElementById("destination-value").value.trim() || document.getElementById("destination-input").value.trim();
    if (!origin || !dest) {
        e.preventDefault();
        const alertError = document.getElementById("alert-error");
        alertError.textContent = "Informe a origem e o destino.";
        alertError.classList.remove("hidden");
        return;
    }
    // Garante que os hidden fields tenham valor
    if (!document.getElementById("origin-value").value)      document.getElementById("origin-value").value = origin;
    if (!document.getElementById("destination-value").value) document.getElementById("destination-value").value = dest;
    if (!document.getElementById("origin-coords").value)      document.getElementById("origin-coords").value = "0,0";
    if (!document.getElementById("destination-coords").value) document.getElementById("destination-coords").value = "0,0";
});
</script>

@if($mapsKey)
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

let map, originMarker, destinationMarker, routePolyline;
let originPlace = null, destinationPlace = null;
let mapClickMode = "origin";

async function initMaps() {
    const { Map }      = await google.maps.importLibrary("maps");
    const { Geocoder } = await google.maps.importLibrary("geocoding");
    await google.maps.importLibrary("places");

    map = new Map(document.getElementById("map"), {
        center: { lat: -21.2342, lng: -44.9998 }, zoom: 13,
        mapTypeControl: false, streetViewControl: false,
        fullscreenControl: false, gestureHandling: "cooperative",
    });

    const acService     = new google.maps.places.AutocompleteService();
    const placesService = new google.maps.places.PlacesService(map);
    const geocoder      = new Geocoder();

    setupAutocomplete("origin-input",      acService, placesService, setOrigin);
    setupAutocomplete("destination-input", acService, placesService, setDestination);

    map.addListener("click", async (e) => {
        const lat = e.latLng.lat(), lng = e.latLng.lng();
        let address = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        try { const { results } = await geocoder.geocode({ location: { lat, lng } }); if (results?.[0]) address = results[0].formatted_address; } catch {}
        if (mapClickMode === "origin") { setOrigin(address, lat, lng); document.getElementById("origin-input").value = address; setMapMode("destination"); }
        else { setDestination(address, lat, lng); document.getElementById("destination-input").value = address; }
    });

    document.getElementById("mode-origin-btn").addEventListener("click", () => setMapMode("origin"));
    document.getElementById("mode-dest-btn").addEventListener("click",   () => setMapMode("destination"));
}

function setupAutocomplete(inputId, acService, placesService, onSelect) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById("suggestions-dropdown");
    let debounce;
    input.addEventListener("input", () => {
        clearTimeout(debounce);
        const val = input.value.trim();
        if (val.length < 2) { dropdown.classList.add("hidden"); return; }
        debounce = setTimeout(() => {
            acService.getPlacePredictions({ input: val, componentRestrictions: { country: "br" } }, (predictions, status) => {
                if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions?.length) { dropdown.classList.add("hidden"); return; }
                renderDropdown(predictions, placesService, onSelect, input);
            });
        }, 300);
    });
    input.addEventListener("keydown", (e) => {
        const items = dropdown.querySelectorAll(".sug-item");
        if (!items.length) return;
        let idx = [...items].findIndex(el => el.classList.contains("bg-gray-50"));
        if (e.key === "ArrowDown") { e.preventDefault(); idx = Math.min(idx + 1, items.length - 1); items.forEach((el, i) => el.classList.toggle("bg-gray-50", i === idx)); }
        if (e.key === "ArrowUp")   { e.preventDefault(); idx = Math.max(idx - 1, 0); items.forEach((el, i) => el.classList.toggle("bg-gray-50", i === idx)); }
        if (e.key === "Enter" && idx >= 0) { e.preventDefault(); items[idx].dispatchEvent(new MouseEvent("mousedown")); }
        if (e.key === "Escape") dropdown.classList.add("hidden");
    });
    input.addEventListener("blur", () => setTimeout(() => dropdown.classList.add("hidden"), 200));
}

function renderDropdown(predictions, placesService, onSelect, input) {
    const dropdown = document.getElementById("suggestions-dropdown");
    dropdown.innerHTML = predictions.slice(0, 5).map((p, i) => `
        <div class="sug-item flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 transition ${i < 4 ? 'border-b border-gray-100' : ''}" data-place-id="${p.place_id}">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">${p.structured_formatting.main_text}</p>
                <p class="text-xs text-gray-400 truncate">${p.structured_formatting.secondary_text ?? ''}</p>
            </div>
        </div>`).join("");
    dropdown.querySelectorAll(".sug-item").forEach(item => {
        item.addEventListener("mousedown", () => {
            placesService.getDetails({ placeId: item.dataset.placeId, fields: ["geometry", "formatted_address"] }, (place, status) => {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    input.value = place.formatted_address;
                    onSelect(place.formatted_address, place.geometry.location.lat(), place.geometry.location.lng());
                }
                dropdown.classList.add("hidden");
            });
        });
    });
    dropdown.classList.remove("hidden");
}

function setOrigin(address, lat, lng) {
    originPlace = { address, lat, lng };
    document.getElementById("origin-value").value  = address;
    document.getElementById("origin-coords").value = `${lat},${lng}`;
    placeMarker("origin", { lat, lng }, "#16a34a"); fitMap(); drawRoute();
}
function setDestination(address, lat, lng) {
    destinationPlace = { address, lat, lng };
    document.getElementById("destination-value").value  = address;
    document.getElementById("destination-coords").value = `${lat},${lng}`;
    placeMarker("dest", { lat, lng }, "#dc2626"); fitMap(); drawRoute();
}
function setMapMode(mode) {
    mapClickMode = mode;
    const oBtn = document.getElementById("mode-origin-btn"), dBtn = document.getElementById("mode-dest-btn");
    if (mode === "origin") { oBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 border-green-500 bg-green-50 text-green-700 transition"; dBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-500 transition"; }
    else { oBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-500 transition"; dBtn.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 border-red-500 bg-red-50 text-red-700 transition"; }
}
function placeMarker(key, pos, color) {
    if (key === "origin" && originMarker) originMarker.setMap(null);
    if (key === "dest"   && destinationMarker) destinationMarker.setMap(null);
    const m = new google.maps.Marker({ map, position: pos, icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: color, fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 } });
    if (key === "origin") originMarker = m; else destinationMarker = m;
}
function fitMap() {
    if (originPlace && destinationPlace) { const b = new google.maps.LatLngBounds(); b.extend({ lat: originPlace.lat, lng: originPlace.lng }); b.extend({ lat: destinationPlace.lat, lng: destinationPlace.lng }); map.fitBounds(b, 60); }
    else if (originPlace)      { map.panTo({ lat: originPlace.lat,      lng: originPlace.lng });      map.setZoom(15); }
    else if (destinationPlace) { map.panTo({ lat: destinationPlace.lat, lng: destinationPlace.lng }); map.setZoom(15); }
}
async function drawRoute() {
    if (!originPlace || !destinationPlace) return;
    if (routePolyline) { routePolyline.setMap(null); routePolyline = null; }
    try {
        const result = await new google.maps.DirectionsService().route({ origin: { lat: originPlace.lat, lng: originPlace.lng }, destination: { lat: destinationPlace.lat, lng: destinationPlace.lng }, travelMode: google.maps.TravelMode.DRIVING });
        routePolyline = new google.maps.Polyline({ path: result.routes[0].overview_path, strokeColor: "#2563EB", strokeWeight: 4, strokeOpacity: 0.8, map });
    } catch {}
}
initMaps();
</script>
@endif
@endpush
