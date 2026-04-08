<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — VaiJunto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-600 to-blue-800 min-h-screen flex items-center justify-center px-4">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center">

        {{-- Logo --}}
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-3 shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 text-white" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 7h8M8 12h8m-8 5h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">VaiJunto</h1>
            <p class="text-sm text-gray-500 mt-1">Caronas solidárias para a comunidade UFLA</p>
        </div>

        {{-- Erro de domínio --}}
        @if(session('error') === 'unauthorized_domain')
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                Acesso permitido apenas para e-mails <strong>@ufla.br</strong>.
            </div>
        @endif

        {{-- Botão Google --}}
        <a href="{{ route('auth.google') }}"
           class="flex items-center justify-center gap-3 w-full border border-gray-300 rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Entrar com Google
        </a>

        <p class="mt-5 text-xs text-gray-400">
            Use seu e-mail institucional <span class="font-medium">@ufla.br</span>
        </p>
    </div>

</body>
</html>
