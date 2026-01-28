# Feature State: FEATURE_X

> This file tracks the state of each role and provides completion signals.
> Update your section when completing checkpoints or changing status.

**Feature**: FEATURE_X
**Workflow**: default
**Created**: 2026-01-15
**Last Updated**: 2026-01-16

---

## Planner

| Field | Value |
|-------|-------|
| **Status** | `PENDING` |
| **Completion Signal** | `false` |
| **Started** | - |
| **Completed** | - |

### Progress
- [ ] Requirements analysis
- [ ] Architecture design
- [ ] API contracts
- [ ] Task breakdown

### Artifacts Created
<!-- List artifacts created by Planner -->

### Notes
Feature initialized, waiting for planning to start.

### Next Action
Start planning when feature requirements are provided.

---

## Backend

| Field | Value |
|-------|-------|
| **Status** | `PENDING` |
| **Completion Signal** | `false` |
| **Started** | - |
| **Completed** | - |
| **Depends On** | Planner |

### Checkpoints
- [ ] Domain Layer
- [ ] Application Layer
- [ ] Infrastructure Layer
- [ ] API Endpoints

### Auto-Correction Loop
| Checkpoint | Iterations | Result |
|------------|------------|--------|
| - | - | - |

### Artifacts Created
<!-- List files created by Backend -->

### Test Results
```
# Paste test output here
```

### Coverage
- **Lines**: -%
- **Branches**: -%

### Blockers
Planning not completed yet.

### Next Action
Wait for Planner.completion_signal == true

---

## Frontend

| Field | Value |
|-------|-------|
| **Status** | `PENDING` |
| **Completion Signal** | `false` |
| **Started** | - |
| **Completed** | - |
| **Depends On** | Planner |
| **API Ready** | `false` |

### Checkpoints
- [ ] Component Structure
- [ ] Form Logic
- [ ] API Integration
- [ ] Responsive & A11y

### Auto-Correction Loop
| Checkpoint | Iterations | Result |
|------------|------------|--------|
| - | - | - |

### Artifacts Created
<!-- List files created by Frontend -->

### Test Results
```
# Paste test output here
```

### Lighthouse Scores
| Metric | Score |
|--------|-------|
| Performance | - |
| Accessibility | - |
| Best Practices | - |
| SEO | - |

### Responsive Testing
- [ ] Mobile (375px)
- [ ] Tablet (768px)
- [ ] Desktop (1024px)

### Blockers
Planning not completed yet.

### Next Action
Wait for Planner.completion_signal == true

---

## QA

| Field | Value |
|-------|-------|
| **Status** | `PENDING` |
| **Completion Signal** | `false` |
| **Started** | - |
| **Completed** | - |
| **Depends On** | Backend, Frontend |
| **Decision** | - |

### Testing Phases
- [ ] Phase 1: API Testing
- [ ] Phase 2: UI Testing
- [ ] Phase 3: Automated Tests
- [ ] Phase 4: Code Quality
- [ ] Phase 5: Acceptance Criteria

### Findings Summary
| Severity | Count |
|----------|-------|
| Critical | 0 |
| Major | 0 |
| Minor | 0 |

### Acceptance Criteria
| Criterion | Status | Evidence |
|-----------|--------|----------|
| - | - | - |

### Decision
<!-- APPROVED or REJECTED with reasoning -->

### Next Action
Wait for Backend.completion_signal == true AND Frontend.completion_signal == true

---

## Status Reference

### Valid Statuses
- `PENDING` - Not started
- `IN_PROGRESS` - Currently working
- `BLOCKED` - Cannot proceed (document blocker)
- `WAITING_API` - Frontend waiting for backend API
- `COMPLETED` - Finished successfully
- `APPROVED` - QA approved
- `REJECTED` - QA rejected (list issues)

### Completion Signal
- `true` - Role has finished all work
- `false` - Role still has work to do

### Workflow Trigger Conditions
- **Backend/Frontend start**: Planner.completion_signal == true
- **QA start**: Backend.completion_signal == true AND Frontend.completion_signal == true
- **Feature done**: QA.status == APPROVED

---

## Comprehension Tracking

> Track comprehension debt to ensure sustainable development velocity

### Current Assessment

| Field | Value |
|-------|-------|
| **Debt Level** | 🟢 LOW / 🟡 MEDIUM / 🔴 HIGH |
| **Last Checkpoint** | - |
| **Knowledge Score** | -/5 |
| **Next Check Due** | After first checkpoint |

### Debt Indicators

| Indicator | Count | Notes |
|-----------|-------|-------|
| "Magic" code incidents | 0 | Code that works but isn't understood |
| Patterns copied without understanding | 0 | Patterns used without knowing why |
| Over-engineering flags | 0 | YAGNI violations |
| Unexplained abstractions | 0 | Abstractions without documented purpose |

### Self-Review Status

| Role | Self-Review Done | Score | Issues Found |
|------|------------------|-------|--------------|
| Backend | ⬜ Pending | - | - |
| Frontend | ⬜ Pending | - | - |

### Comprehension Checkpoints

| Checkpoint | Date | Score | Action Taken |
|------------|------|-------|--------------|
| - | - | - | - |

### Knowledge Gaps Identified

<!-- Document any areas where understanding is weak -->
None identified yet.

### Recommended Actions

<!-- Actions to reduce comprehension debt -->
Feature not started - no actions needed yet.

---

## Session Resume Info

> Fill this when ending a session to help resume later

### Last Session
| Field | Value |
|-------|-------|
| **Role** | - |
| **Date** | - |
| **Last Checkpoint** | - |
| **Context Status** | Fresh |

### Resume Instructions
This is a fresh feature. Start with Planner role.

### Important Context
No context yet - feature not started.

---

## Status History

| Date | Role | From | To | Reason |
|------|------|------|-----|--------|
| 2026-01-15 | All | - | PENDING | Feature initialized |
| 2026-01-16 | - | - | - | Updated to new completion signal format |
