# CLAUDE.md

This file provides specific guidance to **Claude Code** (claude.ai/code) when working in this
repository.

---

## Multi-Agent Environment

**Important**: This project may have multiple AI agents working on it (Claude Code, Cursor,
GitHub Copilot, etc.). To ensure good cohesion and avoid conflicts:

### Standard Documentation

**All agents must follow the standards defined in [AGENTS.md](AGENTS.md)**

The AGENTS.md file contains:

- Project architecture and structure
- Code standards and conventions
- Development guidelines
- Common commands and workflows
- Key concepts and patterns

### Claude Code Specifics

As Claude Code, you have unique capabilities that other agents may not have. Use them effectively:

#### Your Strengths

1. **Autonomous Execution**
    - You can read files, run commands, and make changes directly
    - You can execute tests and verify your work
    - You can interact with git, npm, composer, etc.

2. **Context Management**
    - You have access to the full codebase
    - You can use subagents for parallel exploration
    - You maintain conversation history for context

3. **Verification Capabilities**
    - You can run PHPStan, PHPCS, tests
    - You can build and verify Docker images
    - You can check git status and diffs

#### Your Responsibilities

1. **Read First**
    - **ALWAYS** read [AGENTS.md](AGENTS.md) at the start of a session
    - **ALWAYS** read `.agents/feedback/INDEX.md` to learn from past challenges
    - Check if other agents have left notes in `.agents/` directory

2. **Communicate**
    - Leave feedback in `.agents/feedback/` after completing tasks
    - Update `.agents/feedback/INDEX.md` with new entries
    - Document any issues or missing context you encounter

3. **Respect Standards**
    - Follow the code standards in AGENTS.md
    - Use the workflow orchestration guidelines
    - Maintain the feedback loop

4. **Verify Your Work**
    - Run tests before marking tasks complete
    - Run PHPStan and PHPCS to verify code quality
    - Check that your changes don't break existing functionality

---

## Quick Start Checklist

Every time you start a new session:

- [ ] Read [AGENTS.md](AGENTS.md) for project standards
- [ ] Read `.agents/feedback/INDEX.md` for past challenges
- [ ] Check `.agents/tasks/lessons.md` for project-specific lessons (if exists)
- [ ] Understand the current task requirements
- [ ] Plan your approach (use plan mode for non-trivial tasks)
- [ ] Execute with verification
- [ ] Document feedback in `.agents/feedback/`

---

## Working with Other Agents

If you notice work done by other agents:

- **Respect their changes** - Don't randomly revert or refactor
- **Build on their work** - Understand what they did and why
- **Report issues** - If you find problems, document them in feedback
- **Stay consistent** - Follow the same patterns and conventions

---

## .agents/ Directory Structure

The `.agents/` directory is your coordination hub. **Start here**: [.agents/README.md](.agents/README.md)

### Quick Reference

| File/Directory                                 | Purpose                                | Status   | When to Use            |
|------------------------------------------------|----------------------------------------|----------|------------------------|
| **[.agents/README.md](.agents/README.md)**     | Overview of the .agents/ system        | Required | First visit            |
| **[.agents/EXAMPLES.md](.agents/EXAMPLES.md)** | Code examples and patterns             | Required | Writing code           |
| **[.agents/feedback/](.agents/feedback/)**     | Feedback reports directory             | Required | Every session          |
| `.agents/feedback/INDEX.md`                    | Index of all feedback entries          | Required | Every session start    |
| `.agents/feedback/*.md`                        | Individual task feedback reports       | Required | After completing tasks |
| **[.agents/tasks/](.agents/tasks/)**           | Session-specific tracking (gitignored) | Optional | Complex tasks          |
| `.agents/tasks/todo.md`                        | Current task tracking                  | Optional | Multi-step tasks       |
| `.agents/tasks/lessons.md`                     | Project-specific quick reference       | Optional | Project patterns       |

### Navigation Flow

```
Start → CLAUDE.md (you are here)
  ↓
Read → AGENTS.md (universal standards)
  ↓
Check → .agents/feedback/INDEX.md (learn from past)
  ↓
Optionally → .agents/tasks/lessons.md (project notes)
  ↓
During work → .agents/EXAMPLES.md (code patterns)
  ↓
After task → .agents/feedback/ (document learnings)
```

## Key Files for Multi-Agent Coordination

| File        | Purpose                                  | Status   |
|-------------|------------------------------------------|----------|
| `AGENTS.md` | Universal standards for all agents       | Required |
| `.agents/`  | Multi-agent coordination hub (see above) | Required |

---

## Your First Action

**→ Read [AGENTS.md](AGENTS.md) now to understand the full project context.**

This file (CLAUDE.md) is just a gateway. The real documentation is in AGENTS.md.
