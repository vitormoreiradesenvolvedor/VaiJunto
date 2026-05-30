#!/bin/bash
set -e

echo "[VaiJunto] Descobrindo pacotes Laravel..."
php artisan package:discover --ansi

echo "[VaiJunto] Aguardando banco de dados..."
until php artisan db:monitor --databases=pgsql 2>/dev/null || php -r "
    \$host = getenv('DB_HOST') ?: 'localhost';
    \$port = getenv('DB_PORT') ?: 5432;
    \$sock = @fsockopen(\$host, \$port, \$e, \$m, 3);
    if (!\$sock) { exit(1); }
    fclose(\$sock);
" 2>/dev/null; do
    echo "[VaiJunto] Banco não disponível ainda, aguardando 3s..."
    sleep 3
done

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "[VaiJunto] Rodando migrations..."
    php artisan migrate --force --no-interaction

    echo "[VaiJunto] Otimizando caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
