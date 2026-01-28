---
name: workflows:parallel
description: "Launch multiple agents in parallel with git worktree isolation"
argument_hint: <feature-id> [--roles=backend,frontend,qa] [--cleanup]
---

# Multi-Agent Workflow: Parallel

Launch multiple AI agents working in parallel, each in an isolated git worktree environment.

## Usage

```bash
# Launch with default roles (backend, frontend, qa)
/workflows:parallel user-authentication

# Launch specific roles
/workflows:parallel payment-system --roles=backend,frontend

# Launch only backend and qa
/workflows:parallel api-refactor --roles=backend,qa

# Cleanup after completion
/workflows:parallel user-authentication --cleanup
```

## What This Command Does

### 1. Creates Git Worktrees

For each role, creates an isolated working directory:

```
.worktrees/
├── backend/   (branch: feature/user-authentication-backend)
├── frontend/  (branch: feature/user-authentication-frontend)
└── qa/        (branch: feature/user-authentication-qa)
```

Each worktree:
- Has its own filesystem (no conflicts)
- Is on its own feature branch
- Can run its own dev server
- Shares git history with main repo

### 2. Allocates Ports

Each agent gets a dedicated port for dev servers:

| Role | Port |
|------|------|
| backend | 3001 |
| frontend | 3002 |
| qa | 3003 |

### 3. Launches tmux Session

Creates a tmux session with panes for each agent:

```
┌─────────────────────┬─────────────────────┐
│                     │                     │
│     BACKEND         │     FRONTEND        │
│     :3001           │     :3002           │
│                     │                     │
├─────────────────────┴─────────────────────┤
│                                           │
│                    QA                     │
│                   :3003                   │
│                                           │
└───────────────────────────────────────────┘
```

### 4. Sets Up Environment

Each pane has:
- `AGENT_ROLE` - Current role (backend/frontend/qa)
- `AGENT_PORT` - Allocated port
- `FEATURE_ID` - Feature being worked on
- Working directory set to worktree

## Output

```
╔════════════════════════════════════════════════════════════╗
║          PARALLEL SESSION CREATED                           ║
╚════════════════════════════════════════════════════════════╝

Session: workflow-user-authentication
Feature: user-authentication

Agents launched:
  [0] backend    @ .worktrees/backend                    :3001
  [1] frontend   @ .worktrees/frontend                   :3002
  [2] qa         @ .worktrees/qa                         :3003

Attach with:
  tmux attach -t workflow-user-authentication

Tmux shortcuts:
  Ctrl+b d       - Detach from session
  Ctrl+b arrow   - Navigate between panes
  Ctrl+b z       - Zoom current pane
```

## tmux Navigation

| Shortcut | Action |
|----------|--------|
| `Ctrl+b d` | Detach from session (keeps running) |
| `Ctrl+b arrow` | Move between panes |
| `Ctrl+b z` | Zoom/unzoom current pane |
| `Ctrl+b [` | Enter scroll mode (q to exit) |
| `Ctrl+b "` | Split pane horizontally |
| `Ctrl+b %` | Split pane vertically |

## Workflow Example

```bash
# 1. Plan the feature first
/workflows:plan user-profile

# 2. Launch parallel agents
/workflows:parallel user-profile --roles=backend,frontend

# 3. Attach to the session
tmux attach -t workflow-user-profile

# 4. Work in each pane
#    - Backend pane: implement API
#    - Frontend pane: implement UI

# 5. Each agent commits to its branch
#    Backend: feature/user-profile-backend
#    Frontend: feature/user-profile-frontend

# 6. When done, cleanup
/workflows:parallel user-profile --cleanup
```

## Benefits

1. **No Conflicts**: Each agent has isolated filesystem
2. **Independent Builds**: One agent can break build without affecting others
3. **Easy Review**: Each worktree has clean diff of one role's work
4. **Parallel Progress**: Backend and frontend work simultaneously
5. **Clear Boundaries**: Each role stays in its lane

## Implementation

This command executes:

```bash
PARALLEL_DIR=".ai/workflow/parallel"

source "${PARALLEL_DIR}/tmux_orchestrator.sh"

if [[ "$ARGUMENTS" == *"--cleanup"* ]]; then
    tmux_kill_session "workflow-${FEATURE_ID}"
else
    tmux_create_session "workflow-${FEATURE_ID}" "${ROLES}" "${FEATURE_ID}"
fi
```

## Cleanup

When development is complete:

```bash
# Cleanup session and worktrees
/workflows:parallel user-auth --cleanup

# This will:
# 1. Kill tmux session
# 2. Remove worktrees (if clean)
# 3. Release allocated ports
```

## Merging Work

After parallel development:

1. Each agent pushes their branch
2. Create PRs for each branch
3. Review and merge to main
4. Or merge branches locally:

```bash
git checkout main
git merge feature/user-auth-backend
git merge feature/user-auth-frontend
```

## Requirements

- **tmux** >= 3.0: `apt install tmux` or `brew install tmux`
- **git** >= 2.30: For worktree support

## Tips

1. **Plan first**: Clear specs reduce conflicts between agents
2. **Define contracts**: API contracts let frontend mock while backend implements
3. **Regular syncs**: Merge main periodically to avoid drift
4. **One role per worktree**: Don't mix responsibilities

## Related Commands

- `/workflows:plan` - Plan feature before parallel work
- `/workflows:status` - Check all agent status
- `/workflows:progress` - Track session progress
- `/workflows:sync` - Sync state between agents

## Source

Inspired by:
- [workmux](https://github.com/raine/workmux) - Git worktrees + tmux
- [uzi](https://github.com/devflowinc/uzi) - Parallel coding agents
