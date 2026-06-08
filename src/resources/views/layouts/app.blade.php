<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VaiJunto') — VaiJunto</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#2563EB', dark: '#1D4ED8' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="bg-brand shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            {{-- Wordmark: Vai + J-estrada + unto --}}
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-baseline text-white text-xl font-bold tracking-tight leading-none">
                <span>Vai</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 46"
                     aria-hidden="true"
                     style="height:1.25em;width:auto;margin-bottom:-0.18em">
                    <!-- Superfície da estrada formando a letra J -->
                    <path d="M11.5,22 L11.5,37 Q11.5,43 7.5,43 Q3.5,43 3.5,38"
                          fill="none" stroke="white" stroke-width="6.5"
                          stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Faixa central tracejada amarela -->
                    <path d="M11.5,22 L11.5,37 Q11.5,43 7.5,43 Q3.5,43 3.5,38"
                          fill="none" stroke="#fbbf24" stroke-width="1.1"
                          stroke-dasharray="2.2,1.8" stroke-linecap="butt"/>
                </svg>
                <span>unto</span>
            </a>
            @auth
            @php $userMode = session('user_mode', 'passenger'); @endphp
            <div class="flex items-center gap-3">
                {{-- Toggle de modo --}}
                <div class="flex items-center bg-blue-700 rounded-full p-0.5">
                    <a href="{{ route('mode.set', 'passenger') }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition
                              {{ $userMode === 'passenger' ? 'bg-white text-blue-700' : 'text-blue-200 hover:text-white' }}">
                        Caronista
                    </a>
                    <a href="{{ route('mode.set', 'driver') }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition
                              {{ $userMode === 'driver' ? 'bg-white text-blue-700' : 'text-blue-200 hover:text-white' }}">
                        Motorista
                    </a>
                </div>
                <a href="{{ route('profile.show') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                    <img src="{{ auth()->user()->avatar ?? '' }}"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=1d4ed8&color=fff&size=40'"
                         alt="avatar"
                         class="w-8 h-8 rounded-full border-2 border-white object-cover">
                    <span class="text-white text-sm font-medium hidden sm:block">
                        {{ auth()->user()->name }}
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-blue-200 hover:text-white text-sm transition">
                        Sair
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </nav>

    {{-- Flash messages --}}
    <div class="max-w-5xl mx-auto px-4 w-full">
        @if(session('success'))
            <div class="mt-4 bg-green-100 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error_msg'))
            <div class="mt-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
                {{ session('error_msg') }}
            </div>
        @endif
    </div>

    {{-- Page content --}}
    <main class="flex-1 max-w-5xl mx-auto px-4 py-6 w-full">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-4">
        VaiJunto &copy; {{ date('Y') }} — UFLA
    </footer>

    @stack('scripts')

    {{-- Modal global de reputação --}}
    @auth
    <div id="reputation-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
         onclick="if(event.target===this)closeReputation()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                <h3 class="font-bold text-gray-900 text-base">Reputação</h3>
                <button onclick="closeReputation()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
            </div>
            <div id="reputation-content" class="overflow-y-auto p-5 space-y-1">
                <p class="text-center text-gray-400 text-sm py-8">Carregando...</p>
            </div>
        </div>
    </div>
    <script>
    async function showReputation(userId) {
        const modal   = document.getElementById('reputation-modal');
        const content = document.getElementById('reputation-content');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        content.innerHTML = '<p class="text-center text-gray-400 text-sm py-8">Carregando...</p>';

        try {
            const res  = await fetch(`/users/${userId}/reputation`, { headers: { 'Accept': 'application/json' } });
            const d    = await res.json();
            const total = d.total_ratings || 0;
            const filled = Math.round(d.avg_stars);

            const starsHtml = total > 0
                ? `<div class="text-yellow-400 text-2xl font-bold">${d.avg_stars} ${'★'.repeat(filled)}${'☆'.repeat(5-filled)}</div>
                   <p class="text-xs text-gray-400 mt-0.5">${total} avaliação${total !== 1 ? 'ões' : ''}</p>`
                : `<p class="text-sm text-gray-400">Sem avaliações ainda</p>`;

            const breakdownHtml = total > 0 ? [5,4,3,2,1].map(s => {
                const cnt = d.star_breakdown[s] || 0;
                const pct = Math.round(cnt / total * 100);
                return `<div class="flex items-center gap-2 text-xs">
                    <span class="w-3 text-gray-500 text-right">${s}</span>
                    <span class="text-yellow-400">★</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                        <div class="bg-yellow-400 h-1.5 rounded-full" style="width:${pct}%"></div>
                    </div>
                    <span class="w-4 text-gray-400 text-right">${cnt}</span>
                </div>`;
            }).join('') : '';

            const ratingsHtml = d.recent_ratings.length > 0
                ? d.recent_ratings.map(r => `
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-700">${r.rater_name}</span>
                            <span class="text-yellow-400 text-xs">${'★'.repeat(r.stars)}${'☆'.repeat(5-r.stars)}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">${r.role === 'passenger' ? 'Como passageiro' : 'Como motorista'} · ${r.created_at}</p>
                        ${r.comment ? `<p class="text-xs text-gray-600 mt-1 italic">"${r.comment}"</p>` : ''}
                    </div>`).join('')
                : '<p class="text-xs text-gray-400 text-center pt-3">Nenhum comentário ainda.</p>';

            const avatarSrc = d.avatar || '';
            content.innerHTML = `
                <div class="text-center mb-4">
                    <img src="${avatarSrc}"
                         onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(d.name)}&background=2563eb&color=fff&size=80'"
                         class="w-16 h-16 rounded-full object-cover border-2 border-blue-100 mx-auto">
                    <p class="font-bold text-gray-900 mt-2">${d.name}</p>
                    <div class="mt-1">${starsHtml}</div>
                </div>
                ${breakdownHtml ? `<div class="space-y-1.5 mb-3">${breakdownHtml}</div>` : ''}
                <div class="space-y-0">${ratingsHtml}</div>`;
        } catch {
            content.innerHTML = '<p class="text-center text-red-500 text-sm py-8">Erro ao carregar.</p>';
        }
    }
    function closeReputation() {
        const modal = document.getElementById('reputation-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    </script>
    @endauth

    @auth
    {{-- Laravel Echo + Pusher via CDN (Reverb usa protocolo Pusher) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        window.Echo = new Echo({
            broadcaster:        'reverb',
            key:                '{{ config("broadcasting.connections.reverb.key") }}',
            wsHost:             '{{ config("broadcasting.connections.reverb.options.host") }}',
            wsPort:             {{ config("broadcasting.connections.reverb.options.port", 80) }},
            wssPort:            {{ config("broadcasting.connections.reverb.options.port", 443) }},
            forceTLS:           {{ config("broadcasting.connections.reverb.options.scheme", "http") === "https" ? "true" : "false" }},
            enabledTransports:  ['ws', 'wss'],
            authEndpoint:       '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']")?.content ?? '',
                },
            },
        });

        window._vaijuntoUserId = {{ auth()->id() }};
        window.Echo.connector.pusher.connection.bind('connected',    () => console.log('[Echo] conectado'));
        window.Echo.connector.pusher.connection.bind('disconnected', () => console.warn('[Echo] desconectado'));

        // Toast global
        window.showToast = function(message, type = 'info') {
            const colors = { info: 'bg-blue-600', success: 'bg-green-600', warning: 'bg-yellow-500', error: 'bg-red-600' };
            const toast = document.createElement('div');
            toast.className = `fixed bottom-5 left-1/2 -translate-x-1/2 z-[100] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-lg transition-all duration-300 ${colors[type] ?? colors.info}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 4000);
        };

        // Dispara evento customizado para páginas escutarem
        window.dispatchEchoEvent = (name, data) => window.dispatchEvent(new CustomEvent('echo:' + name, { detail: data }));

        // Canal privado do usuário (motorista e passageiro)
        Echo.private(`user.${window._vaijuntoUserId}`)
            .listen('.NewRideRequestForDriver', (e) => {
                showToast(`🚗 Nova solicitação: ${e.origin} → ${e.destination}`, 'info');
                dispatchEchoEvent('NewRideRequestForDriver', e);
            })
            .listen('.TripRequestReceived', (e) => {
                showToast(`🙋 ${e.passenger.name} quer vaga na sua viagem!`, 'info');
                dispatchEchoEvent('TripRequestReceived', e);
            })
            .listen('.RideAccepted', (e) => {
                showToast('✅ Motorista aceitou sua carona!', 'success');
                dispatchEchoEvent('RideAccepted', e);
                // Redireciona automaticamente para acompanhamento, exceto se já está na página certa
                if (e.track_url && window.location.href !== e.track_url) {
                    setTimeout(() => { window.location.href = e.track_url; }, 800);
                }
            })
            .listen('.DriverArrived', (e) => {
                showToast('📍 Motorista chegou ao ponto de embarque!', 'success');
                dispatchEchoEvent('DriverArrived', e);
            })
            .listen('.PassengerBoarded', (e) => {
                showToast('✅ Passageiro confirmou embarque!', 'success');
                dispatchEchoEvent('PassengerBoarded', e);
            })
            .listen('.DriverLocationUpdated', (e) => {
                dispatchEchoEvent('DriverLocationUpdated', e);
            })
            .listen('.RideCancelledByDriver', (e) => {
                showToast('❌ O motorista cancelou a carona.', 'error');
                dispatchEchoEvent('RideCancelledByDriver', e);
            })
            .listen('.RideCancelledByPassenger', (e) => {
                showToast('❌ O passageiro cancelou a carona.', 'error');
                dispatchEchoEvent('RideCancelledByPassenger', e);
            });

        // Canal público de novas viagens avulsas
        Echo.channel('trips')
            .listen('.NewTripOffer', (e) => {
                showToast(`🆕 Nova carona: ${e.origin} → ${e.destination}`, 'info');
                dispatchEchoEvent('NewTripOffer', e);
            });

        // Canal público de novas rotas fixas
        Echo.channel('routes')
            .listen('.NewFixedRouteOffer', (e) => {
                showToast(`🔄 Nova rota fixa: ${e.origin} → ${e.destination}`, 'info');
                dispatchEchoEvent('NewFixedRouteOffer', e);
            });
    });
    </script>
    @endauth
</body>
</html>
