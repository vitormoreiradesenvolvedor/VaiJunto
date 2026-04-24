# Modelagem do Sistema — VaiJunto

> Atualizado em: Sprint 3 (25/04/2026)

---

## 1. Diagrama de Casos de Uso

```mermaid
flowchart TD
    subgraph Atores
        M[Membro UFLA\nnão autenticado]
        P[Passageiro]
        D[Motorista]
        A[Administrador]
    end

    subgraph Sistema VaiJunto
        UC01[Autenticar via OAuth UFLA]
        UC02[Gerenciar perfil]
        UC03[Configurar papel no sistema]

        subgraph Rotas Fixas
            UC04[Publicar rota fixa]
            UC05[Pausar / cancelar rota]
            UC06[Gerenciar inscrições]
            UC07[Buscar rotas fixas]
            UC08[Inscrever-se em rota]
        end

        subgraph Caronas sob Demanda
            UC09[Solicitar carona]
            UC10[Aceitar / recusar solicitação]
            UC11[Acompanhar status da carona]
            UC12[Cancelar carona]
        end

        subgraph Veículo
            UC13[Cadastrar veículo]
        end

        subgraph Pós-Carona
            UC14[Avaliar usuário]
            UC15[Visualizar avaliações]
        end

        subgraph Gamificação
            UC16[Acumular e visualizar pontos]
        end

        subgraph Administração
            UC17[Listar e moderar usuários]
        end
    end

    M --> UC01
    UC01 --> P
    UC01 --> D

    P --> UC02
    P --> UC03
    P --> UC07
    P --> UC08
    P --> UC09
    P --> UC11
    P --> UC12
    P --> UC14
    P --> UC15

    D --> UC02
    D --> UC03
    D --> UC13
    D --> UC04
    D --> UC05
    D --> UC06
    D --> UC10
    D --> UC12
    D --> UC14
    D --> UC15
    D --> UC16

    A --> UC17
    A --> UC17

    UC04 -.->|include| UC13
```

---

## 2. Diagrama de Classes

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string phone
        +string photo_url
        +enum role
        +float rating_avg
        +int points_balance
        +bool is_active
        +timestamp created_at
        +updateProfile()
        +setRole()
    }

    class Vehicle {
        +int id
        +int user_id
        +string model
        +int year
        +string color
        +string plate
        +int seats
        +register()
        +update()
        +remove()
    }

    class FixedRoute {
        +int id
        +int driver_id
        +int vehicle_id
        +string origin
        +string destination
        +string origin_coords
        +string destination_coords
        +time departure_time
        +array days_of_week
        +int available_seats
        +enum status
        +publish()
        +pause()
        +cancel()
        +getAvailableSeats()
    }

    class RouteSubscription {
        +int id
        +int passenger_id
        +int route_id
        +string pickup_point
        +string dropoff_point
        +enum status
        +timestamp requested_at
        +timestamp responded_at
        +subscribe()
        +approve()
        +reject()
        +cancel()
    }

    class RideRequest {
        +int id
        +int passenger_id
        +string origin
        +string destination
        +string origin_coords
        +string destination_coords
        +timestamp scheduled_for
        +int seats_needed
        +enum status
        +string cancel_reason
        +request()
        +cancel()
    }

    class Ride {
        +int id
        +int driver_id
        +int vehicle_id
        +enum type
        +enum status
        +timestamp started_at
        +timestamp completed_at
        +start()
        +complete()
        +cancel()
    }

    class RidePassenger {
        +int id
        +int ride_id
        +int passenger_id
        +string pickup_point
        +string dropoff_point
        +enum status
    }

    class Rating {
        +int id
        +int rater_id
        +int rated_id
        +int ride_id
        +int stars
        +string comment
        +timestamp created_at
        +submit()
    }

    class PointTransaction {
        +int id
        +int user_id
        +int amount
        +string reason
        +timestamp created_at
    }

    class Notification {
        +int id
        +int user_id
        +string type
        +string message
        +json payload
        +timestamp read_at
        +timestamp created_at
        +markAsRead()
    }

    User "1" --> "0..1" Vehicle : possui
    User "1" --> "0..*" FixedRoute : publica
    User "1" --> "0..*" RouteSubscription : solicita
    User "1" --> "0..*" RideRequest : cria
    User "1" --> "0..*" Rating : recebe
    User "1" --> "0..*" PointTransaction : acumula
    User "1" --> "0..*" Notification : recebe

    FixedRoute "1" --> "0..*" RouteSubscription : possui
    FixedRoute "1" --> "1" Vehicle : usa
    FixedRoute "1" --> "0..*" Ride : gera

    RideRequest "1" --> "0..1" Ride : origina
    Ride "1" --> "1..*" RidePassenger : contém
    Ride "1" --> "0..*" Rating : gera
    Ride "1" --> "1" Vehicle : usa

    RidePassenger "1" --> "1" User : referencia
```

---

## 3. Diagrama de Sequência — Autenticação via OAuth UFLA

```mermaid
sequenceDiagram
    actor Usuario
    participant Browser
    participant App as VaiJunto (Laravel)
    participant Google as Google OAuth
    participant DB as Banco de Dados

    Usuario->>Browser: Acessa /login
    Browser->>App: GET /login
    App-->>Browser: Exibe botão "Entrar com UFLA"

    Usuario->>Browser: Clica em "Entrar com UFLA"
    Browser->>App: GET /auth/google/redirect
    App-->>Browser: Redirect para Google OAuth

    Browser->>Google: GET /o/oauth2/auth?scope=email&...
    Google-->>Browser: Exibe tela de login Google

    Usuario->>Google: Insere credenciais @ufla.br
    Google-->>Browser: Redirect para /auth/google/callback?code=...

    Browser->>App: GET /auth/google/callback?code=...
    App->>Google: POST /token (troca code por access_token)
    Google-->>App: access_token + user info (email, name, photo)

    App->>App: Valida domínio do email
    alt Email não é @ufla.br ou @estudante.ufla.br
        App-->>Browser: Redirect /login?error=unauthorized_domain
        Browser-->>Usuario: "Acesso restrito à comunidade UFLA"
    else Email válido
        App->>DB: SELECT user WHERE email = ?
        alt Usuário não existe
            App->>DB: INSERT INTO users (name, email, photo_url)
        end
        App->>DB: UPDATE last_login_at
        App-->>Browser: Cria sessão + Redirect /dashboard
        Browser-->>Usuario: Dashboard do VaiJunto
    end
