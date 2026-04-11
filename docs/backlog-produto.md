# Product Backlog — VaiJunto

> Atualizado em: Sprint 8 (30/05/2026)
> Formato: `[ID] Como <perfil>, quero <ação> para <benefício>`

---

## Legenda

| Label | Descrição |
|---|---|
| `prio: high` | Essencial para o MVP |
| `prio: medium` | Importante, pode aguardar 1–2 sprints |
| `prio: low` | Desejável, entra quando houver capacidade |

| Símbolo | Significado |
|---|---|
| RF-XX | Requisito funcional relacionado |
| SP | Story Points (estimativa de esforço) |

---

## Épico 1 — Autenticação e Perfil

| ID | História de Usuário | RF | Prioridade | SP | Sprint |
|---|---|---|---|---|---|
| US-01 | Como membro da UFLA, quero me autenticar com meu email institucional para garantir que apenas a comunidade acesse o sistema | RF-01 | `prio: high` | 5 | 2 |
| US-02 | Como usuário, quero criar e editar meu perfil (nome, foto, contato) para que outros membros me identifiquem | RF-02 | `prio: high` | 3 | 2 |
| US-03 | Como usuário, quero configurar se sou motorista, passageiro ou ambos para que o sistema me ofereça as funcionalidades corretas | RF-03 | `prio: high` | 2 | 2 |
| US-04 | Como motorista, quero cadastrar meu veículo (modelo, placa, vagas) para que os passageiros saibam onde vão | RF-04 | `prio: high` | 3 | 2 |

### Critérios de Aceitação — Épico 1

**US-01**
- [ ] Usuário com email `@ufla.br` ou `@estudante.ufla.br` consegue se autenticar via Google OAuth
- [ ] Usuário com email de outro domínio recebe mensagem de erro clara
- [ ] Sessão é mantida por no mínimo 8 horas
- [ ] Logout encerra a sessão imediatamente

**US-02**
- [ ] Usuário pode atualizar nome, foto de perfil e telefone de contato
- [ ] Foto aceita formatos JPG e PNG (máx. 2MB)
- [ ] Campos obrigatórios validados antes de salvar

**US-03**
- [ ] Usuário pode selecionar papel: "Passageiro", "Motorista" ou "Ambos"
- [ ] Interface adapta o menu de acordo com o papel selecionado
- [ ] Papel pode ser alterado a qualquer momento nas configurações

**US-04**
- [ ] Motorista pode cadastrar veículo com: modelo, ano, cor, placa e vagas disponíveis (1–7)
- [ ] Placa validada no formato padrão brasileiro (AAA-0000 ou AAA0A00)
- [ ] Motorista pode editar ou remover o veículo cadastrado
- [ ] Não é possível publicar rotas sem veículo cadastrado

---

## Épico 2 — Rotas Fixas Recorrentes

| ID | História de Usuário | RF | Prioridade | SP | Sprint |
|---|---|---|---|---|---|
| US-05 | Como motorista, quero publicar uma rota fixa (origem, destino, horário, dias da semana) para que passageiros regulares possam se inscrever | RF-05 | `prio: high` | 8 | 3 |
| US-06 | Como passageiro, quero buscar rotas fixas disponíveis por trajeto e horário para encontrar uma carona recorrente | RF-06 | `prio: high` | 5 | 3 |
| US-07 | Como passageiro, quero me inscrever em uma rota fixa para garantir minha vaga regularmente | RF-07 | `prio: high` | 5 | 3 |
| US-08 | Como motorista, quero aprovar ou recusar inscrições na minha rota para controlar quem viaja comigo | RF-08 | `prio: medium` | 3 | 4 |
| US-09 | Como motorista, quero cancelar ou pausar uma rota fixa quando não puder dirigir | RF-09 | `prio: medium` | 3 | 4 |

---

## Épico 3 — Caronas sob Demanda

| ID | História de Usuário | RF | Prioridade | SP | Sprint |
|---|---|---|---|---|---|
| US-10 | Como passageiro, quero solicitar uma carona imediata informando origem, destino e horário para chegar ao campus quando precisar | RF-10 | `prio: high` | 8 | 3 |
| US-11 | Como motorista, quero receber notificações de solicitações de carona compatíveis com minha rota | RF-11 | `prio: high` | 5 | 3 |
| US-12 | Como motorista, quero aceitar ou recusar uma solicitação de carona sob demanda | RF-12 | `prio: high` | 3 | 3 |
| US-13 | Como passageiro, quero acompanhar o status da minha solicitação (pendente, aceita, a caminho, concluída) | RF-13 | `prio: high` | 5 | 4 |
| US-14 | Como usuário, quero cancelar uma carona aceita com registro de justificativa | RF-14 | `prio: medium` | 3 | 4 |

---

## Épico 4 — Notificações em Tempo Real

| ID | História de Usuário | RF | Prioridade | SP | Sprint |
|---|---|---|---|---|---|
| US-15 | Como usuário, quero receber notificações em tempo real sobre o status das minhas caronas | RF-18 | `prio: medium` | 8 | 5 |
| US-16 | Como motorista, quero ser notificado quando um passageiro cancelar uma vaga confirmada | RF-19 | `prio: medium` | 3 | 5 |

---

## Épico 5 — Avaliações

| ID | História de Usuário | RF | Prioridade | SP | Sprint |
|---|---|---|---|---|---|
| US-17 | Como passageiro, quero avaliar o motorista após a carona (1–5 estrelas + comentário) para ajudar outros passageiros | RF-15 | `prio: medium` | 5 | 5 |
| US-18 | Como motorista, quero avaliar o passageiro após a carona para manter a qualidade da comunidade | RF-16 | `prio: medium` | 3 | 5 |
| US-19 | Como usuário, quero visualizar a avaliação média de motoristas e passageiros nos perfis | RF-17 | `prio: medium` | 2 | 5 |

---

## Épico 6 — Gamificação

| ID | História de Usuário | RF | Prioridade | SP | Sprint |
|---|---|---|---|---|---|
| US-20 | Como motorista, quero acumular pontos a cada carona concluída para ter acesso a benefícios | RF-20 | `prio: low` | 5 | 6 |
| US-21 | Como motorista, quero visualizar meu saldo de pontos e histórico de conquistas | RF-21 | `prio: low` | 3 | 6 |

---

## Épico 7 — Mapas e Localização

| ID | História de Usuário | RF | Prioridade | SP | Sprint |
|---|---|---|---|---|---|
| US-23 | Como usuário, quero visualizar no mapa a rota da carona antes de confirmar | RF-23 | `prio: medium` | 8 | 6 |
| US-24 | Como passageiro, quero definir ponto de embarque e desembarque no mapa | RF-24 | `prio: medium` | 5 | 6 |

---

## Resumo por Sprint

| Sprint | Itens | Story Points |
|---|---|---|
| 2 | US-01, US-02, US-03, US-04 | 13 |
| 3 | US-05, US-06, US-07, US-10, US-11, US-12 | 34 |
| 4 | US-08, US-09, US-13, US-14 | 14 |
| 5 | US-15, US-16, US-17, US-18, US-19 | 21 |
| 6 | US-20, US-21, US-23, US-24 | 21 |
| 8 | Consolidação e testes | — |
