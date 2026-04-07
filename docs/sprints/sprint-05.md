# Sprint 05

## 1. Identificação

- **Número da sprint:** 05
- **Período:** 03/05/2026 – 09/05/2026
- **Data da entrega:** 09/05/2026

---

## 2. Objetivo da Sprint

Identificar problemas recorrentes de projeto na solução VaiJunto, selecionar padrões GoF pertinentes e justificar tecnicamente sua adoção, com representação de como cada padrão se encaixa no código.

---

## 3. Itens do Sprint Backlog

| Item | Responsável | Status |
|---|---|---|
| Identificar oportunidades de padrões no sistema | Equipe | Concluído |
| Documentar Observer (Events/Listeners) | Gabriel | Concluído |
| Documentar Strategy (RideMatcherInterface) | Lucas | Concluído |
| Documentar State (ciclo de vida de Ride) | Maria Luiza | Concluído |
| Documentar Adapter (Google Maps) | Vitor (PO) | Concluído |
| Documentar Factory Method (criação de Ride) | Gabriel | Concluído |
| Documentar Facade (NotificationService) | Maria Luiza | Concluído |
| Atualizar diagrama de classes com novos elementos | Lucas | Concluído |
| Atualizar `docs/padroes/padroes-de-projeto.md` | Rafaella (SM) | Concluído |
| Redigir `docs/sprints/sprint-05.md` | Rafaella (SM) | Concluído |

---

## 4. Relação com o Conteúdo da Disciplina

Esta sprint corresponde ao conteúdo de **Padrões de Projeto (Design Patterns)**, cobrindo:

- **Padrões comportamentais:** Observer, Strategy, State — como objetos interagem e distribuem responsabilidades
- **Padrões estruturais:** Adapter, Facade — como compor classes e objetos para formar estruturas maiores
- **Padrões criacionais:** Factory Method — como instanciar objetos de forma flexível
- **Relação com SOLID:** cada padrão aplicado reforça princípios documentados na Sprint 4 (OCP, DIP, SRP)
- **Justificativa técnica:** cada padrão documenta o problema que resolve, a alternativa sem o padrão e os benefícios esperados

---

## 5. Artefatos Produzidos

| Artefato | Localização |
|---|---|
| Observer — Events/Listeners para RideCompleted | `docs/padroes/padroes-de-projeto.md` — Seção 1 |
| Strategy — RideMatcherInterface + BoundingBoxMatcher | `docs/padroes/padroes-de-projeto.md` — Seção 2 |
| State — ciclo de vida de Ride com transições controladas | `docs/padroes/padroes-de-projeto.md` — Seção 3 |
| Adapter — GoogleGeocodingAdapter / GoogleDirectionsAdapter | `docs/padroes/padroes-de-projeto.md` — Seção 4 |
| Factory Method — RideFactory | `docs/padroes/padroes-de-projeto.md` — Seção 5 |
| Facade — NotificationService multi-canal | `docs/padroes/padroes-de-projeto.md` — Seção 6 |
| Diagrama de classes atualizado com interfaces e factories | `docs/padroes/padroes-de-projeto.md` — Seção 7 |

---

## 6. Evidências no GitHub

- **Arquivos criados/atualizados:** `docs/padroes/padroes-de-projeto.md`, `docs/sprints/sprint-05.md`
- **Commits relevantes:** _a ser preenchido após push_
- **Tag da sprint:** `sprint-05`

---

## 7. Evolução da Aplicação Web

Os padrões definidos nesta sprint estabelecem os contratos de código (`interfaces`, `factories`, `events`) que guiarão a implementação nas sprints seguintes. Nenhum código de produção foi escrito ainda, mas os snippets de implementação documentados servem como especificação técnica.

**Impacto direto na implementação:**
- `RideMatcherInterface` → binding no `AppServiceProvider` já está especificado
- `RideFactory` → método por tipo de carona já está documentado
- `NotificationService` + `NotificationChannel[]` → arquitetura de canais pronta para código
- `PendingState`, `AcceptedState`, `InProgressState` → estados do `Ride` já modelados

---

## 8. Dificuldades Encontradas

- Escolha entre State e simples validação de transições no Model — o State foi preferido pela clareza e segurança que oferece
- Definir o nível de complexidade adequado para o Adapter do Google Maps: wrapper simples vs. adapter completo com tipos internos (`Coordinates`, `Route`)
- Identificar onde o Facade agregava valor real vs. onde seria over-engineering

---

## 9. Revisão do Incremento

**O que foi concluído:**
- 6 padrões GoF documentados (2 comportamentais + 1 comportamental + 2 estruturais + 1 criacional)
- Cada padrão com: problema, diagrama Mermaid, snippet de implementação e justificativa
- Diagrama de classes atualizado com interfaces e factories introduzidas pelos padrões
- Coerência mantida com os princípios SOLID da Sprint 4

**O que ficou pendente:**
- Implementação real dos padrões em código — será feita nas Sprints 6 e 7
- Avaliação de padrões adicionais (Command para filas de jobs) — adiado para Sprint 6

---

## 10. Pendências para a Próxima Sprint

- Definir arquitetura geral da aplicação (camadas, componentes, deployment)
- Criar diagrama de arquitetura com Mermaid
- Documentar decisões de arquitetura com foco em qualidade: escalabilidade, manutenibilidade, segurança
- Atualizar `docs/arquitetura/arquitetura.md`

---

## 11. Quadro Kanban (Sprint 5)

| A Fazer | Em Progresso | Concluído |
|---|---|---|
| Push para GitHub remoto | | Observer documentado |
| Command (filas de jobs) | | Strategy documentado |
| | | State documentado |
| | | Adapter documentado |
| | | Factory Method documentado |
| | | Facade documentado |
| | | Diagrama de classes atualizado |
| | | sprint-05.md |
