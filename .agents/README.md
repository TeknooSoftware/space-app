# .agents/ Directory

Multi-agent coordination hub for the Space project.

```
.agents/
├── README.md              # This file
├── EXAMPLES.md            # Code examples (Extension, States, Plan, Step)
├── conventions.md         # Coding & data conventions (Loader/Writer, DTOs, Queries)
├── recipes.md             # Recipe system (Bowls, Plans, Steps, registration)
├── security.md            # Security & access control (Voters, JWT, MFA)
├── testing.md             # Testing conventions (PHPUnit, Behat, traits)
├── api.md                 # API structure (routes, JSON templates, auth flow)
├── messenger.md           # Messenger workers & Mercure
├── forms.md               # Symfony forms (types, data mappers, templates)
├── feedback/
│   ├── INDEX.md           # Read at every session start — central knowledge base
│   ├── README.md          # Feedback system quick reference
│   └── *.md               # Individual feedback reports (versioned)
└── tasks/                 # Session-specific task tracking (gitignored)
    ├── todo.md            # Optional: current task checkboxes
    └── lessons.md         # Optional: project-specific quick reference
```

**Extension Directives**: Enabled extensions may have their own
`appliance/extensions/*/.agents/*.md` files. These extend or refine the
coordination system for extension-specific workflows.

## File Roles

| File                | Required                | Read When                 |
|---------------------|-------------------------|---------------------------|
| `EXAMPLES.md`       | Yes                     | Writing code              |
| `conventions.md`    | Yes                     | Working with domain layer |
| `recipes.md`        | Yes                     | Creating Plans/Steps      |
| `security.md`       | Yes                     | Authorization code        |
| `testing.md`        | Yes                     | Writing tests             |
| `api.md`            | Yes                     | API/controller code       |
| `messenger.md`      | Yes                     | Worker/messaging code     |
| `forms.md`          | Yes                     | Form type code            |
| `feedback/INDEX.md` | Yes                     | Every session start       |
| `feedback/*.md`     | Yes (write after tasks) | As needed                 |
| `tasks/todo.md`     | No                      | Complex multi-step tasks  |
| `tasks/lessons.md`  | No                      | If exists — session start |

## Navigation

- **New session**: read [../AGENTS.md](../AGENTS.md) → read `appliance/extensions/*/AGENTS.md`
  (if any) → read [feedback/INDEX.md](feedback/INDEX.md)
- **Writing code**: refer to [EXAMPLES.md](EXAMPLES.md)
- **Working with domain layer**: [conventions.md](conventions.md)
- **Creating Plans/Steps**: [recipes.md](recipes.md)
- **Authorization/Security**: [security.md](security.md)
- **Writing tests**: [testing.md](testing.md)
- **API/Controllers**: [api.md](api.md)
- **Workers/Messaging**: [messenger.md](messenger.md)
- **Forms**: [forms.md](forms.md)
- **After task**: create `feedback/YYYY-MM-DD-task-name.md` → update [feedback/INDEX.md](feedback/INDEX.md)
