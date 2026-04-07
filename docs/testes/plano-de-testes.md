# Plano de Testes — VaiJunto

> Atualizado em: Sprint 7 (23/05/2026)

---

## 1. Objetivos

- Verificar que cada requisito funcional se comporta conforme especificado
- Validar as transições de estado do ciclo de vida da carona
- Garantir que regras de autorização (Policies) impeçam acessos indevidos
- Confirmar que eventos e listeners funcionam de forma desacoplada
- Detectar regressões ao longo das próximas sprints

---

## 2. Escopo

| Incluído | Excluído |
|---|---|
| Services, Factories, States (unit) | Testes de carga/performance |
| Controllers, Events, Listeners (feature/integration) | Testes de UI visual (screenshots) |
| Autorização via Policies | Testes de infraestrutura Docker |
| Validação de FormRequests | Integração real com Google Maps API |
| Fluxos críticos end-to-end (feature) | Testes de acessibilidade |

---

## 3. Estratégia e Tipos de Teste

| Tipo | Ferramenta | Escopo | Banco |
|---|---|---|---|
| **Unitário** | Pest + Mockery | Services, Factories, States, Matchers | Nenhum (mocks) |
| **Feature (HTTP)** | Pest + Laravel TestCase | Controllers, rotas, FormRequests, Policies | SQLite in-memory |
| **Integração** | Pest + RefreshDatabase | Events + Listeners, fluxos completos | SQLite in-memory |
| **Aceitação** | Pest (critérios das US) | Fluxos end-to-end por história de usuário | SQLite in-memory |

> **Pest** é o framework padrão no Laravel 11. Testes ficam em `tests/Unit/` e `tests/Feature/`.

---

## 4. Casos de Teste

### Módulo: Autenticação (RF-01)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-01 | Login com email `@estudante.ufla.br` válido | Feature | Token OAuth com email válido | Sessão criada, redirect para `/dashboard` |
| TC-02 | Login com email `@gmail.com` | Feature | Token OAuth com email externo | Redirect `/login?error=unauthorized_domain` |
| TC-03 | Logout encerra sessão | Feature | Requisição POST `/logout` autenticada | Sessão destruída, redirect `/login` |

---

### Módulo: Perfil e Veículo (RF-02, RF-03, RF-04)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-04 | Atualizar perfil com dados válidos | Feature | `name`, `phone` preenchidos | HTTP 200, dados atualizados no banco |
| TC-05 | Cadastrar veículo com placa válida (ABC-1234) | Feature | Placa `ABC-1234`, vagas `4` | HTTP 201, veículo salvo |
| TC-06 | Cadastrar veículo com placa inválida | Feature | Placa `INVALIDA` | HTTP 422, erro de validação |
| TC-07 | Publicar rota sem veículo cadastrado | Feature | Driver sem veículo → POST `/routes` | HTTP 403, mensagem de erro |

---

### Módulo: Rotas Fixas (RF-05 a RF-09)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-08 | Motorista publica rota com dados válidos | Feature | Origem, destino, horário, dias | HTTP 201, rota salva com `status=active` |
| TC-09 | Passageiro se inscreve em rota com vaga disponível | Feature | `route_id` com vagas > 0 | HTTP 201, `RouteSubscription` com `status=pending` |
| TC-10 | Motorista aprova inscrição | Feature | `subscription_id` válida | HTTP 200, `status=approved`, vagas decrementadas |
| TC-11 | Inscrição em rota sem vagas | Feature | `route_id` com `available_seats=0` | HTTP 422, mensagem "rota sem vagas" |
| TC-12 | Motorista pausa rota ativa | Feature | POST `/routes/{id}/pause` | HTTP 200, `status=paused` |
| TC-13 | Passageiro tenta pausar rota de outro usuário | Feature | Passageiro autenticado | HTTP 403, Policy nega |

---

### Módulo: Caronas sob Demanda (RF-10 a RF-14)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-14 | Passageiro solicita carona com dados válidos | Feature | Origem, destino, `scheduled_for` | HTTP 201, `RideRequest` com `status=pending` |
| TC-15 | Motorista aceita solicitação | Feature | POST `/rides/{id}/accept` | HTTP 200, `Ride` criado com `status=accepted` |
| TC-16 | Motorista recusa solicitação | Feature | POST `/rides/{id}/reject` | HTTP 200, `RideRequest` com `status=rejected` |
| TC-17 | Passageiro cancela carona aceita com justificativa | Feature | POST `/rides/{id}/cancel` + `reason` | HTTP 200, `status=cancelled`, `cancel_reason` salvo |
| TC-18 | Acesso a endpoint de carona sem autenticação | Feature | GET `/rides` sem token | HTTP 401 |

