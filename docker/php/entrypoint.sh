#!/bin/bash
set -e

# Cria .env antes do composer install (package:discover precisa das variáveis)
if [ ! -f ".env" ]; then
    echo "[VaiJunto] Criando .env a partir do .env.example..."
    cp .env.example .env
fi

if [ ! -f "vendor/autoload.php" ]; then
    echo "[VaiJunto] Instalando dependências do Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Gera APP_KEY se ainda estiver vazio
if grep -q '^APP_KEY=$' .env 2>/dev/null; then
    echo "[VaiJunto] Gerando APP_KEY..."
    php artisan key:generate --no-interaction
fi

exec "$@"
