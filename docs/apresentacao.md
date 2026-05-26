# Guia da Apresentação Final — VaiJunto

> Data: 15/06/2026 | Tempo: 5 minutos | GCC188 — Engenharia de Software

---

## Roteiro (5 minutos)

### 0:00 – 0:45 — Problema e Proposta

> **Quem fala:** qualquer integrante

"Membros da UFLA não têm uma solução gratuita, segura e centralizada para compartilhar caronas. WhatsApp é desorganizado, Uber é caro e o transporte público tem horários escassos.

O **VaiJunto** é uma plataforma web que conecta passageiros e motoristas da comunidade UFLA de forma gratuita, com autenticação pelo email institucional, modelo híbrido de caronas — rotas fixas e sob demanda — e sistema de pontos para incentivar motoristas."

---

### 0:45 – 1:30 — Processo: Scrum e GitHub

> **Quem fala:** Scrum Master (Rafaella)

"Usamos Scrum com 8 sprints. Cada sprint tem backlog, registro, kanban e tag Git. O repositório tem **8 commits rastreáveis**, um por sprint, com tags de `sprint-01` a `sprint-08` e `versao-final`.

O Product Backlog tem **26 histórias de usuário** organizadas em 8 épicos, com story points e rastreabilidade para os 26 requisitos funcionais."

> _Mostrar no GitHub: commits + tags + backlog-produto.md_

---

### 1:30 – 2:30 — Decisões Técnicas: Projeto + Padrões + Arquitetura

> **Quem fala:** Vitor (PO) ou desenvolvedor

"O sistema foi decomposto em **9 módulos** com os 5 princípios SOLID aplicados. Por exemplo:

- **SRP:** `RideService` orquestra caronas; pontos e notificações são responsabilidade de listeners
- **OCP:** novos canais de notificação implementam `NotificationChannel` sem tocar no `NotificationService`
- **DIP:** `RideService` depende de `RideMatcherInterface`, não de implementação concreta

Aplicamos **6 padrões GoF:**
- **Observer** — `RideCompleted` → listeners independentes
- **Strategy** — `BoundingBoxMatcher` via `RideMatcherInterface`
- **State** — ciclo de vida da carona com transições protegidas
- **Adapter** — Google Maps isolado atrás de interfaces internas
- **Factory Method** — `RideFactory` para rotas fixas e caronas sob demanda
- **Facade** — `NotificationService` multi-canal"

> _Mostrar: docs/padroes/padroes-de-projeto.md — diagrama do State_

---

### 2:30 – 3:15 — Arquitetura e Infraestrutura

> **Quem fala:** Lucas ou Gabriel

"A arquitetura é **em camadas** com **orientação a eventos**. São 4 camadas:
Apresentação (Blade + Livewire) → Aplicação (Controllers) → Domínio (Services + Models + Events) → Infraestrutura (Adapters + Jobs + Cache)

O deployment usa **Docker Compose com 6 serviços**: nginx, php-fpm, MySQL, Redis, Reverb (WebSocket) e queue-worker.

O ERD tem **10 tabelas**. O fluxo de carona sob demanda passa por Redis Queue e WebSocket para notificação em tempo real."

> _Mostrar: docs/arquitetura/arquitetura.md — diagrama de contêineres e ERD_

---

### 3:15 – 4:15 — Testes e Qualidade

> **Quem fala:** Maria Luiza

"Planejamos **37 casos de teste** com rastreabilidade para todos os 26 RFs. Implementamos **31 testes em Pest** em 3 categorias:

- **Unitários:** validam States (transições inválidas lançam exceção), BoundingBoxMatcher e PointService
- **Feature:** autenticação OAuth, ciclo da carona, Policies de autorização
- **Integração:** Events e Listeners desacoplados

Durante os testes encontramos e corrigimos **3 bugs reais**, incluindo uma inconsistência no `PointService` que não usava transação de banco.

O CI roda automaticamente no GitHub Actions a cada push."

> _Mostrar: tests/Unit/States/PendingStateTest.php + evidencias-testes.md_

---

### 4:15 – 5:00 — Uso de IA e Considerações Finais

> **Quem fala:** qualquer integrante

"Utilizamos IA como ferramenta de apoio para:
- Geração inicial dos templates de diagramas Mermaid
- Scaffolding dos arquivos de código seguindo os padrões definidos pela equipe
- Revisão de consistência entre artefatos de sprints diferentes

Toda decisão de projeto, escolha de padrão e justificativa técnica foi **revisada e validada pela equipe**. Os padrões GoF, a arquitetura em camadas e a decomposição SOLID foram escolhas conscientes, não geradas automaticamente.

O VaiJunto é um sistema tecnicamente coerente do problema à infraestrutura, documentado em 8 sprints com rastreabilidade completa."

---

## Artefatos para Mostrar na Apresentação

| Momento | O que mostrar | Localização |
|---|---|---|
| Scrum | Commits + tags no terminal / GitHub | `git log --oneline` |
| Backlog | Tabela de USs com épicos e SP | `docs/backlog-produto.md` |
| Modelagem | Diagrama de classes ou sequência | `docs/modelagem/modelagem.md` |
| Padrões | Diagrama State + snippet PendingState | `docs/padroes/padroes-de-projeto.md` |
| Arquitetura | Contêineres Docker + ERD | `docs/arquitetura/arquitetura.md` |
| Testes | PendingStateTest + saída esperada | `tests/Unit/States/` + `docs/testes/evidencias-testes.md` |

---

## Checklist pré-apresentação

- [ ] Repositório com push feito para o GitHub remoto
- [ ] Tags `sprint-01` a `sprint-08` e `versao-final` visíveis no GitHub
- [ ] Issues criadas no GitHub Projects para o backlog
- [ ] Mermaid renderizando corretamente no GitHub (testar nos docs)
- [ ] Slides ou tela do repositório preparados
- [ ] Cada integrante sabe qual parte vai apresentar
