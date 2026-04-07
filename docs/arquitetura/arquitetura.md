# Arquitetura de Software — VaiJunto

> Atualizado em: Sprint 6 (16/05/2026)

---

## 1. Visão Geral

O VaiJunto adota uma **arquitetura em camadas** (Layered Architecture) combinada com **orientação a eventos** (Event-Driven) para comunicação assíncrona entre módulos. A aplicação segue o padrão **MVC** do Laravel, estendido com uma camada de serviços (Service Layer) para isolar a lógica de negócio dos controllers.

### Estilo Arquitetural

| Estilo | Aplicação no VaiJunto |
|---|---|
| Layered (4 camadas) | Apresentação → Aplicação → Domínio → Infraestrutura |
| MVC | Laravel: Controllers, Blade/Livewire Views, Eloquent Models |
| Event-Driven | Eventos Laravel para comunicação desacoplada entre módulos |
| Client-Server | Browser ↔ Laravel App via HTTP/WebSocket |

---

## 2. Diagrama de Contexto (C4 — Nível 1)

```mermaid
graph TB
    subgraph Usuários
        U1[Passageiro\nbrowser / mobile]
        U2[Motorista\nbrowser / mobile]
        U3[Administrador\nbrowser]
    end

    subgraph VaiJunto
        APP[Aplicação Web\nLaravel 11]
    end

    subgraph Sistemas Externos
        GOOGLE[Google OAuth 2.0\nautenticação]
        MAPS[Google Maps API\ngeocodificação e rotas]
    end

    U1 -- HTTPS --> APP
    U2 -- HTTPS --> APP
    U3 -- HTTPS --> APP
    APP -- OAuth redirect --> GOOGLE
    GOOGLE -- callback token --> APP
    APP -- geocode / directions --> MAPS
    MAPS -- JSON response --> APP
```

---

## 3. Diagrama de Contêineres (C4 — Nível 2)

```mermaid
graph TB
    subgraph Docker Compose — VaiJunto
        NGINX[nginx\nReverse Proxy\n:80 / :443]
        PHP[php-fpm\nLaravel 11\nApp Server]
        REVERB[laravel-reverb\nWebSocket Server\n:8080]
        MYSQL[mysql:8.0\nBanco de Dados\n:3306]
        REDIS[redis:7\nCache · Queue · Sessions\n:6379]
        WORKER[queue-worker\nLaravel Queue\n—]
    end

    Browser -- HTTP/HTTPS --> NGINX
    Browser -- WS/WSS --> REVERB
    NGINX -- FastCGI --> PHP
    PHP -- TCP --> MYSQL
    PHP -- TCP --> REDIS
    PHP -- publish events --> REVERB
    WORKER -- consume jobs --> REDIS
    WORKER -- TCP --> MYSQL
```

### Responsabilidades dos Contêineres

| Contêiner | Imagem | Responsabilidade |
|---|---|---|
| `nginx` | `nginx:alpine` | Reverse proxy, SSL termination, arquivos estáticos |
| `php-fpm` | `php:8.3-fpm` + Laravel | Lógica de aplicação, controllers, services, models |
| `laravel-reverb` | `php:8.3-cli` | Servidor WebSocket para notificações em tempo real |
| `mysql` | `mysql:8.0` | Persistência relacional de todos os dados do domínio |
| `redis` | `redis:7-alpine` | Cache de consultas, filas de jobs, armazenamento de sessões |
| `queue-worker` | `php:8.3-cli` | Processa jobs assíncronos (notificações email, pontos) |

---

## 4. Diagrama de Camadas (Layered Architecture)

