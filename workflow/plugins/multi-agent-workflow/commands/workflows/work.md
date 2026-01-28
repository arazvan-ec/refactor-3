---
name: workflows:work
description: "Execute implementation with configurable parallelization modes (roles, layers, or stacks)."
argument_hint: <feature-name> --mode=<roles|layers|stacks> [--role=<role>] [--layer=<layer>]
---

# Multi-Agent Workflow: Work

Execute implementation with the compound engineering principle: make each unit of work easier.

## Usage

```bash
# By Role (standard)
/workflows:work user-auth --mode=roles --role=backend
/workflows:work user-auth --mode=roles --role=frontend

# By Layer (DDD)
/workflows:work user-auth --mode=layers --layer=domain
/workflows:work user-auth --mode=layers --layer=application
/workflows:work user-auth --mode=layers --layer=infrastructure

# By Stack
/workflows:work user-auth --mode=stacks --stack=backend
/workflows:work user-auth --mode=stacks --stack=frontend
```

## Parallelization Modes

### Mode: Roles (Standard)
```
Planner → Backend + Frontend (parallel) → QA
```

**Roles available**: `backend`, `frontend`

**Best for**:
- Full-stack features with separate concerns
- Teams with distinct frontend/backend developers
- Standard feature development

### Mode: Layers (DDD)
```
Domain + Application + Infrastructure (can run parallel)
```

**Layers available**: `domain`, `application`, `infrastructure`

**Best for**:
- Complex business logic
- Backend-heavy features
- DDD architecture compliance

### Mode: Stacks
```
Backend complete + Frontend complete (parallel)
```

**Stacks available**: `backend`, `frontend`

**Best for**:
- Independent features
- Separate deployment pipelines
- Large features with clear boundaries

## Execution Flow

### Step 1: Load Context

```bash
# Based on mode, load appropriate agent
--mode=roles --role=backend → Read: agents/roles/backend.md
--mode=roles --role=frontend → Read: agents/roles/frontend.md
--mode=layers --layer=domain → Read: agents/roles/backend.md + rules/ddd_rules.md
```

### Step 2: Git Sync

```bash
git pull origin feature/${FEATURE_ID} || git pull origin main
```

### Step 3: Load Feature Context

```bash
Read: .ai/project/features/${FEATURE_ID}/50_state.md
Read: .ai/project/features/${FEATURE_ID}/FEATURE_*.md
Read: .ai/project/features/${FEATURE_ID}/30_tasks.md
```

### Step 4: Verify Prerequisites

**For Roles mode**:
- Check Planner status is COMPLETED
- If `frontend`: Check if backend API ready or mock needed

**For Layers mode**:
- Domain: Can start immediately
- Application: Domain should be COMPLETED (or mock)
- Infrastructure: Application should be COMPLETED

### Step 5: Execute with TDD

Follow the TDD cycle for each task:

```
1. 🔴 RED: Write test FIRST (must fail)
2. 🟢 GREEN: Write minimum code to pass
3. 🔵 REFACTOR: Improve while keeping tests green
```

### Step 6: Auto-Correction Loop (Ralph Wiggum Pattern)

```python
iterations = 0
MAX_ITERATIONS = 10

while tests_failing and iterations < MAX_ITERATIONS:
    analyze_error()
    fix_code()
    run_tests()
    iterations += 1

if tests_passing:
    checkpoint_complete()
else:
    document_blocker()
    mark_blocked()
```

### Step 7: Checkpoint

After each logical unit:

```bash
/workflows:checkpoint ${ROLE} ${FEATURE_ID} "Completed ${UNIT}"
```

## Role-Specific Workflows

### Backend Workflow (Layers: domain → application → infrastructure)

```markdown
Checkpoint 1: Domain Layer
- Entities, Value Objects
- Verification: php bin/phpunit tests/Unit/Domain/
- Coverage: >80%

Checkpoint 2: Application Layer
- Use Cases, DTOs
- Verification: php bin/phpunit tests/Unit/Application/
- Coverage: >80%

Checkpoint 3: Infrastructure Layer
- Repositories, Controllers
- Verification: php bin/phpunit tests/Integration/
- Schema validation

Checkpoint 4: API Endpoints
- REST endpoints
- Verification: curl tests, API contract validation
```

### Frontend Workflow

```markdown
Checkpoint 1: Component Structure
- TypeScript types, component skeleton
- Verification: npm run type-check

Checkpoint 2: Form Logic
- Validation, event handlers
- Verification: npm test -- ComponentName

Checkpoint 3: API Integration
- Real API or mocks
- Verification: Integration tests

Checkpoint 4: Responsive Design
- Mobile (375px), Tablet (768px), Desktop (1024px)
- Verification: Visual inspection

Checkpoint 5: Accessibility
- Lighthouse score >90
- Verification: npm run lighthouse
```

## State Updates

Update `50_state.md` at each checkpoint:

```markdown
## Backend Engineer
**Status**: IN_PROGRESS
**Checkpoint**: Domain layer complete
**Timestamp**: 2026-01-16T14:30:00Z
**Tests**: 15/15 passing, 92% coverage
**Iterations**: 3

### Resume Information
- **Completed**: User entity, Email VO
- **Next Task**: CreateUserUseCase (BE-005)
- **Files to Read on Resume**:
  - 30_tasks.md (Task BE-005)
  - src/Domain/Entity/User.php
```

## Escape Hatch

If blocked after 10 iterations:

```markdown
## Blocker: [Task Name]

**Iterations attempted**: 10
**Last error**: [exact error]

**What was tried**:
1. [Approach 1] → [Result]
2. [Approach 2] → [Result]

**Root cause hypothesis**: [Why failing]

**Suggested alternatives**:
1. [Alternative 1]
2. [Alternative 2]

**Status**: BLOCKED - Needs Planner decision
```

## Verification Commands

### Backend
```bash
php bin/phpunit                          # All tests
php bin/phpunit --coverage-text          # Coverage check
./vendor/bin/php-cs-fixer fix --dry-run  # Style check
```

### Frontend
```bash
npm test                    # All tests
npm test -- --coverage      # Coverage check
npm run lint               # Linting
npm run type-check         # TypeScript
```

## Compound Effect

Good work execution compounds:
- TDD prevents regressions
- Checkpoints enable session resumption
- Documentation helps onboarding
- Tests become living specifications
