# Messenger Workers & Mercure

Thin reference for the async worker architecture. **See `documentation/` for full details.**

## Worker Types

Four independent Symfony Messenger workers, each consuming from its own queue:

| Worker                 | Queue          | Purpose                                                                                                                                                 |
|------------------------|----------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| **NewTaskHandler**     | `new_task`     | Run any `NewTaskInterface`: initialize new deployment jobs, apply account provisioning tasks (registry/environment install or reinstall, quota refresh) |
| **RunJobHandler**      | `execute_job`  | Build and deploy (clone, compile, build images, transcribe)                                                                                             |
| **HistorySentHandler** | `history_sent` | Persist deployment history events                                                                                                                       |
| **JobDoneHandler**     | `job_done`     | Finalize completed jobs                                                                                                                                 |

`NewTaskHandler`, `RunJobHandler` and `HistorySentHandler` live in
`infrastructures/Symfony/Messenger/Handler/`. **`JobDoneHandler` is not a Space class** — it comes from East PaaS
(`vendor/teknoo/east-paas/infrastructures/Symfony/Components/Messenger/Handler/Psr11/JobDoneHandler.php`), so
grepping the appliance for it finds nothing.

An enabled extension may declare transports of its own, with their own handlers and their own DSN variable.
Read that extension's `AGENTS.md` for the list and for the consumers to run.

→ `documentation/worker.md#worker-types`

## Message Flow

```
User creates job → NewTaskInterface → new_task queue
  → NewTaskHandler → RunJob → execute_job queue
    → RunJobHandler → HistorySent → history_sent queue
    → RunJobHandler → JobDone → job_done queue
      → HistorySentHandler persists events
      → JobDoneHandler finalizes job status
```

Account provisioning (account created, environment added, admin reinstall / quota refresh):

```
HTTP → PrepareAccountTask → CallNewTask → AddTaskToHistory ("queued" line) → redirect/render
  → new_task queue → NewTaskHandler → AccountProvisioningTask (loads Account, history, clusters, wallet,
    registry) → ProvisioningPlanBowl (K8s or Docker Compose plan) → AccountHistory updated
```

→ `documentation/worker.md#message-flow`

## Transports & DSNs

Declared in `config/packages/messenger.yaml` (`new_task`, `max_retries: 3`) and
`config/packages/east_paas_messenger.yaml` (`execute_job` with `max_retries: 0`, `history_sent`, `job_done`).
DSNs are parameters in `config/parameters.yaml`, fed by the `MESSENGER_*_DSN` env vars and defaulting to
`in-memory://` — RabbitMQ is what a real deployment points them at, not what the defaults use.

**`when@test` overrides every transport to `test://`.** Anything that asserts on dispatched messages depends on
that; do not assume a real broker in Behat or PHPUnit.

`SPACE_NEW_TASK_WAITING_TIME` (`config/services.yaml`) sets how long the web request waits for the `new_task`
worker before falling back to the dashboard.

## Mercure Publishers

Two publishers in `infrastructures/Symfony/Mercure/` broadcast real-time updates via SSE: **TaskUrlPublisher**
(triggers browser redirect after job completion) and **TaskErrorPublisher** (error notifications). Clients
subscribe via the Mercure JS library with JWT auth.

The protocol version spoken by the hub comes from the `MERCURE_PROTOCOL_VERSION` env var (`0.x` by default,
`1.0` supported), read at container compilation in `config/di.variables.php` because MercureBundle resolves
it there — hence a warmup after a change, and the same value on every PHP process.

→ `documentation/worker.md#mercure-real-time-updates` · `documentation/architecture.md#9-mercure-publishers`

## Liveness Pinging

Workers and web app use liveness pinging to detect frozen processes: **PingFile** writes a timestamp to
`SPACE_PING_FILE` at intervals of `SPACE_PING_SECONDS`. **PingScheduler** schedules periodic writes.
**LivenessSubscriber** writes on each HTTP request.

→ `documentation/architecture.md#8-liveness-pinging` · `documentation/worker.md#health-checks`

## Running Workers

```bash
bin/console messenger:consume new_task | execute_job | history_sent | job_done
```

→ `documentation/worker.md#running-workers`

## Extensions

An extension declares its transports and handlers from its own bundle, and documents them in its own
`AGENTS.md`. Nothing about a given extension belongs in this file.