```mermaid
graph TB
    subgraph Camada de Apresentação
        BLADE[Blade Templates]
        LW[Livewire Components\nRideRequestForm\nRouteSearchForm\nRideStatusTracker]
    end

    subgraph Camada de Aplicação
        CTRL[Controllers HTTP\nAuthController\nUserController\nRideController\nFixedRouteController]
        MW[Middleware\nEnsureUflaEmail\nCheckRole]
        FORM[Form Requests\nvalidação de entrada]
    end

    subgraph Camada de Domínio
        SVC[Services\nRideService\nRouteService\nNotificationService\nPointService]
        EVT[Events + Listeners\nRideCompleted\nRideAccepted\nSubscriptionApproved]
        MDL[Models Eloquent\nUser · Vehicle · Ride\nFixedRoute · Rating]
        POL[Policies\nRidePolicy\nFixedRoutePolicy]
    end

    subgraph Camada de Infraestrutura
        REPO[Repositories\nRideRepository\nUserRepository]
        ADPT[Adapters Externos\nGoogleGeocodingAdapter\nGoogleDirectionsAdapter]
        JOBS[Queue Jobs\nSendNotificationJob\nAwardPointsJob]
        CACHE[Cache Layer\nRedis via Laravel Cache]
    end

    BLADE --> CTRL
    LW --> CTRL
    CTRL --> MW
    CTRL --> FORM
    CTRL --> SVC
    SVC --> EVT
    SVC --> MDL
    SVC --> POL
    MDL --> REPO
    SVC --> ADPT
    EVT --> JOBS
    REPO --> CACHE
```

---

## 5. Diagrama de Componentes — Módulo Ride (C4 — Nível 3)

```mermaid
graph LR
    subgraph HTTP Layer
        RC[RideController]
        FR[RideFormRequest]
    end

    subgraph Domain Layer
        RS[RideService]
        RM[RideMatcherInterface\nBoundingBoxMatcher]
        RF[RideFactory]
        RST[RideState\nPending · Accepted · InProgress]
    end

    subgraph Infrastructure Layer
        REPO[RideRepository]
        NS[NotificationService]
        PS[PointService]
    end

    subgraph Events
        RA[RideAccepted]
        RC2[RideCompleted]
    end

    RC --> FR
    RC --> RS
    RS --> RM
    RS --> RF
    RS --> RST
    RS --> REPO
    RS -.-> RA
    RS -.-> RC2
    RA --> NS
    RC2 --> NS
    RC2 --> PS
```

---

## 6. Fluxo de Dados — Carona sob Demanda (end-to-end)

```mermaid
sequenceDiagram
    participant B as Browser (Livewire)
    participant N as nginx
    participant P as php-fpm (Laravel)
    participant R as Redis (Queue)
    participant W as queue-worker
    participant DB as MySQL
    participant WS as Reverb (WebSocket)

    B->>N: POST /rides/request
    N->>P: FastCGI
    P->>P: RideFormRequest::validate()
    P->>DB: INSERT ride_requests
    P->>P: RideMatcherService::findDrivers()
    P->>DB: SELECT drivers disponíveis
    P->>R: dispatch(NotifyDriversJob)
    P-->>B: 201 Created {ride_request_id}

    W->>R: consume NotifyDriversJob
    W->>DB: INSERT notifications
    W->>WS: broadcast RideRequested

    WS-->>B: evento WS: nova solicitação (motorista vê)
    B->>N: POST /rides/{id}/accept
    N->>P: FastCGI
    P->>DB: INSERT rides + UPDATE ride_requests
    P->>P: event(RideAccepted)
    P->>R: dispatch(NotifyPassengerJob)
    P-->>B: 200 OK

    W->>R: consume NotifyPassengerJob
    W->>WS: broadcast RideAccepted
    WS-->>B: evento WS: carona aceita (passageiro vê)
```

---

