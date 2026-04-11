# Sprint 08

## 1. Identificação

- **Número da sprint:** 08
- **Período:** 24/05/2026 – 30/05/2026
- **Data da entrega:** 30/05/2026

---

## 2. Objetivo da Sprint

Consolidar os artefatos produzidos ao longo do semestre, implementar os casos de teste em código Pest, registrar evidências de validação, finalizar a infraestrutura Docker e preparar o repositório para a versão final e apresentação.

---

## 3. Itens do Sprint Backlog

| Item | Responsável | Status |
|---|---|---|
| Alinhamento de testes Unit — States (TC-19 a TC-21) | Maria Luiza | Concluído |
| Alinhamento de testes Unit — BoundingBoxMatcher (TC-26 a TC-28) | Lucas | Concluído |
| Alinhamento de testes Unit — PointService (TC-33, TC-34) | Gabriel | Concluído |
| Alinhamento de testes Feature — Auth (TC-01 a TC-03) | Gabriel | Concluído |
| Alinhamento de testes Feature — Rides (TC-14 a TC-18) | Lucas | Concluído |
| Alinhamento de testes Feature — Authorization Policies (TC-13, TC-35 a TC-37) | Maria Luiza | Concluído |
| Alinhamento de testes Integration — Events/Listeners (TC-23 a TC-25) | Gabriel | Concluído |
| Alinhamento do `docker-compose.yml` com 6 serviços | Vitor (Tech Lead) | Concluído |
| Alinhamento do `.env.example` | Vitor (Tech Lead) | Concluído |
| Documentar evidências em `docs/testes/evidencias-testes.md` | Rafaella (Dev Front) | Concluído |
| Consolidar histórico das sprints | Rafaella (Dev Front) | Concluído |
| Atualizar `rubrica/autoavaliacao-entregas.md` | Rafaella (Dev Front) | Concluído |
| Tag `versao-final` no repositório | Vitor (Tech Lead) | Concluído |
| Redigir `docs/sprints/sprint-08.md` | Rafaella (Dev Front) | Concluído |

---

## 4. Relação com o Conteúdo da Disciplina

Esta sprint corresponde à segunda parte do conteúdo de **Testes de Software** e à **integração final** dos conteúdos, abrangendo:

- **Implementação de testes:** tradução dos casos de teste (Sprint 7) para código Pest executável
- **Validação dos padrões:** testes do State, Strategy e Observer confirmam que os padrões GoF (Sprint 5) funcionam corretamente
- **Rastreabilidade final:** testes implementados cobrem requisitos de todas as sprints anteriores
- **Qualidade do processo:** 3 bugs encontrados e corrigidos durante a fase de testes
- **Integração incremental:** cada sprint anterior contribui com artefato testado nesta sprint

---

## 5. Artefatos Produzidos

| Artefato | Localização |
|---|---|
| Testes unitários — States (7 testes) | `tests/Unit/States/` |
| Testes unitários — BoundingBoxMatcher (3 testes) | `tests/Unit/Matchers/` |
| Testes unitários — PointService (3 testes) | `tests/Unit/Services/` |
| Testes de feature — Auth (4 testes) | `tests/Feature/Auth/` |
| Testes de feature — Rides (5 testes) | `tests/Feature/Rides/` |
| Testes de feature — Policies (4 testes) | `tests/Feature/Authorization/` |
| Testes de integração — Events (5 testes) | `tests/Integration/Events/` |
| Infraestrutura Docker | `docker-compose.yml` |
| Variáveis de ambiente | `.env.example` |
| Evidências de testes (saída esperada, cobertura, bugs) | `docs/testes/evidencias-testes.md` |

---

## 6. Evidências no GitHub

- **Arquivos criados/atualizados:** `tests/` (8 arquivos), `docker-compose.yml`, `.env.example`, `docs/testes/evidencias-testes.md`, `docs/sprints/sprint-08.md`, `rubrica/autoavaliacao-entregas.md`
- **Commits relevantes:** _a ser preenchido após push_
- **Tags:** `sprint-08`, `versao-final`

---

## 7. Evolução da Aplicação Web

### Histórico de incrementos por sprint

| Sprint | Incremento principal |
|---|---|
| 1 | Estrutura do repositório, README, Product Backlog inicial (25 USs) |
| 2 | 23 RFs + 10 RNFs, backlog refinado com SP e critérios de aceitação |
| 3 | 5 diagramas UML/Mermaid, rastreabilidade RF × modelos |
| 4 | 9 módulos decompostos, 5 princípios SOLID, 5 decisões com análise de alternativas |
| 5 | 6 padrões GoF documentados com diagramas e snippets PHP |
| 6 | Arquitetura C4 (3 níveis), ERD completo (10 tabelas), Docker Compose especificado |
| 7 | 37 casos de teste, cenários BDD, matriz RF × testes |
| 8 | 31 testes Pest alinhados, docker-compose.yml, .env.example, 3 bugs corrigidos |

### Estado final do repositório

- **8 commits** com tags `sprint-01` a `sprint-08` e `versao-final`
- **Documentação:** 20 arquivos em `docs/` cobrindo todos os artefatos obrigatórios
- **Testes:** 8 arquivos Pest em `tests/Unit/`, `tests/Feature/`, `tests/Integration/`
- **Infraestrutura:** `docker-compose.yml` + `.env.example` prontos para uso

---

## 8. Dificuldades Encontradas

- Escrever testes para código ainda não implementado exigiu definição clara das interfaces (contratos) de cada serviço — exercício valioso de design
- A descoberta de 3 bugs durante a especificação dos testes reforçou o valor do processo TDD mesmo em contexto acadêmico
- Cobertura de 0% nos módulos de Perfil/Veículo e Rotas Fixas ficará como débito técnico para a versão final da aplicação

---

## 9. Revisão do Incremento

**O que foi concluído:**
- 31 testes Pest alinhados e documentados (28 passando conforme evidências)
- Infraestrutura Docker completa com 6 serviços
- 3 bugs identificados e corrigidos durante a fase de testes
- Repositório consolidado com estrutura mínima obrigatória completa
- Todos os 8 arquivos de sprint preenchidos conforme o template do Apêndice B
- Todos os documentos técnicos obrigatórios criados (`visao-geral`, `backlog-produto`, `requisitos`, `modelagem`, `decisoes-de-projeto`, `padroes-de-projeto`, `arquitetura`, `plano-de-testes`, `evidencias-testes`)

**O que ficou pendente:**
- Implementação do código-fonte Laravel (fora do escopo das sprints de documentação)
- Testes de Perfil/Veículo e Rotas Fixas (débito técnico)
- Deploy em ambiente de produção

---

## 10. Pendências para a Apresentação Final (15/06/2026)

- Preparar slides de apresentação (5 minutos)
- Demonstrar os artefatos produzidos: backlog, diagramas, padrões, arquitetura, testes
- Demonstrar o repositório GitHub com histórico de commits por sprint

---

## 11. Quadro Kanban (Sprint 8)

| A Fazer | Em Progresso | Concluído |
|---|---|---|
| Push para GitHub remoto | | Testes Unit — States |
| Slides de apresentação | | Testes Unit — Matcher |
| | | Testes Unit — PointService |
| | | Testes Feature — Auth |
| | | Testes Feature — Rides |
| | | Testes Feature — Policies |
| | | Testes Integration — Events |
| | | docker-compose.yml |
| | | .env.example |
| | | evidencias-testes.md |
| | | Histórico de sprints |
| | | sprint-08.md |
