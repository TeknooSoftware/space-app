# Space — Documentation

Deep-dive documentation for Teknoo Space. Start here; the root [README.md](../README.md) is the product
overview, and [AGENTS.md](../AGENTS.md) is the standards reference for AI agents.

## Reading order

Follow this order the first time — each document assumes the previous ones.

| # | Document                               | What it answers                                                                                                                                                  |
|---|----------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1 | [requirements.md](requirements.md)     | What hardware, PHP version, extensions, services, ports and certificates Space needs before anything is installed.                                               |
| 2 | [installation.md](installation.md)     | How to install Space, either with the shipped Docker Compose stacks or by hand on a Debian or RedHat server, and what to do once it runs.                        |
| 3 | [configuration.md](configuration.md)   | The complete environment variable reference, and — just as important — **which process reads what**: the web app and the four workers do not share the same set. |
| 4 | [architecture.md](architecture.md)     | The shape of the application: hexagonal layering, the Recipe pattern, Loader/Writer, PHP-DI, and how an extension is mounted.                                    |
| 5 | [domain.md](domain.md)                 | The business model: accounts, users, projects, jobs, environments, clusters; DTOs, query objects, business and validation rules, voters.                         |
| 6 | [infrastructure.md](infrastructure.md) | The adapters: Doctrine ODM, the Kubernetes client, the Docker Compose / Ansible driver, Symfony integration, Twig, message transports.                           |
| 7 | [worker.md](worker.md)                 | The four asynchronous workers — what each consumes, how to run and supervise them, and how Mercure reports progress to the browser.                              |
| 8 | [api.md](api.md)                       | The REST API: authentication, the full endpoint list, response envelope, status codes and worked examples.                                                       |
| 9 | [development.md](development.md)       | Working on Space itself: dev setup, coding standards, the Recipe patterns in practice, tests, QA, writing an extension, debugging.                               |

## Looking something up

- **An environment variable** → [configuration.md](configuration.md)
- **An HTTP endpoint** → [api.md](api.md)
- **Why a job is stuck** → [worker.md](worker.md), then [infrastructure.md](infrastructure.md)
- **Where a class belongs** → [architecture.md](architecture.md), then [domain.md](domain.md)
- **How to add a Plan or a Step** → [development.md](development.md) and [../.agents/recipes.md](../.agents/recipes.md)

## Beyond this directory

- [../AGENTS.md](../AGENTS.md) — standards every contributor and AI agent follows
- [../.agents/](../.agents/) — the agent coordination hub: short references and the feedback log
- [../CONTRIBUTING.md](../CONTRIBUTING.md) — how to send a change
- [../CHANGELOG.md](../CHANGELOG.md) and [../DOCKER_COMPOSE_CHANGELOG.md](../DOCKER_COMPOSE_CHANGELOG.md) — release
  history
- `appliance/extensions/*/AGENTS.md` and the `documentation/` directory beside it — **an enabled extension
  documents itself there**. This directory stays about the appliance and never describes an extension's
  features.
