# Messenger Workers & Mercure

Thin reference for the async worker architecture. **See `documentation/` for full details.**

## Worker Types

Four independent Symfony Messenger workers, each consuming from its own RabbitMQ queue:

| Worker | Queue | Purpose |
|--------|-------|---------|
| **NewTaskHandler** | `new_task` | Initialize new deployment jobs |
| **ExecuteJobHandler** | `execute_job` | Build and deploy (clone, compile, build images, transcribe) |
| **HistorySentHandler** | `history_sent` | Persist deployment history events |
| **JobDoneHandler** | `job_done` | Finalize completed jobs |

→ `documentation/worker.md#worker-types`

## Message Flow

```
User creates job → NewTaskInterface → new_task queue
  → NewTaskHandler → RunJob → execute_job queue
    → ExecuteJobHandler → HistorySent → history_sent queue
    → ExecuteJobHandler → JobDone → job_done queue
      → HistorySentHandler persists events
      → JobDoneHandler finalizes job status
```

→ `documentation/worker.md#message-flow`

## Mercure Publishers

Two publishers in `infrastructures/Symfony/Mercure/` broadcast real-time updates via SSE: **JobUrlPublisher**
(triggers browser redirect after job completion) and **TaskErrorPublisher** (error notifications). Clients
subscribe via the Mercure JS library with JWT auth.

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
## Enterprise Extension Reference

Enterprise may add custom message handlers or Mercure publishers. See
`documentation/architecture.md#extension-system`.
