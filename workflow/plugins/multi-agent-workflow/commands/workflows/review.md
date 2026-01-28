---
name: workflows:review
description: "Multi-agent review before merge. Validates implementation against specs, runs tests, and approves or rejects."
argument_hint: <feature-name> [--agent=<security|performance|ddd|code>]
---

# Multi-Agent Workflow: Review

Comprehensive review phase using specialized review agents.

## Usage

```bash
# Full review (all agents)
/workflows:review user-authentication

# Specific review agent
/workflows:review user-authentication --agent=security
/workflows:review user-authentication --agent=performance
/workflows:review user-authentication --agent=ddd
/workflows:review user-authentication --agent=code
```

## Review Agents

| Agent | Focus | When to Use |
|-------|-------|-------------|
| **qa** (default) | Full review | Always |
| **security** | OWASP, vulnerabilities | Auth, payments, sensitive data |
| **performance** | Speed, optimization | High-traffic features |
| **ddd** | DDD compliance | Backend with business logic |
| **code** | Code quality, patterns | All features |

## Philosophy

> "Trust = Passing Test Suite + Evidence"

Never say "it works" without:
- Test results with commands
- Screenshots/logs as evidence
- Explicit acceptance criteria verification

## Review Process

### Phase 1: API Testing (Backend Verification)

```bash
# Happy path
curl -X POST localhost:8000/api/users -d '{"email":"test@example.com",...}'
# Expected: 201 Created
# Actual: [document response]

# Validation errors
curl -X POST localhost:8000/api/users -d '{"email":"invalid"}'
# Expected: 400 Bad Request
# Actual: [document response]

# Edge cases
curl -X POST localhost:8000/api/users -d '{"email":"existing@email.com"}'
# Expected: 409 Conflict
# Actual: [document response]
```

### Phase 2: UI Testing (Frontend Verification)

```markdown
Test 1: Happy Path
- Steps: Open /register → Fill valid data → Submit
- Expected: Success toast, redirect to /dashboard
- Actual: [document result]
- Evidence: [screenshot description]

Test 2: Validation
- Steps: Enter invalid email → Submit
- Expected: Error message under field
- Actual: [document result]

Test 3: Responsive
- Mobile (375px): [document result]
- Tablet (768px): [document result]
- Desktop (1024px): [document result]
```

### Phase 3: Automated Test Execution

```bash
# Backend
php bin/phpunit tests/
# Result: X/Y tests passing
# Coverage: Z%

# Frontend
npm test
# Result: X/Y tests passing
# Coverage: Z%

# E2E
npm run test:e2e -- feature-name
# Result: X/Y scenarios passing
```

### Phase 4: Code Quality Review

```markdown
1. TDD Compliance (CRITICAL):
   - [ ] Git history shows tests before implementation
   - [ ] Red-Green-Refactor cycle followed

   Verification:
   git log --oneline | head -20
   # Expected: test commits before implementation commits

2. DDD Compliance (Backend):
   - [ ] Domain layer has no infrastructure dependencies
   - [ ] Entities have behavior, not just getters
   - [ ] Value objects are immutable

3. Test Coverage:
   - Backend: >80% required, actual: [X]%
   - Frontend: >70% required, actual: [X]%

4. Code Style:
   - Backend: ./vendor/bin/php-cs-fixer fix --dry-run
   - Frontend: npm run lint
```

### Phase 5: Acceptance Criteria Validation

For each criterion in FEATURE_X.md:

```markdown
Criterion 1: "User can register with email/name/password"
- [✓] PASS
- Evidence: curl test returned 201, user in database
- Screenshot: [description]

Criterion 2: "System validates email format"
- [✓] PASS
- Evidence: Invalid email → 400 with error details

Criterion 3: "User redirected after registration"
- [✓] PASS
- Evidence: Browser redirected to /dashboard
```

## Issue Reporting Template

```markdown
## Issue #1: [Title]

**Severity**: CRITICAL | MAJOR | MINOR
**Type**: Backend | Frontend | Integration
**Status**: OPEN

**Steps to Reproduce**:
1. [Step 1]
2. [Step 2]

**Expected**: [What should happen]
**Actual**: [What happened]

**Evidence**:
- Error log: [paste log]
- Screenshot: [description]

**Root Cause** (if known): [hypothesis]
**Suggested Fix**: [recommendation]
**Location**: file.php:45

**Assigned To**: Backend Engineer | Frontend Engineer
**Priority**: P0 | P1 | P2
```

## Decision Criteria

### APPROVED - All must be true:
- ✅ All acceptance criteria met (with evidence)
- ✅ No critical/major bugs (P0/P1)
- ✅ All automated tests passing
- ✅ Code meets quality standards
- ✅ Documentation complete

### REJECTED - Any of these:
- ❌ Any acceptance criterion fails
- ❌ Critical or major bug found
- ❌ Automated tests failing
- ❌ Security vulnerability present
- ❌ Code quality below standards

## QA Report Template

```markdown
# QA Report: ${FEATURE_ID}

**Reviewer**: Claude QA
**Date**: 2026-01-16
**Status**: APPROVED | REJECTED

## Summary
[1-2 sentence summary]

## Test Results
- API Tests: X/Y passing
- UI Tests: X/Y passing
- Unit Tests: X/Y passing (Z% coverage)
- E2E Tests: X/Y passing

## Acceptance Criteria
- [✓] Criterion 1 - Evidence: [...]
- [✓] Criterion 2 - Evidence: [...]
- [✗] Criterion 3 - Issue #1

## Issues Found
### Critical (blocks approval)
- Issue #1: [description]

### Minor (can fix later)
- Issue #2: [description]

## Decision
**Status**: REJECTED

**Reason**: Issue #1 blocks user registration flow

**Must fix before approval**:
1. Fix Issue #1 (Backend)

**Next steps**:
- Backend fixes Issue #1
- Re-review after fix pushed
```

## State Update

After review, update `50_state.md`:

```markdown
## QA / Reviewer
**Status**: APPROVED | REJECTED
**Review Date**: 2026-01-16
**Critical Issues**: 0 | [count]
**Minor Issues**: [count]

### Review Summary
- Acceptance Criteria: 5/5 passed
- Test Coverage: Backend 87%, Frontend 78%
- Issues Found: 0 critical, 2 minor

### Decision
APPROVED - Feature ready for merge
```

## Compound Effect

Good reviews compound:
- Issues found early save debugging time
- Documented patterns prevent future bugs
- Test evidence becomes regression prevention
- Quality standards improve over time
