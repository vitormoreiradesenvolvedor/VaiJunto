.PHONY: up down restart install migrate test test-unit test-feature test-integration shell logs setup

# Sobe todos os containers
up:
	docker compose up -d

# Derruba os containers
down:
	docker compose down

# Reinicia os containers
restart:
	docker compose restart

# Instala dependências PHP via Composer
install:
	docker compose exec php composer install

# Executa as migrations no banco principal
migrate:
	docker compose exec php php artisan migrate

# Executa TODOS os testes (SQLite in-memory, sem precisar de MySQL)
test:
	docker compose exec php ./vendor/bin/pest

# Somente testes unitários
test-unit:
	docker compose exec php ./vendor/bin/pest --testsuite=Unit

# Somente testes de feature
test-feature:
	docker compose exec php ./vendor/bin/pest --testsuite=Feature

# Somente testes de integração
test-integration:
	docker compose exec php ./vendor/bin/pest --testsuite=Integration

# Abre shell no container PHP
shell:
	docker compose exec php bash

# Exibe os logs dos containers
logs:
	docker compose logs -f

# Configuração inicial completa
setup: up
	docker compose exec php composer install --no-interaction --prefer-dist
	docker compose exec php php artisan key:generate
	docker compose exec php php artisan migrate
	@echo ""
	@echo "VaiJunto está pronto! Acesse http://localhost"
	@echo "Para rodar os testes: make test"
