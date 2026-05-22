# Sprint 07

## 1. Identificação

- **Número da sprint:** 07
- **Período:** 17/05/2026 – 23/05/2026
- **Data da entrega:** 23/05/2026

---

## 2. Objetivo da Sprint

Planejar a estratégia de testes do VaiJunto e documentar os critérios de validação dos principais incrementos, com rastreabilidade completa entre requisitos funcionais e casos de teste.

---

## 3. Itens do Sprint Backlog

| Item | Responsável | Status |
|---|---|---|
| Definir objetivos e escopo dos testes | Rafaella (SM) | Concluído |
| Definir tipos de teste e ferramentas (Pest, Mockery, SQLite) | Lucas | Concluído |
| Elaborar casos de teste — Auth (TC-01 a TC-03) | Gabriel | Concluído |
| Elaborar casos de teste — Perfil e Veículo (TC-04 a TC-07) | Maria Luiza | Concluído |
| Elaborar casos de teste — Rotas Fixas (TC-08 a TC-13) | Gabriel | Concluído |
| Elaborar casos de teste — Caronas sob Demanda (TC-14 a TC-18) | Lucas | Concluído |
| Elaborar casos de teste — State Pattern (TC-19 a TC-22) | Maria Luiza | Concluído |
| Elaborar casos de teste — Eventos e Listeners (TC-23 a TC-25) | Gabriel | Concluído |
| Elaborar casos de teste — Matching (TC-26 a TC-28) | Lucas | Concluído |
| Elaborar casos de teste — Avaliações e Pontos (TC-29 a TC-34) | Maria Luiza | Concluído |
| Elaborar casos de teste — Segurança e Policies (TC-35 a TC-37) | Vitor (PO) | Concluído |
| Elaborar cenários de aceitação para US-01, US-10, US-12, US-16 | Rafaella (SM) | Concluído |
| Criar matriz de rastreabilidade RF × testes | Rafaella (SM) | Concluído |
| Definir estrutura de pastas dos testes | Lucas | Concluído |
| Definir critérios de aceitação da suíte | Equipe | Concluído |
| Redigir `docs/sprints/sprint-07.md` | Rafaella (SM) | Concluído |

---

## 4. Relação com o Conteúdo da Disciplina

Esta sprint corresponde à primeira parte do conteúdo de **Testes de Software**, abrangendo:

- **Planejamento de testes:** definição de objetivos, escopo, tipos e estratégia
- **Tipos de teste:** unitário, feature (integração HTTP), integração (eventos) e aceitação
- **Casos de teste:** especificação com ID, entrada e resultado esperado por módulo
- **Cenários de aceitação:** formato BDD (Dado/Quando/Então) para histórias de usuário críticas
- **Rastreabilidade:** matriz completa RF × casos de teste para todos os 26 RFs
- **Ferramentas:** Pest 3.x + Mockery + Laravel Factories + SQLite in-memory
- **Qualidade:** critérios mínimos de cobertura e tempo de execução definidos

---

## 5. Artefatos Produzidos

| Artefato | Localização |
|---|---|
| Objetivos e escopo dos testes | `docs/testes/plano-de-testes.md` — Seções 1 e 2 |
| Estratégia e tipos de teste | `docs/testes/plano-de-testes.md` — Seção 3 |
| 37 casos de teste em 9 módulos | `docs/testes/plano-de-testes.md` — Seção 4 |
| Cenários BDD para US-01, US-10, US-12, US-16 | `docs/testes/plano-de-testes.md` — Seção 5 |
| Matriz de rastreabilidade RF × testes | `docs/testes/plano-de-testes.md` — Seção 6 |
| Estrutura de pastas `tests/` | `docs/testes/plano-de-testes.md` — Seção 7 |
| Critérios de aceitação da suíte | `docs/testes/plano-de-testes.md` — Seção 8 |
| Ferramentas definidas | `docs/testes/plano-de-testes.md` — Seção 9 |

---

## 6. Evidências no GitHub

- **Arquivos criados/atualizados:** `docs/testes/plano-de-testes.md`, `docs/sprints/sprint-07.md`
- **Commits relevantes:** _a ser preenchido após push_
- **Tag da sprint:** `sprint-07`

---

## 7. Evolução da Aplicação Web

Os 37 casos de teste especificados nesta sprint funcionam como contrato de qualidade para a implementação. Nenhum teste foi executado ainda — a execução e as evidências são foco da Sprint 8.

**Destaques do plano:**
- **TC-19 a TC-22:** validam o State Pattern (Sprint 5) — garantem que transições inválidas lançam exceção
- **TC-23 a TC-25:** validam o Observer Pattern — Events/Listeners desacoplados
- **TC-26 a TC-28:** validam o Strategy Pattern — BoundingBoxMatcher isolado
- **TC-35 a TC-37:** validam as Laravel Policies — nenhuma escalação de privilégio possível

---

## 8. Dificuldades Encontradas

- Definir o nível de granularidade dos casos de teste: muito genérico perde valor; muito detalhado torna-se excessivo para o escopo acadêmico
- Cobrir o fluxo WebSocket em testes sem infraestrutura real — solução: mock do `NotificationService` nas Feature Tests
- Decidir entre Laravel Dusk (browser tests) e Pest Feature Tests — optou-se por Feature Tests pela menor complexidade de setup

---

## 9. Revisão do Incremento

**O que foi concluído:**
- 37 casos de teste especificados cobrindo todos os módulos críticos
- Cenários BDD para 4 histórias de usuário de alta prioridade
- Rastreabilidade para todos os 26 RFs (RF-01 a RF-21 + RNF-02, RNF-03)
- Estrutura de pastas `tests/Unit/`, `tests/Feature/`, `tests/Integration/` definida
- Critérios mínimos de cobertura (70% de Services e States) estabelecidos

**O que ficou pendente:**
- Implementação dos testes em código Pest — Sprint 8
- Execução da suíte e coleta de evidências — Sprint 8
- Configuração do GitHub Actions para CI — Sprint 8

---

## 10. Pendências para a Próxima Sprint

- Implementar os casos de teste prioritários em Pest (TC-01 a TC-22 no mínimo)
- Executar a suíte e documentar os resultados em `docs/testes/evidencias-testes.md`
- Consolidar toda a documentação do projeto
- Criar `docker-compose.yml` e `.env.example`
- Preparar repositório para a versão final (`versao-final` tag)
- Iniciar preparação da apresentação final

---

## 11. Quadro Kanban (Sprint 7)

| A Fazer | Em Progresso | Concluído |
|---|---|---|
| Push para GitHub remoto | | Casos de teste Auth |
| Implementar testes em Pest (Sprint 8) | | Casos de teste Perfil/Veículo |
| GitHub Actions CI (Sprint 8) | | Casos de teste Rotas Fixas |
| | | Casos de teste Caronas |
| | | Casos de teste State Pattern |
| | | Casos de teste Eventos |
| | | Casos de teste Matching |
| | | Casos de teste Avaliações/Pontos |
| | | Casos de teste Segurança |
| | | Cenários BDD |
| | | Matriz RF × testes |
| | | sprint-07.md |
