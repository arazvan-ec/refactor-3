# Worktree Manager Skill

Manage git worktrees for parallel development.

## What This Skill Does

- Create isolated worktrees for parallel work
- Enable multiple roles to work simultaneously
- Manage worktree lifecycle
- Facilitate parallel development

## Why Worktrees?

```
Traditional: One working directory
├── Can't run backend and frontend simultaneously
├── Context switching requires stashing
└── Limited parallelization

With Worktrees: Multiple working directories
├── Backend worktree → Run backend server
├── Frontend worktree → Run frontend server
├── QA worktree → Run tests independently
└── True parallel development
```

## Commands

### Create Worktree

```bash
# Create worktree for a feature
git worktree add ../project-backend feature/user-auth

# Create worktree for specific role
git worktree add ../project-frontend feature/user-auth

# Create from specific branch
git worktree add ../project-qa main
```

### List Worktrees

```bash
git worktree list

# Output:
# /home/user/project          abc1234 [main]
# /home/user/project-backend  def5678 [feature/user-auth]
# /home/user/project-frontend ghi9012 [feature/user-auth]
```

### Remove Worktree

```bash
# Remove worktree (after merging)
git worktree remove ../project-backend

# Force remove (if dirty)
git worktree remove --force ../project-backend
```

### Prune Stale Worktrees

```bash
git worktree prune
```

## Parallel Development Setup

### Setup for Feature

```bash
# 1. Create feature branch
git checkout -b feature/user-auth

# 2. Create worktrees for parallel work
git worktree add ../project-backend feature/user-auth
git worktree add ../project-frontend feature/user-auth

# 3. Start services in each worktree
# Terminal 1 (Backend):
cd ../project-backend
php -S localhost:8000 -t public/

# Terminal 2 (Frontend):
cd ../project-frontend
npm run dev
```

### Directory Structure

```
/home/user/
├── project/              # Main repo (Planner/QA)
│   └── .git/
├── project-backend/      # Backend worktree
│   └── .git → ../project/.git
└── project-frontend/     # Frontend worktree
    └── .git → ../project/.git
```

## Multi-Role Workflow

```bash
# Planner (main repo)
cd /home/user/project
/workflows:plan user-auth

# Backend (worktree 1)
cd /home/user/project-backend
/workflows:work user-auth --mode=roles --role=backend

# Frontend (worktree 2)
cd /home/user/project-frontend
/workflows:work user-auth --mode=roles --role=frontend

# QA (main repo, after implementation)
cd /home/user/project
/workflows:review user-auth
```

## Synchronization

```bash
# In any worktree, pull latest changes
git pull origin feature/user-auth

# Push from any worktree
git push origin feature/user-auth

# All worktrees see the same commits
```

## Cleanup After Feature

```bash
# After feature merged
git checkout main
git pull

# Remove worktrees
git worktree remove ../project-backend
git worktree remove ../project-frontend

# Prune any stale references
git worktree prune

# Delete feature branch
git branch -d feature/user-auth
```

## Integration with Workflow

```bash
# Create worktrees for feature
/multi-agent-workflow:setup-worktrees user-auth

# Internally:
# 1. git worktree add ../project-backend feature/user-auth
# 2. git worktree add ../project-frontend feature/user-auth
# 3. Report paths for parallel development
```

## Best Practices

1. **One worktree per role**: Keeps contexts separate
2. **Same branch**: All worktrees on same feature branch
3. **Frequent sync**: Pull before starting work
4. **Clean up**: Remove worktrees after merge
5. **Don't mix**: Don't do backend work in frontend worktree
