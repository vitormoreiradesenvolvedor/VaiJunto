# Requisitos do Sistema — VaiJunto

> Atualizado em: Sprint 2 (11/04/2026)

---

## 1. Requisitos Funcionais

### Módulo: Autenticação e Perfil

| ID | Requisito | Prioridade |
|---|---|---|
| RF-01 | O sistema deve autenticar usuários exclusivamente via OAuth 2.0 com email institucional UFLA (`@ufla.br` ou `@estudante.ufla.br`) | Alta |
| RF-02 | O sistema deve permitir que o usuário crie e edite seu perfil (nome, foto, número de contato) | Alta |
| RF-03 | O sistema deve permitir que o usuário configure seu papel: passageiro, motorista ou ambos | Alta |
| RF-04 | O sistema deve permitir que motoristas cadastrem seu veículo (modelo, placa, cor, número de vagas disponíveis) | Alta |

### Módulo: Rotas Fixas Recorrentes

| ID | Requisito | Prioridade |
|---|---|---|
| RF-05 | O sistema deve permitir que motoristas publiquem rotas fixas com origem, destino, horário de saída e dias da semana | Alta |
| RF-06 | O sistema deve permitir que passageiros busquem rotas fixas disponíveis por trajeto e horário | Alta |
| RF-07 | O sistema deve permitir que passageiros se inscrevam em uma rota fixa, ocupando uma das vagas disponíveis | Alta |
| RF-08 | O sistema deve permitir que motoristas aprovem ou recusem inscrições de passageiros em suas rotas | Média |
| RF-09 | O sistema deve permitir que motoristas cancelem ou pausem temporariamente uma rota fixa | Média |

### Módulo: Caronas sob Demanda

| ID | Requisito | Prioridade |
|---|---|---|
| RF-10 | O sistema deve permitir que passageiros solicitem uma carona informando origem, destino, horário desejado e número de vagas | Alta |
| RF-11 | O sistema deve notificar motoristas compatíveis sobre novas solicitações de carona sob demanda | Alta |
| RF-12 | O sistema deve permitir que motoristas aceitem ou recusem solicitações de carona sob demanda | Alta |
| RF-13 | O sistema deve exibir o status da carona ao passageiro: pendente, aceita, a caminho, concluída ou cancelada | Alta |
| RF-14 | O sistema deve permitir que passageiro ou motorista cancele uma carona aceita, com registro da justificativa | Média |

### Módulo: Avaliações

| ID | Requisito | Prioridade |
|---|---|---|
| RF-15 | O sistema deve permitir que o passageiro avalie o motorista (1–5 estrelas e comentário opcional) após a conclusão da carona | Média |
| RF-16 | O sistema deve permitir que o motorista avalie o passageiro (1–5 estrelas) após a conclusão da carona | Média |
| RF-17 | O sistema deve exibir a avaliação média de cada usuário em seu perfil público | Média |

### Módulo: Notificações em Tempo Real

| ID | Requisito | Prioridade |
|---|---|---|
| RF-18 | O sistema deve enviar notificações em tempo real via WebSocket sobre mudanças de status de caronas | Média |
| RF-19 | O sistema deve notificar motorista quando um passageiro cancelar uma vaga confirmada | Média |

### Módulo: Gamificação

| ID | Requisito | Prioridade |
|---|---|---|
| RF-20 | O sistema deve creditar pontos ao motorista a cada carona concluída com avaliação positiva | Baixa |
| RF-21 | O sistema deve exibir o saldo de pontos e o histórico de conquistas do motorista em seu perfil | Baixa |

### Módulo: Mapas

| ID | Requisito | Prioridade |
|---|---|---|
| RF-23 | O sistema deve integrar o Google Maps para exibição visual de rotas (origem → destino) | Média |
| RF-24 | O sistema deve permitir que passageiro defina ponto de embarque e desembarque via mapa interativo | Média |

---

## 2. Requisitos Não Funcionais

| ID | Requisito | Categoria |
|---|---|---|
| RNF-01 | O sistema deve estar disponível 99% do tempo durante o período letivo da UFLA | Disponibilidade |
| RNF-02 | O acesso deve ser restrito exclusivamente a usuários com email institucional UFLA válido | Segurança |
| RNF-03 | As senhas e tokens OAuth não devem ser armazenados em texto claro | Segurança |
| RNF-04 | As páginas principais devem carregar em menos de 2 segundos em conexão de banda larga | Desempenho |
| RNF-05 | O sistema deve suportar pelo menos 500 usuários simultâneos sem degradação perceptível | Escalabilidade |
| RNF-06 | A interface deve ser responsiva e funcional em dispositivos móveis (mobile-first) | Usabilidade |
| RNF-07 | O sistema deve ser compatível com as duas versões mais recentes dos principais navegadores (Chrome, Firefox, Safari, Edge) | Compatibilidade |
| RNF-08 | O código deve seguir as convenções do Laravel e princípios de Clean Code | Manutenibilidade |
| RNF-09 | O sistema deve registrar logs de erros para monitoramento e diagnóstico | Observabilidade |
| RNF-10 | A aplicação deve ser containerizada com Docker para garantir portabilidade de ambiente | Portabilidade |

---

## 3. Stakeholders

| Perfil | Interesses |
|---|---|
| Estudante (passageiro) | Encontrar carona gratuita com facilidade e segurança |
| Estudante (motorista) | Publicar trajetos e ser recompensado por ajudar colegas |
| Professor / Servidor | Acesso simples e rápido ao sistema de caronas |

---

## 4. Restrições

- A plataforma é gratuita; nenhuma funcionalidade de cobrança deve ser implementada
- O acesso é restrito à comunidade UFLA (validação por email)
- A área de cobertura inicial é Lavras/MG e São Sebastião do Paraíso/MG
- O sistema deve ser entregue como aplicação web (não mobile nativo)
