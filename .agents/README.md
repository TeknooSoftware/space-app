# .agents/ Directory

Multi-agent coordination hub for the Space project.

```
.agents/
├── README.md              # This file
├── EXAMPLES.md            # Code examples (Extension, States, Plan, Step)
├── feedback/
│   ├── INDEX.md           # Read at every session start — central knowledge base
│   ├── README.md          # Feedback system quick reference
│   └── *.md               # Individual feedback reports (versioned)
└── tasks/                 # Session-specific task tracking (gitignored)
    ├── todo.md            # Optional: current task checkboxes
    └── lessons.md         # Optional: project-specific quick reference
```

## File Roles

| File                | Required                | Read When                 |
|---------------------|-------------------------|---------------------------|
| `EXAMPLES.md`       | Yes                     | Writing code              |
| `feedback/INDEX.md` | Yes                     | Every session start       |
| `feedback/*.md`     | Yes (write after tasks) | As needed                 |
| `tasks/todo.md`     | No                      | Complex multi-step tasks  |
| `tasks/lessons.md`  | No                      | If exists — session start |

## Navigation

- **New session**: read [../AGENTS.md](../AGENTS.md) → read [feedback/INDEX.md](feedback/INDEX.md)
- **Writing code**: refer to [EXAMPLES.md](EXAMPLES.md)
- **After task**: create `feedback/YYYY-MM-DD-task-name.md` → update [feedback/INDEX.md](feedback/INDEX.md)