---

### Módulo: Ciclo de Vida da Carona — State Pattern (RF-13)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-19 | Transição válida: `pending` → `accepted` | Unit | `PendingState::accept()` | Estado muda para `AcceptedState`, evento `RideAccepted` disparado |
| TC-20 | Transição inválida: `pending` → `completed` | Unit | `PendingState::complete()` | `InvalidStateTransitionException` lançada |
| TC-21 | Transição inválida: `completed` → `accepted` | Unit | `CompletedState::accept()` | `InvalidStateTransitionException` lançada |
| TC-22 | Ciclo completo válido: `pending` → `accepted` → `in_progress` → `completed` | Integration | Sequência de chamadas no `RideService` | Estado final `completed`, `completed_at` preenchido |

---

### Módulo: Eventos e Listeners (RF-18, RF-19, RF-20)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-23 | `RideCompleted` dispara `AwardPointsOnRideCompleted` | Integration | `event(new RideCompleted($ride))` | `PointTransaction` criada com `amount=10` para o motorista |
| TC-24 | `RideCompleted` dispara notificação ao passageiro | Integration | `event(new RideCompleted($ride))` | `Notification` criada para o passageiro com `type=ride_completed` |
| TC-25 | `RideAccepted` notifica passageiro | Integration | `event(new RideAccepted($ride))` | `Notification` criada para o passageiro com `type=ride_accepted` |

---

### Módulo: Matching — Strategy Pattern (RF-11)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-26 | `BoundingBoxMatcher` retorna motorista dentro do raio | Unit | Driver a 0.03° de distância, disponível no horário | Driver incluído na coleção retornada |
| TC-27 | `BoundingBoxMatcher` não retorna motorista fora do raio | Unit | Driver a 0.10° de distância | Driver não incluído na coleção |
| TC-28 | `BoundingBoxMatcher` ignora motorista indisponível no horário | Unit | Driver dentro do raio, mas ocupado | Driver não incluído na coleção |

---

### Módulo: Avaliações (RF-15, RF-16, RF-17)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-29 | Passageiro avalia motorista com 5 estrelas após carona | Feature | `stars=5`, `ride_id` com `status=completed` | HTTP 201, `Rating` salvo, `rating_avg` do motorista atualizado |
| TC-30 | Avaliação com `stars=0` | Feature | `stars=0` | HTTP 422, erro de validação |
| TC-31 | Avaliação em carona não concluída | Feature | `ride_id` com `status=accepted` | HTTP 403, Policy nega |
| TC-32 | Avaliação duplicada na mesma carona | Feature | Segundo POST `/ratings` para mesmo `ride_id` | HTTP 422, "já avaliado" |

---

### Módulo: Gamificação (RF-20, RF-21)

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-33 | Saldo de pontos reflete transações | Unit | Três `PointTransaction` de 10pts | `points_balance = 30` |
| TC-34 | `PointService::award()` cria transação e atualiza saldo | Unit | `award($userId, 10, 'carona')` | Transação salva, `users.points_balance` incrementado |

---

### Módulo: Segurança e Autorização

| ID | Caso de Teste | Tipo | Entrada | Resultado Esperado |
|---|---|---|---|---|
| TC-35 | Passageiro não pode aceitar carona (Policy) | Feature | POST `/rides/{id}/accept` por passageiro | HTTP 403 |
| TC-36 | Usuário não pode editar veículo de outro usuário | Feature | PUT `/vehicles/{id}` por outro usuário | HTTP 403 |
| TC-37 | Rota protegida redireciona não autenticado | Feature | GET `/dashboard` sem sessão | Redirect `/login` |

---

## 5. Cenários de Aceitação por História de Usuário

### US-01 — Autenticação institucional
```
Dado que sou membro da UFLA com email @estudante.ufla.br
Quando realizo login via Google OAuth
Então devo ser redirecionado ao dashboard do VaiJunto
E minha sessão deve ser mantida por 8 horas

Dado que possuo email @gmail.com
Quando tento realizar login via Google OAuth
Então devo ver a mensagem "Acesso restrito à comunidade UFLA"
```

### US-10 — Solicitar carona sob demanda
```
Dado que sou passageiro autenticado
Quando solicito uma carona informando origem, destino e horário válidos
Então uma RideRequest deve ser criada com status "pendente"
E os motoristas compatíveis devem ser notificados em tempo real

Dado que solicito uma carona sem informar o destino
Então devo ver um erro de validação no campo destino
```

