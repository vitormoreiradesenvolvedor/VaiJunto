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
            <div class="relative">
                <input type="text" id="origin-input" autocomplete="off" required
                       placeholder="Digite ou use sua localização atual"
                       class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" id="gps-btn"
                        title="Usar minha localização atual"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-blue-500 hover:text-blue-700 transition p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>
                    </svg>
                </button>
            </div>
            <p id="gps-status" class="text-xs text-gray-400 mt-1 hidden">Obtendo localização...</p>
            {{-- Campos ocultos enviados ao backend --}}
            <input type="hidden" name="origin" id="origin-value">
            <input type="hidden" name="origin_coords" id="origin-coords">
        </div>

        {{-- Destino --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
            <input type="text" id="destination-input" autocomplete="off" required
                   placeholder="Digite o endereço de destino"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
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

@endsection

@push('scripts')
@if($mapsKey)
<script>
// ── Estado ──────────────────────────────────────────────
let map, originMarker, destinationMarker, directionsRenderer;
let originPlace = null, destinationPlace = null;

// ── Inicialização do Maps ────────────────────────────────
window.initMap = function () {
    const lavras = { lat: -21.2342, lng: -44.9998 };

    map = new google.maps.Map(document.getElementById('map'), {
        center: lavras,
        zoom: 13,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    });

    directionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true,
        polylineOptions: { strokeColor: '#2563EB', strokeWeight: 4 },
    });
    directionsRenderer.setMap(map);

    // Autocomplete — restringe ao Brasil
    const opts = { componentRestrictions: { country: 'br' }, fields: ['formatted_address', 'geometry'] };

    const acOrigin = new google.maps.places.Autocomplete(
        document.getElementById('origin-input'), opts
    );
    const acDest = new google.maps.places.Autocomplete(
        document.getElementById('destination-input'), opts
    );

    acOrigin.addListener('place_changed', () => {
        const place = acOrigin.getPlace();
        if (!place.geometry) return;
        setOrigin(place.formatted_address, place.geometry.location.lat(), place.geometry.location.lng());
    });

    acDest.addListener('place_changed', () => {
        const place = acDest.getPlace();
        if (!place.geometry) return;
        setDestination(place.formatted_address, place.geometry.location.lat(), place.geometry.location.lng());
    });
};

// ── Helpers de marcadores ────────────────────────────────
function setOrigin(address, lat, lng) {
    originPlace = { address, lat, lng };
    document.getElementById('origin-value').value  = address;
    document.getElementById('origin-coords').value = `${lat},${lng}`;
    document.getElementById('origin-input').value  = address;

    if (originMarker) originMarker.setMap(null);
    originMarker = new google.maps.Marker({
        position: { lat, lng }, map,
        title: 'Origem',
        icon: { url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png' },
    });
    fitMap();
    maybeDrawRoute();
}

function setDestination(address, lat, lng) {
    destinationPlace = { address, lat, lng };
    document.getElementById('destination-value').value  = address;
    document.getElementById('destination-coords').value = `${lat},${lng}`;
    document.getElementById('destination-input').value  = address;

    if (destinationMarker) destinationMarker.setMap(null);
    destinationMarker = new google.maps.Marker({
        position: { lat, lng }, map,
        title: 'Destino',
        icon: { url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png' },
    });
    fitMap();
    maybeDrawRoute();
}

function fitMap() {
    if (originPlace && destinationPlace) {
        const bounds = new google.maps.LatLngBounds();
        bounds.extend({ lat: originPlace.lat, lng: originPlace.lng });
        bounds.extend({ lat: destinationPlace.lat, lng: destinationPlace.lng });
        map.fitBounds(bounds, 60);
    } else if (originPlace) {
        map.setCenter({ lat: originPlace.lat, lng: originPlace.lng });
        map.setZoom(15);
    } else if (destinationPlace) {
        map.setCenter({ lat: destinationPlace.lat, lng: destinationPlace.lng });
        map.setZoom(15);
    }
}

function maybeDrawRoute() {
    if (!originPlace || !destinationPlace) return;
    const ds = new google.maps.DirectionsService();
    ds.route({
        origin:      { lat: originPlace.lat, lng: originPlace.lng },
        destination: { lat: destinationPlace.lat, lng: destinationPlace.lng },
        travelMode:  google.maps.TravelMode.DRIVING,
    }, (result, status) => {
        if (status === 'OK') directionsRenderer.setDirections(result);
    });
}

// ── Botão GPS ────────────────────────────────────────────
document.getElementById('gps-btn').addEventListener('click', () => {
    if (!navigator.geolocation) {
        alert('Seu navegador não suporta geolocalização.');
        return;
    }
    const status = document.getElementById('gps-status');
    status.textContent = 'Obtendo localização...';
    status.classList.remove('hidden');

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat, lng } }, (results, geoStatus) => {
                status.classList.add('hidden');
                if (geoStatus === 'OK' && results[0]) {
                    setOrigin(results[0].formatted_address, lat, lng);
                } else {
                    setOrigin(`${lat.toFixed(6)}, ${lng.toFixed(6)}`, lat, lng);
                }
            });
        },
        (err) => {
            status.classList.add('hidden');
            const msgs = {
                1: 'Permissão de localização negada.',
                2: 'Localização indisponível.',
                3: 'Tempo esgotado ao obter localização.',
            };
            alert(msgs[err.code] ?? 'Erro ao obter localização.');
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
});
</script>
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places&callback=initMap"
    async defer>
