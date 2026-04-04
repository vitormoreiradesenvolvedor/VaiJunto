# Visão Geral do Produto — VaiJunto

## Declaração de Visão

Para **membros da comunidade UFLA** (estudantes, professores e servidores) que **precisam de deslocamento acessível entre residência e campus**, o **VaiJunto** é uma **plataforma web de caronas gratuitas** que **conecta quem tem vaga no carro com quem precisa de carona**, com autenticação institucional e incentivo por pontos. Ao contrário de **grupos de WhatsApp desorganizados ou aplicativos pagos como Uber e 99**, o VaiJunto é **gratuito, seguro e exclusivo para a comunidade UFLA**.

---

## Problema

| Aspecto | Descrição |
|---|---|
| O problema | Falta de solução centralizada, gratuita e segura para compartilhamento de caronas |
| Afeta | Estudantes, professores e servidores da UFLA |
| Impacto | Custos elevados de transporte, dificuldade de acesso ao campus, veículos subutilizados |
| Solução | Plataforma web que conecta motoristas e passageiros da comunidade UFLA |

---

## Objetivos do Produto

1. Conectar motoristas e passageiros da comunidade UFLA de forma gratuita e segura
2. Oferecer modelo híbrido: rotas fixas recorrentes e caronas sob demanda
3. Incentivar motoristas com sistema de gamificação por pontos
4. Garantir segurança com autenticação exclusiva via email institucional UFLA
5. Reduzir o número de veículos em circulação, promovendo sustentabilidade

---

## Escopo Inicial da Aplicação Web

### Incluído no escopo

- Cadastro e autenticação via email institucional UFLA (OAuth 2.0)
- Perfil de usuário (passageiro e/ou motorista)
- Publicação de rotas fixas recorrentes pelos motoristas
- Solicitação de caronas sob demanda pelos passageiros
- Matching entre passageiro e motorista disponível
- Acompanhamento de status da carona
- Sistema de avaliação (passageiro avalia motorista e vice-versa)
- Sistema de pontos para motoristas
- Notificações em tempo real (Laravel Reverb)
- Integração com Google Maps (visualização de rotas)

### Fora do escopo (versão inicial)

- Pagamentos ou cobranças de qualquer natureza
- Usuários externos à comunidade UFLA
- Aplicativo mobile nativo
- Integração com transporte público

---

## Contexto e Restrições

- **Área de cobertura:** Lavras/MG e São Sebastião do Paraíso/MG
- **Acesso restrito:** apenas membros com email `@ufla.br` ou `@estudante.ufla.br`
- **Gratuidade total:** nenhuma funcionalidade de cobrança ou monetização
- **Plataforma:** aplicação web responsiva (mobile-first)
