@extends('layouts.app')

@section('title', 'Criar Rota Fixa')

@section('content')

<div class="max-w-xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm transition">← Dashboard</a>
        <h2 class="text-xl font-bold text-gray-900">Nova Rota Fixa</h2>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800 mb-5">
        <strong>Rota fixa</strong> é uma carona recorrente que você oferece regularmente (ex.: todos os dias úteis às 07:30).
        Passageiros encontram sua rota e solicitam vaga a qualquer momento.
    </div>

    @if(!$vehicle)
    <div class="bg-amber-50 border border-amber-300 rounded-xl px-4 py-4 mb-5 flex items-center gap-3">
        <span class="text-2xl">🚗</span>
        <div class="flex-1">
            <p class="font-semibold text-sm text-amber-800">Cadastre seu veículo primeiro</p>
            <p class="text-xs text-amber-700 mt-0.5">Você precisa de um veículo para criar uma rota fixa.</p>
        </div>
        <a href="{{ route('vehicles.create') }}"
           class="flex-shrink-0 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
            Cadastrar
        </a>
    </div>
    @endif

    <form method="POST" action="{{ route('routes.store') }}" class="space-y-5">
        @csrf

        {{-- Mapa --}}
        @if($mapsKey)
        <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
            <div id="route-map" class="w-full h-48 bg-gray-100"></div>
        </div>
        @endif

        {{-- Origem --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Origem</label>
            <div class="relative">
                <input id="origin-input" name="origin" type="text"
                       value="{{ old('origin') }}"
                       placeholder="Ex.: UFLA — Universidade Federal de Lavras"
                       required
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                <button type="button" id="clear-origin"
                        onclick="clearRouteField('origin')"
                        class="{{ old('origin') ? '' : 'hidden' }} absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-base leading-none">✕</button>
                <input type="hidden" name="origin_coords" id="origin_coords" value="{{ old('origin_coords') }}">
            </div>
            @error('origin')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Destino --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Destino</label>
            <div class="relative">
                <input id="dest-input" name="destination" type="text"
                       value="{{ old('destination') }}"
                       placeholder="Ex.: Centro de Lavras"
                       required
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                <button type="button" id="clear-dest"
                        onclick="clearRouteField('dest')"
                        class="{{ old('destination') ? '' : 'hidden' }} absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-base leading-none">✕</button>
                <input type="hidden" name="destination_coords" id="destination_coords" value="{{ old('destination_coords') }}">
            </div>
            @error('destination')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Horário de partida --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Horário de partida</label>
            <input type="time" name="departure_time" value="{{ old('departure_time', '07:00') }}"
                   required
                   class="border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('departure_time')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Dias da semana --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Dias da semana</label>
            <div class="flex gap-2 flex-wrap">
                @php
                    $dayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                    $oldDays  = old('days_of_week', []);
                @endphp
                @foreach($dayNames as $i => $name)
                <label class="cursor-pointer select-none">
                    <input type="checkbox" name="days_of_week[]" value="{{ $i }}"
                           class="peer sr-only"
                           {{ in_array($i, $oldDays) ? 'checked' : '' }}>
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl text-sm font-bold border-2 transition
                                 border-gray-200 text-gray-400
                                 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                 hover:border-blue-400 hover:text-blue-600">
                        {{ $name }}
                    </span>
                </label>
                @endforeach
            </div>
            @error('days_of_week')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Vagas --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Vagas disponíveis</label>
            <div class="flex gap-2">
                @foreach([1,2,3,4,5,6,7,8] as $n)
                <label class="cursor-pointer select-none">
                    <input type="radio" name="available_seats" value="{{ $n }}"
                           class="peer sr-only"
                           {{ old('available_seats', $vehicle?->seats ?? 3) == $n ? 'checked' : '' }}>
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-sm font-bold border-2 transition
                                 border-gray-200 text-gray-500
                                 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                 hover:border-blue-400">
                        {{ $n }}
                    </span>
                </label>
                @endforeach
            </div>
            @error('available_seats')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        @if($errors->any() && !$errors->has('origin') && !$errors->has('destination') && !$errors->has('departure_time') && !$errors->has('days_of_week'))
        <div class="bg-red-50 border border-red-300 text-red-700 rounded-xl px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <button type="submit"
                {{ !$vehicle ? 'disabled' : '' }}
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold py-3.5 rounded-xl transition text-sm shadow">
            Publicar Rota Fixa
        </button>
    </form>
</div>

@endsection

@push('scripts')
@if($mapsKey)
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
({key: "{{ $mapsKey }}", v: "weekly"});

let map, originMarker, destMarker, routePoly;

function clearRouteField(type) {
    if (type === 'origin') {
        document.getElementById('origin-input').value = '';
        document.getElementById('origin_coords').value = '';
        document.getElementById('clear-origin').classList.add('hidden');
        if (originMarker) { originMarker.setMap(null); originMarker = null; }
    } else {
        document.getElementById('dest-input').value = '';
        document.getElementById('destination_coords').value = '';
        document.getElementById('clear-dest').classList.add('hidden');
        if (destMarker) { destMarker.setMap(null); destMarker = null; }
    }
    if (routePoly) { routePoly.setMap(null); routePoly = null; }
}

// Mostrar/ocultar botão ✕ conforme digitação
document.getElementById('origin-input').addEventListener('input', () => {
    document.getElementById('clear-origin').classList.toggle('hidden', !document.getElementById('origin-input').value);
});
document.getElementById('dest-input').addEventListener('input', () => {
    document.getElementById('clear-dest').classList.toggle('hidden', !document.getElementById('dest-input').value);
});

async function initMap() {
    const { Map } = await google.maps.importLibrary("maps");
    const { Autocomplete } = await google.maps.importLibrary("places");

    map = new Map(document.getElementById("route-map"), {
        center: { lat: -21.2342, lng: -44.9998 },
        zoom: 13,
        mapTypeControl: false, streetViewControl: false,
        fullscreenControl: false, rotateControl: false, zoomControl: false,
        gestureHandling: "cooperative",
    });

    function makeMarker(color) {
        return { path: google.maps.SymbolPath.CIRCLE, scale: 9,
                 fillColor: color, fillOpacity: 1, strokeColor: "#fff", strokeWeight: 2 };
    }

    function fitBothMarkers() {
        if (!originMarker || !destMarker) return;
        const b = new google.maps.LatLngBounds();
        b.extend(originMarker.getPosition()); b.extend(destMarker.getPosition());
        map.fitBounds(b, 36);
    }

    async function drawRoute() {
        if (!originMarker || !destMarker) return;
        const oPos = originMarker.getPosition();
        const dPos = destMarker.getPosition();
        if (routePoly) routePoly.setMap(null);
        try {
            const res = await fetch(`/api/directions?origin=${oPos.lat()},${oPos.lng()}&destination=${dPos.lat()},${dPos.lng()}`);
            if (!res.ok) throw new Error();
            const { path } = await res.json();
            routePoly = new google.maps.Polyline({ path, strokeColor: "#2563EB", strokeWeight: 4, strokeOpacity: 0.85, map });
        } catch {
            routePoly = new google.maps.Polyline({
                path: [{ lat: oPos.lat(), lng: oPos.lng() }, { lat: dPos.lat(), lng: dPos.lng() }],
                strokeColor: "#2563EB", strokeWeight: 3, strokeOpacity: 0.6, geodesic: true, map,
            });
        }
    }

    function setupAutocomplete(inputId, coordsId, clearBtnId, onSet) {
        const input = document.getElementById(inputId);
        const ac = new Autocomplete(input, { fields: ["formatted_address","geometry"], types: ["geocode","establishment"] });
        ac.addListener("place_changed", () => {
            const place = ac.getPlace();
            if (!place.geometry) return;
            const loc = place.geometry.location;
            document.getElementById(coordsId).value = `${loc.lat()},${loc.lng()}`;
            input.value = place.formatted_address;
            document.getElementById(clearBtnId).classList.remove('hidden');
            onSet({ lat: loc.lat(), lng: loc.lng() });
        });
    }

    setupAutocomplete("origin-input", "origin_coords", "clear-origin", (pos) => {
        if (originMarker) originMarker.setMap(null);
        originMarker = new google.maps.Marker({ map, position: pos, icon: makeMarker("#16a34a") });
        if (destMarker) { fitBothMarkers(); } else { map.panTo(pos); map.setZoom(15); }
        drawRoute();
    });

    setupAutocomplete("dest-input", "destination_coords", "clear-dest", (pos) => {
        if (destMarker) destMarker.setMap(null);
        destMarker = new google.maps.Marker({ map, position: pos, icon: makeMarker("#dc2626") });
        if (originMarker) { fitBothMarkers(); } else { map.panTo(pos); map.setZoom(15); }
        drawRoute();
    });
}

initMap();
</script>
@endif
@endpush