## 7. Esquema do Banco de Dados

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string phone
        string photo_url
        enum role
        float rating_avg
        int points_balance
        bool is_active
        timestamp created_at
    }
    vehicles {
        bigint id PK
        bigint user_id FK
        string model
        int year
        string color
        string plate UK
        int seats
    }
    fixed_routes {
        bigint id PK
        bigint driver_id FK
        bigint vehicle_id FK
        string origin
        string destination
        point origin_coords
        point destination_coords
        time departure_time
        json days_of_week
        int available_seats
        enum status
    }
    route_subscriptions {
        bigint id PK
        bigint passenger_id FK
        bigint route_id FK
        string pickup_point
        string dropoff_point
        enum status
        timestamp requested_at
        timestamp responded_at
    }
    ride_requests {
        bigint id PK
        bigint passenger_id FK
        string origin
        string destination
        point origin_coords
        point destination_coords
        timestamp scheduled_for
        int seats_needed
        enum status
        string cancel_reason
    }
    rides {
        bigint id PK
        bigint driver_id FK
        bigint vehicle_id FK
        bigint ride_request_id FK
        bigint fixed_route_id FK
        enum type
        enum status
        timestamp started_at
        timestamp completed_at
    }
    ride_passengers {
        bigint id PK
        bigint ride_id FK
        bigint passenger_id FK
        string pickup_point
        string dropoff_point
        enum status
    }
    ratings {
        bigint id PK
        bigint rater_id FK
        bigint rated_id FK
        bigint ride_id FK
        tinyint stars
        text comment
        timestamp created_at
    }
    point_transactions {
        bigint id PK
        bigint user_id FK
        int amount
        string reason
        timestamp created_at
    }
    notifications {
        bigint id PK
        bigint user_id FK
        string type
        json payload
        timestamp read_at
        timestamp created_at
    }

    users ||--o{ vehicles : possui
    users ||--o{ fixed_routes : publica
    users ||--o{ route_subscriptions : faz
    users ||--o{ ride_requests : cria
    users ||--o{ ride_passengers : participa
    users ||--o{ ratings : recebe
    users ||--o{ point_transactions : acumula
    users ||--o{ notifications : recebe
    fixed_routes ||--o{ route_subscriptions : tem
    fixed_routes ||--o{ rides : origina
    ride_requests ||--o| rides : origina
    rides ||--o{ ride_passengers : inclui
    rides ||--o{ ratings : gera
    vehicles ||--o{ rides : usado_em
```

---

## 8. Justificativa das Escolhas Arquiteturais

### 8.1 Layered Architecture

**Motivo:** separa claramente apresentação, lógica de negócio e infraestrutura. Mudanças em uma camada não afetam as demais. Diretamente alinhado com SRP e DIP da Sprint 4.

**Alternativa rejeitada:** arquitetura monolítica sem camadas (fat controllers) — inviabiliza testes e manutenção.

### 8.2 Event-Driven para comunicação entre módulos

**Motivo:** módulos como Gamificação e Notificações não devem ser chamados diretamente pelo módulo de Ride. Eventos garantem desacoplamento e extensibilidade (OCP). Jobs na fila garantem que falhas em notificações não interrompem o fluxo principal.

**Alternativa rejeitada:** chamadas diretas entre services — gera acoplamento e dificulta testes.

### 8.3 Redis para filas, cache e sessões

**Motivo:** driver único para três necessidades (filas assíncronas, cache de consultas frequentes, sessões). Evita sobrecarga no MySQL com queries repetidas (ex: perfil do usuário, lista de rotas ativas).

**Alternativa rejeitada:** MySQL para filas — baixo desempenho em alta concorrência; arquivos para cache — não funciona em múltiplas instâncias.

### 8.4 nginx como reverse proxy

**Motivo:** separar o servidor HTTP (nginx) do servidor de aplicação (php-fpm) é prática padrão de produção. nginx serve arquivos estáticos sem passar pelo PHP e faz SSL termination.

### 8.5 Livewire para reatividade no frontend

**Motivo:** eliminando a necessidade de construir uma API REST separada, Livewire mantém o código PHP/Blade como única fonte de verdade. Reduz a superfície de ataque e o esforço de desenvolvimento.

---

## 9. Atributos de Qualidade

| Atributo | Estratégia arquitetural |
|---|---|
| **Segurança** | Middleware `EnsureUflaEmail` valida domínio em toda requisição autenticada; Policies do Laravel controlam autorização por recurso |
| **Desempenho** | Redis para cache de rotas e sessões; jobs assíncronos para operações lentas (notificações) |
| **Escalabilidade** | Múltiplos workers de fila independentes; nginx com upstream balanceado para múltiplas instâncias php-fpm |
| **Manutenibilidade** | Service Layer + padrões GoF (Sprint 5) garantem baixo acoplamento; cobertura de testes por camada |
| **Disponibilidade** | Docker Compose com restart policies; Redis como buffer para operações não críticas |
| **Portabilidade** | Docker garante paridade entre desenvolvimento, CI e produção |
