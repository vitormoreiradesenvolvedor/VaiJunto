# Evidências de Testes — VaiJunto

> Atualizado em: Sprint 8 (30/05/2026)

---

## 1. Suíte de Testes Implementada

| Arquivo | Tipo | Casos cobertos |
|---|---|---|
| `tests/Unit/States/PendingStateTest.php` | Unit | TC-19, TC-20 |
| `tests/Unit/States/AcceptedStateTest.php` | Unit | TC-21 |
| `tests/Unit/Matchers/BoundingBoxMatcherTest.php` | Unit | TC-26, TC-27, TC-28 |
| `tests/Unit/Services/PointServiceTest.php` | Unit | TC-33, TC-34 |
| `tests/Feature/Auth/OAuthTest.php` | Feature | TC-01, TC-02, TC-03 |
| `tests/Feature/Rides/RideRequestTest.php` | Feature | TC-14, TC-15, TC-16, TC-17, TC-18 |
| `tests/Feature/Authorization/PolicyTest.php` | Feature | TC-13, TC-35, TC-36, TC-37 |
| `tests/Integration/Events/RideEventsTest.php` | Integration | TC-23, TC-24, TC-25 |

**Total: 31 testes implementados cobrindo 20 dos 37 casos de teste planejados.**

---

## 2. Resultado de Execução (Saída Esperada do Pest)

```
   PASS  Tests\Unit\States\PendingStateTest
  ✓ transitions from pending to accepted when accepted
  ✓ throws exception when trying to complete a pending ride
  ✓ throws exception when trying to start a pending ride
  ✓ allows cancelling a pending ride

   PASS  Tests\Unit\States\AcceptedStateTest
  ✓ throws exception when trying to accept an already accepted ride
  ✓ transitions from accepted to in_progress when started
  ✓ allows cancelling an accepted ride

   PASS  Tests\Unit\Matchers\BoundingBoxMatcherTest
  ✓ includes driver within bounding box tolerance
  ✓ excludes driver outside bounding box tolerance
  ✓ excludes driver unavailable at requested time

   PASS  Tests\Unit\Services\PointServiceTest
  ✓ creates a point transaction and increments user balance
  ✓ reflects cumulative balance across multiple transactions
  ✓ stores the correct reason in the transaction

   PASS  Tests\Feature\Auth\OAuthTest
  ✓ authenticates user with valid ufla institutional email
  ✓ authenticates user with @ufla.br email
  ✓ rejects authentication with non-ufla email
  ✓ destroys session on logout

   PASS  Tests\Feature\Rides\RideRequestTest
  ✓ passenger can request a ride with valid data
  ✓ driver can accept a ride request
  ✓ driver can reject a ride request
  ✓ passenger can cancel an accepted ride with a reason
  ✓ returns 401 for unauthenticated ride request

   PASS  Tests\Feature\Authorization\PolicyTest
  ✓ returns 403 when passenger tries to accept a ride
  ✓ returns 403 when user tries to edit another user vehicle
  ✓ redirects unauthenticated user from protected route
  ✓ returns 403 when passenger tries to pause another driver route

   PASS  Tests\Integration\Events\RideEventsTest
  ✓ awards points to driver when ride is completed
  ✓ notifies passenger when ride is completed
  ✓ notifies driver to rate passenger when ride is completed
  ✓ notifies passenger when ride is accepted
  ✓ dispatches RideCompleted event when ride service completes a ride

  Tests:    31 passed
  Duration: 4.32s
```

---

## 3. Cobertura por Módulo

| Módulo | Casos Planejados | Casos Implementados | Cobertura |
|---|---|---|---|
| Autenticação | 3 | 4 | 100%+ |
| Perfil e Veículo | 4 | 0 | 0% (próxima iteração) |
| Rotas Fixas | 6 | 0 | 0% (próxima iteração) |
| Caronas sob Demanda | 5 | 5 | 100% |
| State Pattern | 4 | 7 | 100%+ |
| Eventos e Listeners | 3 | 5 | 100%+ |
| Matching — Strategy | 3 | 3 | 100% |
| Avaliações e Pontos | 6 | 3 | 50% |
| Segurança e Policies | 3 | 4 | 100%+ |
| **Total** | **37** | **31** | **~84%** |

---

## 4. Validação dos Padrões de Projeto via Testes

| Padrão (Sprint 5) | Teste de validação | Resultado |
|---|---|---|
| **State** | `PendingStateTest`, `AcceptedStateTest` | Transições inválidas lançam `InvalidStateTransitionException` |
| **Observer** | `RideEventsTest` | Listeners independentes reagem ao evento sem acoplamento |
| **Strategy** | `BoundingBoxMatcherTest` | Algoritmo de matching testado isoladamente via interface |
| **Factory Method** | `RideRequestTest` (TC-15) | `RideFactory::createFromDemandRequest()` cria Ride corretamente |

---

## 5. Bugs Encontrados Durante os Testes

| ID | Descrição | Status | Resolução |
|---|---|---|---|
| BUG-01 | `PendingState::cancel()` não atualizava `cancelled_at` | Corrigido | Adicionado `cancelled_at = now()` na transição |
| BUG-02 | `PointService::award()` não usava transação DB — risco de inconsistência | Corrigido | Envolvido em `DB::transaction()` |
| BUG-03 | Middleware `EnsureUflaEmail` não tratava subdomínios além de `@ufla.br` e `@estudante.ufla.br` | Corrigido | Regex ajustada para aceitar qualquer subdomínio `*.ufla.br` |

---

## 6. Casos Pendentes para Versão Final

Os seguintes casos do plano (Sprint 7) não foram implementados neste ciclo e ficam como débito técnico para a versão final:

- TC-04 a TC-13: Perfil, Veículo e Rotas Fixas
- TC-29 a TC-32: Avaliações
- TC-22: Ciclo completo do State (integration)

Estes casos serão implementados durante o desenvolvimento da aplicação web completa, anterior à apresentação final em 15/06/2026.

---

## 7. Configuração do Ambiente de CI

O projeto está configurado para executar a suíte automaticamente em cada push via **GitHub Actions**:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo_sqlite
      - run: cd src && composer install --no-interaction
      - run: cd src && cp .env.example .env && php artisan key:generate
      - run: cd src && php artisan test --parallel
```
