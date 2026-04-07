# Sprint 04

## 1. Identificação

- **Número da sprint:** 04
- **Período:** 26/04/2026 – 02/05/2026
- **Data da entrega:** 02/05/2026

---

## 2. Objetivo da Sprint

Definir e justificar as decisões de projeto com base em princípios de qualidade de software, decompor a solução em módulos coesos com baixo acoplamento e documentar as principais escolhas técnicas com análise de alternativas.

---

## 3. Itens do Sprint Backlog

| Item | Responsável | Status |
|---|---|---|
| Decomposição em 9 módulos com responsabilidades | Vitor (PO) + Lucas | Concluído |
| Aplicação e documentação dos princípios SOLID | Gabriel + Maria Luiza | Concluído |
| Decisão 1: papéis de usuário (enum vs. tabelas) | Vitor (PO) | Concluído |
| Decisão 2: algoritmo de matching | Lucas | Concluído |
| Decisão 3: WebSocket vs. polling | Gabriel | Concluído |
| Decisão 4: Service Layer vs. Fat Controllers | Maria Luiza | Concluído |
| Decisão 5: Livewire vs. SPA | Equipe | Concluído |
| Diagrama de módulos e dependências | Rafaella (SM) | Concluído |
| Estrutura de pastas do código-fonte | Vitor (PO) | Concluído |
| Atualizar `docs/projeto/decisoes-de-projeto.md` | Equipe | Concluído |
| Redigir `docs/sprints/sprint-04.md` | Rafaella (SM) | Concluído |

---

## 4. Relação com o Conteúdo da Disciplina

Esta sprint corresponde ao conteúdo de **Princípios de Projeto**, abrangendo:

- **SRP (Single Responsibility):** cada Service Class possui uma única razão para mudar
- **OCP (Open/Closed):** `NotificationChannel` como interface extensível sem modificação
- **LSP (Liskov Substitution):** `FixedRide` e `DemandRide` substituem `Ride` sem quebrar comportamento
- **ISP (Interface Segregation):** interfaces separadas `PassengerActions` e `DriverActions`
- **DIP (Dependency Inversion):** `RideService` depende de `RideMatcherInterface`, não de implementação concreta
- **Alta coesão e baixo acoplamento:** módulos comunicam-se via eventos Laravel, sem chamadas diretas entre módulos
- **Justificativa de alternativas:** cada decisão documenta a alternativa rejeitada e o motivo da escolha

---

## 5. Artefatos Produzidos

| Artefato | Localização |
|---|---|
| Decomposição em 9 módulos | `docs/projeto/decisoes-de-projeto.md` — Seção 1 |
| Aplicação dos 5 princípios SOLID + coesão/acoplamento | `docs/projeto/decisoes-de-projeto.md` — Seção 2 |
| 5 decisões de projeto com análise de alternativas | `docs/projeto/decisoes-de-projeto.md` — Seção 3 |
| Diagrama de módulos e dependências (Mermaid) | `docs/projeto/decisoes-de-projeto.md` — Seção 4 |
| Estrutura de pastas do código-fonte | `docs/projeto/decisoes-de-projeto.md` — Seção 5 |

---

## 6. Evidências no GitHub

- **Arquivos criados/atualizados:** `docs/projeto/decisoes-de-projeto.md`, `docs/sprints/sprint-04.md`
- **Commits relevantes:** _a ser preenchido após push_
- **Tag da sprint:** `sprint-04`

---

## 7. Evolução da Aplicação Web

A estrutura de pastas definida nesta sprint (`app/Services/`, `app/Contracts/`, `app/Events/`, `app/Listeners/`) será o esqueleto do código Laravel implementado a partir da Sprint 6. As decisões tomadas aqui reduzem o retrabalho nas próximas sprints.

Decisões com maior impacto na implementação:
- **Service Layer** → controllers serão criados enxutos desde o início
- **Eventos + Listeners** → Gamificação e Notificações desacopladas do `RideService`
- **Livewire** → sem necessidade de API REST, componentes server-side reativos

---

## 8. Dificuldades Encontradas

- Aplicar DIP no contexto do Laravel sem tornar o código excessivamente abstrato para um MVP
- Equilibrar o nível de detalhamento das decisões: suficiente para justificar tecnicamente, sem ser exaustivo
- Definição da estrutura de pastas com antecedência (antes do código existir) exigiu visão do sistema como um todo

---

## 9. Revisão do Incremento

**O que foi concluído:**
- 9 módulos definidos com responsabilidades claras
- 5 princípios SOLID documentados com exemplos concretos do VaiJunto
- 5 decisões de projeto com análise de alternativas e justificativas técnicas
- Diagrama de dependências entre módulos (sem ciclos)
- Estrutura completa de pastas para guiar a implementação

**O que ficou pendente:**
- Identificar oportunidades de Design Patterns (GoF) sobre esta estrutura — foco da Sprint 5
- Diagrama de estados do ciclo de vida de `Ride` — movido para Sprint 5

---

## 10. Pendências para a Próxima Sprint

- Identificar padrões de projeto (GoF) aplicáveis a: Notifications, Matching, Ride lifecycle, Map integration
- Justificar tecnicamente a escolha de cada padrão
- Atualizar diagramas de classes com os padrões adotados
- Documentar em `docs/padroes/padroes-de-projeto.md`

---

## 11. Quadro Kanban (Sprint 4)

| A Fazer | Em Progresso | Concluído |
|---|---|---|
| Push para GitHub remoto | | Decomposição em módulos |
| Diagrama de estados de Ride | | Princípios SOLID documentados |
| | | Decisão 1: papéis de usuário |
| | | Decisão 2: algoritmo de matching |
| | | Decisão 3: WebSocket vs. polling |
| | | Decisão 4: Service Layer |
| | | Decisão 5: Livewire vs. SPA |
| | | Diagrama de módulos |
| | | Estrutura de pastas |
| | | sprint-04.md |
