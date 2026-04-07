# Sprint 03

## 1. Identificação

- **Número da sprint:** 03
- **Período:** 12/04/2026 – 25/04/2026
- **Data da entrega:** 25/04/2026

---

## 2. Objetivo da Sprint

Representar a solução VaiJunto por meio de modelos UML que auxiliem a compreensão do sistema, evidenciem as decisões de estrutura e comportamento, e sirvam de base para as próximas decisões de projeto e arquitetura.

---

## 3. Itens do Sprint Backlog

| Item | Responsável | Status |
|---|---|---|
| Elaborar diagrama de casos de uso | Gabriel | Concluído |
| Elaborar diagrama de classes principal | Lucas + Maria Luiza | Concluído |
| Elaborar diagrama de sequência — autenticação OAuth | Vitor | Concluído |
| Elaborar diagrama de sequência — carona sob demanda | Lucas | Concluído |
| Elaborar diagrama de atividades — inscrição em rota fixa | Maria Luiza | Concluído |
| Vincular requisitos (RF) aos modelos produzidos | Rafaella (SM) | Concluído |
| Atualizar `docs/modelagem/modelagem.md` | Equipe | Concluído |
| Redigir `docs/sprints/sprint-03.md` | Rafaella (SM) | Concluído |

---

## 4. Relação com o Conteúdo da Disciplina

Esta sprint corresponde ao conteúdo de **Modelos** da disciplina, cobrindo:

- **Diagrama de Casos de Uso:** representação dos atores (Passageiro, Motorista, Administrador) e das interações com o sistema
- **Diagrama de Classes:** estrutura estática do sistema — entidades, atributos, métodos e associações
- **Diagrama de Sequência:** comportamento dinâmico nos fluxos críticos (autenticação e carona sob demanda)
- **Diagrama de Atividades:** fluxo de controle no processo de inscrição em rota fixa
- **Rastreabilidade:** vínculo explícito entre todos os RFs levantados na Sprint 2 e os modelos produzidos

---

## 5. Artefatos Produzidos

| Artefato | Localização |
|---|---|
| Diagrama de Casos de Uso (Mermaid) | `docs/modelagem/modelagem.md` — Seção 1 |
| Diagrama de Classes (Mermaid) | `docs/modelagem/modelagem.md` — Seção 2 |
| Diagrama de Sequência — Autenticação OAuth | `docs/modelagem/modelagem.md` — Seção 3 |
| Diagrama de Sequência — Carona sob Demanda | `docs/modelagem/modelagem.md` — Seção 4 |
| Diagrama de Atividades — Inscrição em Rota Fixa | `docs/modelagem/modelagem.md` — Seção 5 |
| Tabela de rastreabilidade RF × Modelos | `docs/modelagem/modelagem.md` — Seção 6 |

---

## 6. Evidências no GitHub

- **Arquivos criados/atualizados:** `docs/modelagem/modelagem.md`, `docs/sprints/sprint-03.md`
- **Commits relevantes:** _a ser preenchido após push_
- **Tag da sprint:** `sprint-03`

---

## 7. Evolução da Aplicação Web

Ainda sem código implementado. A modelagem desta sprint consolida o entendimento do sistema e viabiliza as próximas etapas:

- O diagrama de classes define as **10 entidades principais** do banco de dados
- Os diagramas de sequência revelam as **integrações críticas**: OAuth, WebSocket e persistência
- A rastreabilidade garante que todos os **26 RFs** têm representação nos modelos

---

## 8. Dificuldades Encontradas

- Definição do nível de detalhe adequado para o diagrama de classes (evitar over-engineering vs. deixar lacunas)
- Representação do fluxo WebSocket no diagrama de sequência — tecnologia nova para parte da equipe
- Decisão de usar Mermaid (renderiza no GitHub) em vez de ferramentas externas para manter tudo no repositório

---

## 9. Revisão do Incremento

**O que foi concluído:**
- 5 diagramas UML/Mermaid produzidos e publicados no repositório
- 10 classes identificadas com atributos, métodos e associações
- 4 atores e 17 casos de uso mapeados
- Rastreabilidade completa: todos os 26 RFs vinculados a ao menos um modelo
- Fluxos críticos documentados em sequência (OAuth + carona sob demanda)

**O que ficou pendente:**
- Diagrama de implantação (adiado para Sprint 6 — Arquitetura)
- Diagrama de estados para o ciclo de vida de `Ride` (avaliado para Sprint 4)

---

## 10. Pendências para a Próxima Sprint

- Definir decomposição modular da aplicação com base nas classes identificadas
- Documentar decisões de projeto (coesão, acoplamento, responsabilidade)
- Analisar alternativas para o matching entre passageiros e motoristas
- Justificar uso de SOLID no contexto do Laravel

---

## 11. Quadro Kanban (Sprint 3)

| A Fazer | Em Progresso | Concluído |
|---|---|---|
| Push para GitHub remoto | | Casos de uso |
| Diagrama de estados de Ride | | Diagrama de classes |
| | | Sequência: autenticação |
| | | Sequência: carona sob demanda |
| | | Atividades: inscrição rota fixa |
| | | Rastreabilidade RF × modelos |
| | | sprint-03.md |
