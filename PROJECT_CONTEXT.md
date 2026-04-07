# PROJECT_CONTEXT — VaiJunto

## Identificação

- **Projeto:** VaiJunto — Caronas Solidárias para a Comunidade UFLA
- **Disciplina:** GCC188 — Engenharia de Software
- **Turma:** 14A | Semestre: 2026/1
- **Docentes:** Prof. Paulo Afonso Parreira Junior / Prof. Johnatan Oliveira
- **Valor total:** 40 pontos

## Equipe

| Membro | Papel sugerido |
|---|---|
| Gabriel Francisco Borges dos Santos | Desenvolvedor |
| Lucas Silva Meira | Desenvolvedor |
| Maria Luiza Santos Ferreira | Desenvolvedora |
| Rafaella Maciel Pereira Leite | Scrum Master |
| Vitor Moreira dos Santos | Product Owner |

## Problema e Proposta

Membros da comunidade UFLA (Lavras/MG e São Sebastião do Paraíso/MG) não possuem solução centralizada, gratuita e segura para compartilhamento de caronas. As alternativas atuais (WhatsApp, Uber/99, transporte público) são caras, desorganizadas ou com horários escassos.

**Solução:** Plataforma web responsiva que conecta quem tem vaga no carro com quem precisa de carona, de forma gratuita, com autenticação institucional UFLA e sistema de gamificação por pontos para incentivar motoristas.

### Modelo Híbrido

- **Rotas fixas recorrentes:** motoristas publicam trajetos regulares
- **Caronas sob demanda:** passageiros solicitam em tempo real

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 11 (PHP 8.3) |
| Frontend | Blade + Livewire + Tailwind CSS |
| Banco de Dados | MySQL 8.0 |
| Mapas | Google Maps API (Places, Directions, Geocoding) |
| Autenticação | OAuth 2.0 com email institucional UFLA |
| Tempo Real | Laravel Reverb (WebSockets) |
| Versionamento | Git + GitHub |
| Deploy | Docker + Docker Compose |

## Metodologia

**Scrum simplificado** com sprints semanais/quinzenais. Artefatos obrigatórios:

- **Product Backlog:** lista priorizada de funcionalidades (GitHub Issues + Labels + Milestones)
- **Sprint Backlog:** itens selecionados por sprint
- **Incrementos:** evidências concretas de evolução a cada sprint
- **Registro de sprint:** planejamento, execução, dificuldades e revisão
- **Evidências de reuniões:** prints de Google Meet ou fotos de reuniões presenciais no GitHub

### Papéis no GitHub

- Issues = itens do backlog
- Labels = prioridade (`prio: high`, `prio: low`) e categoria (`feature`, `bug`, `debt`)
- Milestones = sprints
- GitHub Projects = quadro Kanban

## Cronograma de Sprints

| Sprint | Data | Foco | Pontos |
|---|---|---|---|
| 1 | 04/04/2026 | Definição do problema, visão do produto, Scrum, GitHub e backlog inicial | 4,0 |
| 2 | 11/04/2026 | Requisitos, refinamento do backlog e definição da aplicação web | 4,0 |
| 3 | 25/04/2026 | Modelagem do sistema e vínculo entre requisitos e modelos | 4,0 |
| 4 | 02/05/2026 | Decisões de projeto, decomposição e justificativas técnicas | 4,0 |
| 5 | 09/05/2026 | Aplicação justificada de padrões de projeto | 4,0 |
| 6 | 16/05/2026 | Definição da arquitetura e organização estrutural da aplicação | 4,0 |
| 7 | 23/05/2026 | Planejamento e documentação dos testes | 4,0 |
| 8 | 30/05/2026 | Consolidação, evidências finais e revisão dos incrementos | 4,0 |
| Apresentação Final | 15/06/2026 | Exposição oral da solução, do processo e dos resultados | 8,0 |

## Estrutura Obrigatória do Repositório

```
/
├── README.md
├── docs/
│   ├── visao-geral.md
│   ├── backlog-produto.md
│   ├── criterios-avaliacao-interna.md
│   ├── arquitetura/
│   │   └── arquitetura.md
│   ├── modelagem/
│   │   └── modelagem.md
│   ├── projeto/
│   │   └── decisoes-de-projeto.md
│   ├── padroes/
│   │   └── padroes-de-projeto.md
│   ├── testes/
│   │   ├── plano-de-testes.md
│   │   └── evidencias-testes.md
│   └── sprints/
│       ├── sprint-01.md
│       ├── sprint-02.md
│       ├── sprint-03.md
│       ├── sprint-04.md
│       ├── sprint-05.md
│       ├── sprint-06.md
│       ├── sprint-07.md
│       └── sprint-08.md
├── src/
├── public/
├── tests/
├── rubrica/
│   └── autoavaliacao-entregas.md
└── .github/
    └── ISSUE_TEMPLATE/
```

## Template de Sprint (Apêndice B)

Cada `docs/sprints/sprint-0X.md` deve conter:

1. Identificação (número, período, data de entrega)
2. Objetivo da sprint
3. Itens do Sprint Backlog
4. Relação com o conteúdo da disciplina
5. Artefatos produzidos
6. Evidências no GitHub (arquivos, commits relevantes, tag `sprint-0X`)
7. Evolução da aplicação web
8. Dificuldades encontradas
9. Revisão do incremento (concluído / pendente)
10. Pendências para a próxima sprint

**Tags Git obrigatórias:** `sprint-01` ... `sprint-08` e `versao-final`

## Critérios de Avaliação

| Critério | Descrição |
|---|---|
| Organização e comprometimento | Cumprimento de prazos, regularidade das entregas, participação |
| Uso do Scrum | Product Backlog, Sprint Backlog, registros, revisões |
| Qualidade técnica | Consistência da proposta, coerência das decisões |
| Aplicação dos conteúdos | Requisitos, modelagem, projeto, padrões, arquitetura, testes |
| Qualidade da documentação | Redação, organização, completude, objetividade |
| Evolução incremental | Progressão evidenciada sprint a sprint |
| GitHub | Versionamento, histórico, rastreabilidade, organização |
| Aplicação web | Adequação ao problema, organização funcional, demonstração |
| Apresentação final | Clareza, domínio, demonstração da solução |

## Regras Críticas

- Repositório GitHub deve estar **atualizado ao longo do semestre** — não apenas ao final
- Cada integrante deve ter **commits verificáveis**
- A solução deve ser **obrigatoriamente uma aplicação web**
- Uso exagerado de IA sem curadoria pode resultar em **penalização ou nota zero**
- Entrega por sprint: enviar link do arquivo `docs/sprints/sprint-0X.md` no UFLA Virtual

## Princípios de Desenvolvimento

- **Clean Code:** nomes expressivos, funções pequenas e coesas, sem código morto
- **SOLID:** responsabilidade única, aberto/fechado, inversão de dependência
- **Scrum:** incrementos funcionais a cada sprint, backlog vivo e priorizado
- **Minimalismo:** apenas o necessário para o sprint em curso — sem over-engineering