</script>
@else
<script>
// Sem Maps API — aceita texto livre e coordenadas via geolocation simples
document.getElementById('gps-btn')?.addEventListener('click', () => {
    if (!navigator.geolocation) { alert('Seu navegador não suporta geolocalização.'); return; }
    const status = document.getElementById('gps-status');
    status.textContent = 'Obtendo localização...';
    status.classList.remove('hidden');
    navigator.geolocation.getCurrentPosition(pos => {
        status.classList.add('hidden');
        const val = `${pos.coords.latitude.toFixed(6)},${pos.coords.longitude.toFixed(6)}`;
        document.getElementById('origin-input').value  = val;
        document.getElementById('origin-value').value  = val;
        document.getElementById('origin-coords').value = val;
    }, () => { status.classList.add('hidden'); alert('Não foi possível obter a localização.'); });
});
</script>
@endif

<script>
// ── Submit do formulário ─────────────────────────────────
document.getElementById('ride-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const alertSuccess = document.getElementById('alert-success');
    const alertError   = document.getElementById('alert-error');
    alertSuccess.classList.add('hidden');
    alertError.classList.add('hidden');

    // Sincroniza o valor do input visível com o hidden (caso Maps não esteja ativo)
    const originInput = document.getElementById('origin-input');
    const destInput   = document.getElementById('destination-input');

    if (!document.getElementById('origin-value').value) {
        document.getElementById('origin-value').value = originInput.value;
    }
    if (!document.getElementById('destination-value').value) {
        document.getElementById('destination-value').value = destInput.value;
    }
    if (!document.getElementById('origin-coords').value) {
        document.getElementById('origin-coords').value = '0,0';
    }
    if (!document.getElementById('destination-coords').value) {
        document.getElementById('destination-coords').value = '0,0';
    }

    const payload = {
        origin:              document.getElementById('origin-value').value,
        destination:         document.getElementById('destination-value').value,
        origin_coords:       document.getElementById('origin-coords').value,
        destination_coords:  document.getElementById('destination-coords').value,
        scheduled_for:       new Date(document.getElementById('scheduled_for').value).toISOString(),
        seats_needed:        parseInt(document.getElementById('seats_needed').value),
    };

    if (!payload.origin || !payload.destination) {
        alertError.textContent = 'Preencha a origem e o destino.';
        alertError.classList.remove('hidden');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    const res = await fetch('/rides/request', {
        method:  'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept':       'application/json',
        },
        body: JSON.stringify(payload),
    });

    btn.disabled = false;
    btn.textContent = 'Solicitar Carona';

    if (res.status === 201) {
        alertSuccess.classList.remove('hidden');
        alertSuccess.scrollIntoView({ behavior: 'smooth' });
        e.target.reset();
        originPlace = null; destinationPlace = null;
        if (typeof originMarker !== 'undefined' && originMarker) originMarker.setMap(null);
        if (typeof destinationMarker !== 'undefined' && destinationMarker) destinationMarker.setMap(null);
        document.getElementById('origin-input').value = '';
        document.getElementById('destination-input').value = '';
    } else {
        const data = await res.json();
        const msgs = data.errors
            ? Object.values(data.errors).flat().join(' ')
            : (data.message ?? 'Erro ao enviar solicitação.');
        alertError.textContent = msgs;
        alertError.classList.remove('hidden');
    }
});
</script>
@endpush
