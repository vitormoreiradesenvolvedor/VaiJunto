<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VaiJunto') — VaiJunto</title>
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
            <a href="{{ route('dashboard') }}" class="text-white text-xl font-bold tracking-tight">
                VaiJunto
            </a>
            @auth
            <div class="flex items-center gap-4">
                <span class="text-blue-100 text-sm hidden sm:block">
                    🪙 {{ auth()->user()->points_balance ?? 0 }} pts
                </span>
                <div class="flex items-center gap-2">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="avatar"
                             class="w-8 h-8 rounded-full border-2 border-white">
                    @endif
                    <span class="text-white text-sm font-medium">
                        {{ auth()->user()->name }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-blue-200 hover:text-white text-sm transition">
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
</body>
</html>