### US-12 — Motorista aceita solicitação
```
Dado que sou motorista com veículo cadastrado
Quando aceito uma solicitação de carona pendente
Então uma Ride deve ser criada com status "aceita"
E o passageiro deve receber uma notificação imediata
```

### US-16 — Avaliação pós-carona
```
Dado que sou passageiro e minha carona foi concluída
Quando avalio o motorista com 4 estrelas
Então a avaliação deve ser salva
E a média do motorista deve ser recalculada

Dado que minha carona ainda não foi concluída
Quando tento avaliar o motorista
Então devo receber um erro de autorização
```

---

## 6. Matriz de Rastreabilidade Requisitos × Testes

| Requisito | Casos de Teste |
|---|---|
| RF-01 (Autenticação OAuth) | TC-01, TC-02, TC-03 |
| RF-02 (Perfil) | TC-04 |
| RF-03 (Papel) | TC-07 |
| RF-04 (Veículo) | TC-05, TC-06, TC-07 |
| RF-05 (Publicar rota fixa) | TC-08 |
| RF-06 (Buscar rotas) | TC-09 |
| RF-07 (Inscrever-se em rota) | TC-09, TC-11 |
| RF-08 (Aprovar inscrição) | TC-10, TC-13 |
| RF-09 (Pausar/cancelar rota) | TC-12, TC-13 |
| RF-10 (Solicitar carona) | TC-14 |
| RF-11 (Notificar motoristas) | TC-24, TC-25, TC-26, TC-27, TC-28 |
| RF-12 (Aceitar/recusar) | TC-15, TC-16 |
| RF-13 (Status da carona) | TC-17, TC-19, TC-20, TC-21, TC-22 |
| RF-14 (Cancelar carona) | TC-17 |
| RF-15 (Avaliar motorista) | TC-29, TC-30, TC-31, TC-32 |
| RF-16 (Avaliar passageiro) | TC-29, TC-31 |
| RF-17 (Exibir avaliação) | TC-29 |
| RF-18 (Notificações tempo real) | TC-24, TC-25 |
| RF-19 (Notificar cancelamento) | TC-24 |
| RF-20 (Creditar pontos) | TC-23, TC-34 |
| RF-21 (Saldo de pontos) | TC-33, TC-34 |
| RNF-02 (Acesso restrito UFLA) | TC-02, TC-37 |
| RNF-03 (Senhas não em texto claro) | TC-01 (validar que token não é armazenado) |

---

## 7. Estrutura dos Testes no Repositório

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── RideServiceTest.php          (TC-22)
│   │   └── PointServiceTest.php         (TC-33, TC-34)
│   ├── States/
│   │   ├── PendingStateTest.php         (TC-19, TC-20)
│   │   └── AcceptedStateTest.php        (TC-21)
│   ├── Matchers/
│   │   └── BoundingBoxMatcherTest.php   (TC-26, TC-27, TC-28)
│   └── Factories/
│       └── RideFactoryTest.php
├── Feature/
│   ├── Auth/
│   │   └── OAuthTest.php                (TC-01, TC-02, TC-03)
│   ├── User/
│   │   └── ProfileTest.php              (TC-04)
│   ├── Vehicle/
│   │   └── VehicleTest.php              (TC-05, TC-06, TC-07)
│   ├── Routes/
│   │   └── FixedRouteTest.php           (TC-08 a TC-13)
│   ├── Rides/
│   │   └── RideRequestTest.php          (TC-14 a TC-18)
│   ├── Ratings/
│   │   └── RatingTest.php               (TC-29 a TC-32)
│   └── Authorization/
│       └── PolicyTest.php               (TC-35, TC-36, TC-37)
└── Integration/
    └── Events/
        └── RideEventsTest.php           (TC-23, TC-24, TC-25)
```

---

## 8. Critérios de Aceitação dos Testes

- Todos os testes unitários e de feature devem passar antes de qualquer merge na branch principal
- Cobertura mínima esperada: 70% das classes de `app/Services/` e `app/States/`
- Nenhum teste deve depender de serviços externos (Google Maps, Reverb) — usar mocks/fakes
- Banco de dados de testes: SQLite in-memory via `RefreshDatabase` trait
- Tempo máximo de execução da suíte completa: 60 segundos

---

## 9. Ferramentas

| Ferramenta | Versão | Uso |
|---|---|---|
| Pest | ^3.0 | Framework principal de testes |
| Mockery | ^1.6 | Mocks para Services e Adapters |
| Laravel Factories | nativo | Geração de dados de teste |
| SQLite | in-memory | Banco isolado para testes |
| GitHub Actions | — | Execução automática da suíte em cada push |
