# Sprint 06

## 1. Identificação

- **Número da sprint:** 06
- **Período:** 10/05/2026 – 16/05/2026
- **Data da entrega:** 16/05/2026

---

## 2. Objetivo da Sprint

Definir a arquitetura do VaiJunto, explicitando componentes, responsabilidades, comunicação entre camadas, organização do deployment e justificativa das escolhas com foco em qualidade.

---

## 3. Itens do Sprint Backlog

| Item | Responsável | Status |
|---|---|---|
| Diagrama de Contexto C4 nível 1 | Vitor (Tech Lead) | Concluído |
| Diagrama de Contêineres C4 nível 2 (Docker Compose) | Lucas | Concluído |
| Diagrama de Camadas (Layered Architecture) | Gabriel | Concluído |
| Diagrama de Componentes do módulo Ride (C4 nível 3) | Maria Luiza | Concluído |
| Fluxo de dados end-to-end (sequência com filas) | Lucas | Concluído |
| Esquema do banco de dados (ERD) | Gabriel | Concluído |
| Justificativas das escolhas arquiteturais | Vitor (Tech Lead) | Concluído |
| Atributos de qualidade mapeados à arquitetura | Rafaella (Dev Front) | Concluído |
| Atualizar `docs/arquitetura/arquitetura.md` | Equipe | Concluído |
| Redigir `docs/sprints/sprint-06.md` | Rafaella (Dev Front) | Concluído |

---

## 4. Relação com o Conteúdo da Disciplina

Esta sprint corresponde ao conteúdo de **Arquitetura de Software**, cobrindo:

- **Estilos arquiteturais:** Layered Architecture como base + Event-Driven para comunicação assíncrona
- **Modelo C4:** diagramas de contexto (nível 1), contêineres (nível 2) e componentes (nível 3)
- **Deployment:** infraestrutura Docker com nginx, php-fpm, MySQL, Redis, Reverb e queue-worker
- **Qualidade arquitetural:** segurança, desempenho, escalabilidade, manutenibilidade e portabilidade documentados
- **Coerência:** arquitetura justificada com referência direta às decisões (Sprint 4) e padrões (Sprint 5)

---

## 5. Artefatos Produzidos

| Artefato | Localização |
|---|---|
| Diagrama de Contexto C4 (nível 1) | `docs/arquitetura/arquitetura.md` — Seção 2 |
| Diagrama de Contêineres Docker (nível 2) | `docs/arquitetura/arquitetura.md` — Seção 3 |
| Responsabilidades dos 6 contêineres | `docs/arquitetura/arquitetura.md` — Seção 3 |
| Diagrama de Camadas (Apresentação → Infraestrutura) | `docs/arquitetura/arquitetura.md` — Seção 4 |
| Diagrama de Componentes — módulo Ride | `docs/arquitetura/arquitetura.md` — Seção 5 |
| Fluxo de dados end-to-end com filas Redis | `docs/arquitetura/arquitetura.md` — Seção 6 |
| Esquema ERD completo (10 tabelas) | `docs/arquitetura/arquitetura.md` — Seção 7 |
| Justificativas arquiteturais (5 decisões) | `docs/arquitetura/arquitetura.md` — Seção 8 |
| Atributos de qualidade (6 atributos) | `docs/arquitetura/arquitetura.md` — Seção 9 |

---

## 6. Evidências no GitHub

- **Arquivos criados/atualizados:** `docs/arquitetura/arquitetura.md`, `docs/sprints/sprint-06.md`
- **Commits relevantes:** _a ser preenchido após push_
- **Tag da sprint:** `sprint-06`

---

## 7. Evolução da Aplicação Web

A arquitetura definida nesta sprint completa a especificação técnica do VaiJunto. O sistema está documentado do problema (Sprint 1) aos contratos de código (Sprint 5) e à infraestrutura completa.

**O que a arquitetura viabiliza:**
- **ERD completo** (10 tabelas) é a especificação das migrations Laravel
- **Docker Compose** com 6 serviços é o ambiente de desenvolvimento e produção
- **Diagrama de camadas** guia a organização de cada arquivo do projeto
- **Fluxo de dados** especifica a integração entre fila, WebSocket e banco

---

## 8. Dificuldades Encontradas

- Decidir o nível de detalhe adequado para os diagramas C4 sem torná-los excessivamente complexos
- Modelar o ERD considerando os dois tipos de carona (fixed e demand) numa tabela `rides` com FKs opcionais
- Justificar a escolha de Redis para três papéis distintos (cache, fila, sessão) de forma clara e técnica

---

## 9. Revisão do Incremento

**O que foi concluído:**
- Arquitetura completa documentada: 9 seções, 7 diagramas Mermaid
- Modelo C4 (contexto + contêineres + componentes)
- Infraestrutura Docker com 6 serviços especificada
- ERD com 10 tabelas e todos os relacionamentos
- 5 justificativas arquiteturais com análise de alternativas
- 6 atributos de qualidade mapeados a estratégias concretas

**O que ficou pendente:**
- `docker-compose.yml` com a infraestrutura real — a ser criado na Sprint 8
- Configuração de ambiente (`.env.example`) — Sprint 8

---

## 10. Pendências para a Próxima Sprint

- Elaborar plano de testes cobrindo: unitários (Services), integração (Controllers + DB), aceitação (fluxos end-to-end)
- Definir casos de teste por módulo com rastreabilidade para os RFs
- Criar matriz de rastreabilidade requisitos × testes
- Documentar em `docs/testes/plano-de-testes.md`

---

## 11. Quadro Kanban (Sprint 6)

| A Fazer | Em Progresso | Concluído |
|---|---|---|
| Push para GitHub remoto | | Diagrama de Contexto C4 |
| docker-compose.yml (Sprint 8) | | Diagrama de Contêineres |
| .env.example (Sprint 8) | | Diagrama de Camadas |
| | | Diagrama de Componentes Ride |
| | | Fluxo de dados end-to-end |
| | | ERD completo |
| | | Justificativas arquiteturais |
| | | Atributos de qualidade |
| | | sprint-06.md |
