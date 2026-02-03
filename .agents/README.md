# .agents/ Directory

This directory contains documentation and coordination files for AI agents working on the Space project.

---

## Purpose

This directory serves as a **multi-agent coordination hub**, providing:

- Shared documentation and code examples
- Feedback and lessons learned from past tasks
- Session-specific task tracking (not versioned)

---

## Directory Structure

```
.agents/
├── README.md              # This file - overview of the .agents/ system
├── EXAMPLES.md            # Code examples referenced from AGENTS.md
├── feedback/              # Feedback reports from agent sessions
│   ├── INDEX.md          # Central index of all feedback entries
│   ├── README.md         # Feedback system documentation
│   └── *.md              # Individual feedback reports (versioned)
└── tasks/                 # Session-specific task tracking (not versioned)
    ├── .gitkeep          # Keeps directory in git
    ├── todo.md           # Optional: Current task tracking
    └── lessons.md        # Optional: Project-specific quick reference
```

---

## File Roles

### Documentation Files (Required, Versioned)

| File                   | Purpose                                                   | Read When           |
|------------------------|-----------------------------------------------------------|---------------------|
| **EXAMPLES.md**        | Detailed code examples for Space patterns and conventions | Writing code        |
| **feedback/INDEX.md**  | Index of all feedback entries with common patterns        | Every session start |
| **feedback/README.md** | Explains the feedback system workflow                     | First time only     |
| **feedback/*.md**      | Individual task feedback reports                          | As needed           |

### Task Files (Optional, Not Versioned)

| File                 | Purpose                                     | When to Use               |
|----------------------|---------------------------------------------|---------------------------|
| **tasks/todo.md**    | Track current session tasks with checkboxes | Complex multi-step tasks  |
| **tasks/lessons.md** | Quick project-specific reference notes      | Project-specific patterns |

---

## Quick Navigation

### For New Sessions

1. Read [../AGENTS.md](../AGENTS.md) - Universal standards for all agents
2. Read [feedback/INDEX.md](feedback/INDEX.md) - Learn from past challenges
3. Check [tasks/lessons.md](tasks/lessons.md) if it exists - Project-specific notes

### When Writing Code

- Refer to [EXAMPLES.md](EXAMPLES.md) for code patterns
- Follow standards in [../AGENTS.md](../AGENTS.md)

### After Completing Tasks

- Create feedback report in `feedback/YYYY-MM-DD-task-name.md`
- Update [feedback/INDEX.md](feedback/INDEX.md)
- See [../AGENTS.md](../AGENTS.md#feedback-loop) for format

---

## File Status Summary

| Status            | Files                                           |
|-------------------|-------------------------------------------------|
| **Required**      | EXAMPLES.md, feedback/INDEX.md, feedback/*.md   |
| **Optional**      | tasks/todo.md, tasks/lessons.md                 |
| **Versioned**     | All except tasks/*.md (gitignored)              |
| **Read at Start** | feedback/INDEX.md, tasks/lessons.md (if exists) |

---

## Why This Structure?

- **Separation of Concerns**: Persistent knowledge (feedback) vs. ephemeral tracking (tasks)
- **Multi-Agent Friendly**: All agents can learn from feedback regardless of which agent created it
- **Git-Friendly**: Session-specific files don't pollute git history
- **Scalable**: Easy to add new feedback entries without conflicts

---

## Related Documentation

- [../CLAUDE.md](../CLAUDE.md) - Claude Code specific guidance
- [../AGENTS.md](../AGENTS.md) - Universal standards for all AI agents

---

**Always read [../AGENTS.md](../AGENTS.md) first - it contains the complete project documentation.**