```

---

## 4. Diagrama de Sequência — Solicitação de Carona sob Demanda

```mermaid
sequenceDiagram
    actor Passageiro
    actor Motorista
    participant App as VaiJunto (Laravel)
    participant WS as Reverb (WebSocket)
    participant DB as Banco de Dados

    Passageiro->>App: POST /rides/request {origem, destino, horário}
    App->>DB: INSERT ride_requests (status=pending)
    App->>DB: SELECT motoristas disponíveis e compatíveis
    App->>WS: Broadcast RideRequested {request_id, rota, passageiro}
    WS-->>Motorista: Notificação: nova solicitação de carona

    Motorista->>App: POST /rides/{id}/accept
    App->>DB: UPDATE ride_requests SET status=accepted
    App->>DB: INSERT rides (driver_id, vehicle_id, type=demand, status=accepted)
    App->>WS: Broadcast RideAccepted {ride_id, motorista}
    WS-->>Passageiro: Notificação: carona aceita por [Motorista]

    App-->>Motorista: 200 OK

    Motorista->>App: POST /rides/{id}/start
    App->>DB: UPDATE rides SET status=in_progress, started_at=now()
    App->>WS: Broadcast RideStarted
    WS-->>Passageiro: Notificação: motorista a caminho

    Motorista->>App: POST /rides/{id}/complete
    App->>DB: UPDATE rides SET status=completed, completed_at=now()
    App->>DB: INSERT point_transactions (driver_id, +10 pts)
    App->>WS: Broadcast RideCompleted
    WS-->>Passageiro: Notificação: carona concluída — avalie o motorista
    WS-->>Motorista: Notificação: carona concluída — avalie o passageiro
```

---

## 5. Diagrama de Atividades — Inscrição em Rota Fixa

```mermaid
flowchart TD
    A([Passageiro acessa busca de rotas]) --> B[Informa origem, destino e horário]
    B --> C[Sistema exibe rotas disponíveis]
    C --> D{Existe rota compatível?}
    D -- Não --> E([Passageiro encerra sem inscrição])
    D -- Sim --> F[Passageiro seleciona rota e clica em Inscrever-se]
    F --> G{Há vagas disponíveis?}
    G -- Não --> H[Exibe mensagem: rota lotada]
    H --> E
    G -- Sim --> I[Sistema cria RouteSubscription com status=pending]
    I --> J[Notifica motorista sobre nova solicitação]
    J --> K{Motorista aprova?}
    K -- Não --> L[Status = rejected]
    L --> M[Notifica passageiro: solicitação recusada]
    M --> E
    K -- Sim --> N[Status = approved]
    N --> O[Decrementa vagas disponíveis na rota]
    O --> P[Notifica passageiro: inscrição confirmada]
    P --> Q([Passageiro inscrito com sucesso])
```

---

## 6. Vínculo entre Requisitos e Modelos

| Requisito | Modelo(s) relacionado(s) |
|---|---|
| RF-01 (Autenticação OAuth) | Diagrama de Sequência 3, Casos de Uso UC01 |
| RF-02 (Perfil) | Classe `User`, UC02 |
| RF-03 (Papel) | Atributo `role` em `User`, UC03 |
| RF-04 (Veículo) | Classe `Vehicle`, UC13 |
| RF-05 (Publicar rota fixa) | Classe `FixedRoute`, UC04 |
| RF-06 (Buscar rotas) | UC07 |
| RF-07 (Inscrever-se em rota) | Classe `RouteSubscription`, UC08, Diagrama de Atividades 5 |
| RF-08 (Aprovar/recusar inscrição) | `RouteSubscription.approve()`, UC06, Diagrama de Atividades 5 |
| RF-09 (Pausar/cancelar rota) | `FixedRoute.pause()`, `FixedRoute.cancel()`, UC05 |
| RF-10 (Solicitar carona sob demanda) | Classe `RideRequest`, UC09, Diagrama de Sequência 4 |
| RF-11 (Notificar motoristas) | Classe `Notification`, WebSocket no Diagrama de Sequência 4 |
| RF-12 (Aceitar/recusar solicitação) | `Ride`, UC10, Diagrama de Sequência 4 |
| RF-13 (Status da carona) | Atributo `status` em `Ride` e `RideRequest`, UC11 |
| RF-14 (Cancelar carona) | `Ride.cancel()`, UC12 |
| RF-15, RF-16 (Avaliações) | Classe `Rating`, UC14 |
| RF-17 (Exibir avaliação média) | `User.rating_avg`, UC15 |
| RF-18, RF-19 (Notificações) | Classe `Notification`, WebSocket nos diagramas de sequência |
| RF-20, RF-21 (Gamificação) | Classe `PointTransaction`, UC16 |
| RF-23, RF-24 (Mapas) | Atributos `*_coords` em `FixedRoute` e `RideRequest` |
| RF-25, RF-26 (Administração) | UC17 |
