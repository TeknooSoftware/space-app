# Feedback Index

Central knowledge base from AI agent sessions. **Read at every session start.**
After each task: create `YYYY-MM-DD-task-name.md` here, then add an entry below.

---

## Recent Feedback Entries

### 2026-07-01 — [Ansible + Docker Compose deployment](2026-07-01-ansible-docker.md)

**Status**: ✅ Resolved — 22-step pack complete; docker-compose target added, Kubernetes byte-for-byte
unchanged (full Behat exit 0). 🔄 A few DC-only follow-ups deferred + documented (registry/reinstall/refresh
dispatch, render-agnostic endpoint, out-of-band playbook run).

### 2026-02-02 — [Example Task](2026-02-02-example-task.md)

**Status**: ✅ Resolved — Created feedback system (index, templates, workflow integration).

---

## Common Patterns

### Documentation Gaps

- **Rename-tool blast radius**: a spec that says "rename class X → Y" must warn that PhpStorm's
  `rename_refactoring` also rewrites the identifier in strings/comments/translations/Markdown. Revert textual
  over-renames after, or use FQCN-anchored targeted edits. (2026-07-01)

### Recurring Blockers

- **Build-time-fixed structures vs per-request data**: `RecipeBowl` is readonly and fixed at container
  compile; per-request variation (e.g. cluster `type`) needs a thin `BowlInterface` resolving the concrete
  plan at `execute()` time. (2026-07-01)
- **No target host / no container-integration harness**: Ansible playbooks and driver `require()` can only be
  syntax-checked / warmup-checked here; functional runs are out-of-band. This project is Behat-only. (2026-07-01)

### Frequently Missing Context

- **Two-repo layout**: `appliance/extensions/Enterprise/*` is git-ignored in space-app and mounts a separate
  repo (`space-app-enterprise`). Enterprise code commits there; plan-doc Findings commit to space-app. Check
  `git status` in **both** repos. (2026-07-01)
- **Field-name reuse for parity**: keeping `masterAddress` / reusing `client_key` + `ca_cert` let a new
  deployment target ride the existing persistence + deploy-identity path with no schema change. (2026-07-01)

---

## Legend

- ⚠️ Needs attention — not yet addressed
- ✅ Resolved — fixed, docs updated
- 📝 Documented — added to AGENTS.md or other docs
- 🔄 In Progress — currently being addressed

## Adding New Entries

1. Create `YYYY-MM-DD-task-name.md` in this directory
2. Add entry at top of "Recent Feedback Entries": `### YYYY-MM-DD — [Task Name](file.md) **Status**: icon`
3. Update "Common Patterns" if applicable

File sections: Task Summary · Missing Precision · Blockers · Suggestions · Lessons Learned.
See [AGENTS.md](../../AGENTS.md#task-management--feedback-loop) for full format.
