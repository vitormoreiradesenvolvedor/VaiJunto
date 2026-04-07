# VaiJunto — Caronas Solidárias para a Comunidade UFLA

<p align="center">
  <strong>Plataforma web de caronas gratuitas para estudantes, professores e servidores da UFLA</strong><br>
  Conectando a comunidade universitária em Lavras e São Sebastião do Paraíso
</p>

---

## Sobre o Projeto

O **VaiJunto** é uma aplicação web responsiva que conecta membros da comunidade UFLA que precisam de carona com aqueles que podem oferecê-la, de forma **totalmente gratuita**.

O sistema implementa um **modelo híbrido**:
- **Rotas fixas recorrentes:** motoristas publicam trajetos regulares
- **Caronas sob demanda:** passageiros solicitam caronas em tempo real

Para incentivar a participação de motoristas, o VaiJunto conta com um **sistema de gamificação por pontos** com benefícios reais.

## Problema

Membros da UFLA enfrentam dificuldades diárias de deslocamento. As alternativas existentes são caras (Uber/99), desorganizadas (grupos de WhatsApp) ou com horários escassos (transporte público). Não existe solução centralizada, gratuita e segura para compartilhamento de caronas na comunidade.

## Público-Alvo

| Perfil | Descrição |
|---|---|
| Estudantes | Graduação e pós-graduação da UFLA |
| Professores | Docentes de todos os departamentos |
| Servidores | Técnicos administrativos e terceirizados com vínculo UFLA |

**Área de cobertura:** Lavras/MG e São Sebastião do Paraíso/MG

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 11 (PHP 8.3) |
| Frontend | Blade + Livewire + Tailwind CSS |
| Banco de Dados | MySQL 8.0 |
| Mapas | Google Maps API |
| Autenticação | OAuth 2.0 com email institucional UFLA |
| Tempo Real | Laravel Reverb (WebSockets) |
| Deploy | Docker + Docker Compose |

## Equipe

| Membro | Papel |
|---|---|
| Gabriel Francisco Borges dos Santos | Desenvolvedor |
| Lucas Silva Meira | Desenvolvedor |
| Maria Luiza Santos Ferreira | Desenvolvedora |
| Rafaella Maciel Pereira Leite | Scrum Master |
| Vitor Moreira dos Santos | Product Owner |

## Cronograma de Sprints

| Sprint | Data | Foco |
|---|---|---|
| 1 | 04/04/2026 | Definição do problema, visão do produto e organização inicial |
| 2 | 11/04/2026 | Levantamento e priorização de requisitos |
| 3 | 25/04/2026 | Modelagem do sistema |
| 4 | 02/05/2026 | Princípios de projeto e decisões de solução |
| 5 | 09/05/2026 | Aplicação de padrões de projeto |
| 6 | 16/05/2026 | Definição da arquitetura de software |
| 7 | 23/05/2026 | Planejamento e documentação de testes |
| 8 | 30/05/2026 | Consolidação, revisão e evidências finais |
| Final | 15/06/2026 | Apresentação final |

## Como Executar

> Instruções detalhadas serão adicionadas na Sprint 6 (Arquitetura).

```bash
git clone https://github.com/seu-grupo/vaijunto.git
cd vaijunto
```

## Disciplina

Projeto acadêmico desenvolvido para a disciplina **GCC188 — Engenharia de Software**
Universidade Federal de Lavras (UFLA) — Semestre 2026/1
Docentes: Prof. Paulo Afonso Parreira Junior / Prof. Johnatan Oliveira
