---
name: workflows:plan
description: "Convert ideas into implementable strategies with detailed planning. The foundation of compound engineering (80% planning, 20% execution)."
argument_hint: <feature-name> [--workflow=default|task-breakdown]
---

# Multi-Agent Workflow: Plan

The planning phase is the foundation of compound engineering. Invest 80% of effort here.

## Usage

```
/workflows:plan user-authentication
/workflows:plan payment-system --workflow=task-breakdown
```

## Arguments

- `feature-name`: Name for the feature (kebab-case)
- `--workflow`: (optional) Planning depth
  - `default` - Standard planning with API contracts and task breakdown
  - `task-breakdown` - Exhaustive planning (10 documents) for complex features

## Philosophy

> "Each unit of engineering work should make subsequent units easier—not harder"

Good planning means:
- Engineers can start WITHOUT asking questions
- API contracts are complete enough to mock
- References to existing code patterns provided
- Every task has clear "definition of done"

## What This Command Does

### Step 1: Create Feature Workspace

```bash
FEATURE_ID="$ARGUMENTS"
WORKSPACE=".ai/project/features/${FEATURE_ID}"
mkdir -p "${WORKSPACE}"
```

### Step 2: Load Planner Context

```
Read: agents/roles/planner.md
Read: rules/global_rules.md
Read: rules/ddd_rules.md
Read: rules/project_specific.md
```

### Step 3: Analyze Existing Patterns

Before planning, analyze the codebase:
- Find similar features to use as reference
- Identify existing code patterns
- Understand technical constraints

### Step 4: Create Planning Documents

**For `default` workflow:**
1. `FEATURE_${FEATURE_ID}.md` - Feature definition with:
   - Objective and context
   - Acceptance criteria (testable)
   - API contracts (complete)
   - Task breakdown by role

2. `50_state.md` - State tracking for all roles

3. `30_tasks.md` - Detailed task breakdown

**For `task-breakdown` workflow (10 documents):**
1. `00_requirements_analysis.md`
2. `10_architecture.md`
3. `15_data_model.md`
4. `20_api_contracts.md`
5. `30_tasks_backend.md`
6. `31_tasks_frontend.md`
7. `32_tasks_qa.md`
8. `35_dependencies.md`
9. `FEATURE_${FEATURE_ID}.md`
10. `50_state.md`

### Step 5: Initialize State File

```markdown
# Feature State: ${FEATURE_ID}

## Overview
**Feature**: ${FEATURE_ID}
**Workflow**: ${WORKFLOW_TYPE}
**Created**: $(date -Iseconds)
**Status**: PLANNING

---

## Planner / Architect
**Status**: IN_PROGRESS
**Notes**: Planning feature

---

## Backend Engineer
**Status**: PENDING
**Notes**: Waiting for planning to complete

---

## Frontend Engineer
**Status**: PENDING
**Notes**: Waiting for planning to complete

---

## QA / Reviewer
**Status**: PENDING
**Notes**: Waiting for implementation to complete
```

## Planning Checklist

Before marking planning as COMPLETED, verify:

- [ ] Feature objective is clear and measurable
- [ ] All acceptance criteria defined (testable)
- [ ] All API endpoints fully specified (request/response/errors)
- [ ] References to existing patterns provided
- [ ] Tasks broken down by role (backend, frontend, qa)
- [ ] Each task has "done" definition
- [ ] Dependencies identified
- [ ] All questions answered (no "TBD" or "unclear")

## Self-Review Questions

- Can backend engineer start WITHOUT asking questions? YES/NO
- Can frontend engineer start WITHOUT asking questions? YES/NO
- Does QA know exactly what to test? YES/NO
- Are API contracts complete enough to mock? YES/NO
- Are there references to existing code patterns? YES/NO

**If any NO, plan is incomplete. Add missing details.**

## API Contract Template

Every endpoint must be fully specified:

```markdown
### Endpoint: POST /api/users

**Purpose**: Create new user account
**Authentication**: Public

**Request**:
{
  "email": "string, required, valid email",
  "name": "string, required, 2-50 chars",
  "password": "string, required, min 8 chars"
}

**Success Response (201)**:
{
  "id": "uuid",
  "email": "string",
  "name": "string",
  "created_at": "ISO 8601 datetime"
}

**Error Responses**:
- 400: Validation failed (with details)
- 409: Email already exists
```

## Task Template

Each task must include:

```markdown
### Task BE-001: Create User Entity

**Role**: Backend Engineer
**Reference**: src/Domain/Entity/Order.php
**Methodology**: TDD (Red-Green-Refactor)
**Max Iterations**: 10

**Requirements**:
- User entity with id, email, name, password
- Email value object with validation
- Follow DDD principles

**Tests to Write FIRST**:
- [ ] test_user_can_be_created_with_valid_data()
- [ ] test_user_rejects_invalid_email()

**Acceptance Criteria**:
- [ ] Entity exists in src/Domain/Entity/
- [ ] Tests pass with >80% coverage
- [ ] No Doctrine annotations in Domain

**Verification**:
php bin/phpunit tests/Unit/Domain/Entity/UserTest.php

**Escape Hatch**: If blocked after 10 iterations, document in DECISIONS.md
```

## Output

After successful planning:

```
Feature workspace created: .ai/project/features/${FEATURE_ID}/

Planning documents:
- FEATURE_${FEATURE_ID}.md (definition)
- 50_state.md (state tracking)
- 30_tasks.md (task breakdown)

Next steps:
1. Review the plan for completeness
2. Start implementation:
   /workflows:work --mode=roles --role=backend ${FEATURE_ID}
   /workflows:work --mode=layers --layer=domain ${FEATURE_ID}

3. Or start all roles in parallel (Tilix terminal):
   ./workflow start ${FEATURE_ID} --parallel
```

## Compound Effect

Good planning compounds:
- Clear specs reduce back-and-forth
- Patterns become templates for future features
- Decisions documented prevent repeated debates
- API contracts enable parallel development
