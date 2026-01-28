---
name: workflows:compound
description: "Capture learnings after completing a feature to make future work easier. The compounding effect of engineering."
argument_hint: <feature-name>
---

# Multi-Agent Workflow: Compound

Capture insights from completed features to make future development easier.

## Philosophy

> "Each unit of engineering work should make subsequent units easier—not harder"
> — Compound Engineering Principle

The `/workflows:compound` command is the key differentiator of compound engineering.
Without it, you're just doing work. With it, work builds on work.

## The 70% Problem Awareness

> "AI helps you reach 70% quickly, but the remaining 30% is where real complexity lives."
> — Addy Osmani, Beyond Vibe Coding

When capturing learnings, pay special attention to **where the 70% ended and the 30% began**:

```
Feature Timeline:
├── 0-70%: Fast progress (AI excels)
│   └── Scaffolding, CRUD, happy paths
│
└── 70-100%: Slow progress (Human expertise needed)
    ├── Edge cases
    ├── Error handling
    ├── Security hardening
    └── Integration issues
```

**Questions to answer in compound capture:**
- Where did progress slow down? (The 70% boundary)
- What caused the "two-step-back" pattern? (Fixes introducing bugs)
- What would have prevented the slowdown if known earlier?
- What should future specs include to avoid this?

This awareness helps future planning account for the **real complexity**, not just the easy 70%.

## Usage

```bash
# After QA approval
/workflows:compound user-authentication
```

## When to Run

Run after:
- ✅ Feature is APPROVED by QA
- ✅ All tests passing
- ✅ Ready to merge

## What This Command Does

### Step 1: Analyze Feature History

```bash
# Get all commits for the feature
git log --oneline feature/${FEATURE_ID}

# Get diff from base branch
git diff main...feature/${FEATURE_ID}

# Analyze files changed
git diff --stat main...feature/${FEATURE_ID}
```

### Step 2: Extract Patterns

Identify what went well:

```markdown
## Patterns Identified

### Pattern 1: Email Validation Value Object
**Where**: src/Domain/ValueObject/Email.php
**Why it worked**: Encapsulates validation, immutable, reusable
**Recommendation**: Use for all email fields in future entities

### Pattern 2: TDD for Use Cases
**Where**: tests/Application/CreateUserUseCaseTest.php
**Why it worked**: Found 3 bugs during Red phase before implementation
**Recommendation**: Always write use case tests first
```

### Step 3: Identify Anti-Patterns & The 70% Boundary

Document what should be avoided AND where progress slowed:

```markdown
## Anti-Patterns Found

### Anti-Pattern 1: Skipping Integration Tests
**Where**: Initial implementation had no API tests
**What happened**: 500 error found only during QA
**Cost**: 2 extra iterations to fix
**Rule**: Always write integration tests for new endpoints

### Anti-Pattern 2: Incomplete API Contract
**Where**: FEATURE_X.md missing error response formats
**What happened**: Frontend had to guess error handling
**Cost**: 3 back-and-forth messages to clarify
**Rule**: Always specify all error responses in contracts

## The 70% Boundary Analysis

### Where did the 70% end?
**Milestone**: Basic CRUD working, happy path tests passing
**Time spent**: 2 hours (40% of total)

### What made the 30% hard?
1. **Edge case**: Email already exists scenario
   - Not in original spec
   - Required new validation logic
   - Added 1 hour

2. **Security**: Password hashing integration
   - bcrypt config not documented
   - Trial and error with rounds
   - Added 45 minutes

3. **Integration**: Frontend form validation mismatch
   - Backend and frontend had different rules
   - Required sync meeting
   - Added 30 minutes

### What would have helped?
- [ ] Spec should include ALL error scenarios upfront
- [ ] Security requirements should reference existing patterns
- [ ] Validation rules should be in shared contract

### Prevention for future features
- Add "Error Scenarios" section to spec template
- Create validation rules library (shared between BE/FE)
- Document security patterns in project_specific.md
```

### Step 4: Update Project Rules

If patterns are generalizable, update rules:

```markdown
# Additions to global_rules.md

## Email Validation (Added from user-authentication feature)
All email fields must use the Email value object pattern:
- Create src/Domain/ValueObject/Email.php
- Validation in constructor
- Immutable (no setters)
- Reference: src/Domain/ValueObject/Email.php from user-auth feature
```

### Step 5: Create Compound Log Entry

Append to `.ai/project/compound_log.md`:

```markdown
# Compound Log

## 2026-01-16: user-authentication

### Summary
Implemented user registration with email/password authentication.
3 iterations to complete Domain layer, 2 for Application layer.

### Time Investment
- Planning: 2 hours (40%)
- Implementation: 2 hours (40%)
- Review: 30 minutes (10%)
- Compound: 30 minutes (10%)
- **Total**: 5 hours

### Learnings Captured

#### Patterns to Reuse
1. **Email Value Object** - Use for all email validation
   - File: src/Domain/ValueObject/Email.php
   - Tests: tests/Unit/Domain/ValueObject/EmailTest.php

2. **Registration Form Pattern** - Use for all auth forms
   - File: src/components/RegistrationForm.tsx
   - Tests: src/__tests__/RegistrationForm.test.tsx

#### Rules Updated
- global_rules.md: Added Email VO requirement
- ddd_rules.md: Added Value Object immutability check

#### Anti-Patterns Documented
1. Skipping integration tests → Added to QA checklist
2. Incomplete API contracts → Added template requirement

### Impact on Future Work
- Next auth feature (password reset) can reuse:
  - Email VO ✓
  - Form pattern ✓
  - Test structure ✓
- Estimated time savings: 30-40%

### Questions for Future
- Should we create a shared auth package?
- Is JWT refresh token pattern documented?
```

### Step 6: Update Feature Templates

If new templates discovered:

```bash
# Save successful patterns as templates
cp .ai/project/features/user-auth/FEATURE_user-auth.md \
   .ai/workflow/templates/FEATURE_auth_template.md
```

## Compound Checklist

- [ ] Analyzed git history for patterns
- [ ] Documented successful patterns (2-3)
- [ ] Documented anti-patterns (1-2)
- [ ] **Identified the 70% boundary** (where progress slowed)
- [ ] **Documented what made the 30% hard**
- [ ] **Listed preventions for future features**
- [ ] Updated relevant project rules
- [ ] Added entry to compound_log.md
- [ ] Created/updated templates if applicable
- [ ] Estimated time savings for future work

## Output

After running compound:

```
Compound capture complete for: user-authentication

Patterns captured: 3
Anti-patterns documented: 2
Rules updated: 2 files
Templates created: 1

Estimated future time savings: 30-40% on similar features

Next feature recommendation:
- Use Email VO pattern
- Follow RegistrationForm pattern
- Reference: .ai/project/compound_log.md

Compound log updated: .ai/project/compound_log.md
```

## Compound Metrics

Track compounding effect over time:

```markdown
## Compound Metrics

| Feature | Planning | Implementation | Review | Compound | Total | Patterns |
|---------|----------|----------------|--------|----------|-------|----------|
| user-auth | 2h | 2h | 0.5h | 0.5h | 5h | 3 new |
| password-reset | 1h | 1h | 0.5h | 0.5h | 3h | 2 reused |
| profile-edit | 0.5h | 1h | 0.5h | 0.5h | 2.5h | 3 reused |

**Trend**: Each feature takes less time as patterns compound.
```

## The Compound Effect

```
Feature 1: 5 hours + 3 patterns captured
Feature 2: 3 hours (reused 2 patterns) + 2 new patterns
Feature 3: 2.5 hours (reused 4 patterns) + 1 new pattern
Feature 4: 2 hours (reused 5 patterns)

Total: 12.5 hours for 4 features
Without compounding: ~20 hours (5h each)
Time saved: 37.5%
```

## State Update

After compound capture:

```markdown
## Feature: user-authentication
**Status**: COMPLETED + COMPOUNDED
**Compound Date**: 2026-01-16
**Patterns Captured**: 3
**Rules Updated**: 2

### Compound Summary
- Email VO pattern documented
- RegistrationForm pattern documented
- Integration test requirement added to rules
- Estimated 30-40% time savings on similar features
```

## Best Practices

1. **Run immediately after QA approval** - Context is fresh
2. **Be specific about patterns** - Include file paths
3. **Quantify impact** - "Saved 2 iterations" not "was helpful"
4. **Update rules** - Make patterns enforceable
5. **Reference future work** - Connect to next features

## Compound Effect Over Time

```
Month 1: Establishing patterns (slower)
Month 2: Reusing patterns (faster)
Month 3: Pattern library mature (much faster)
Month 6: New features feel like "already done"
```

The compound effect is why planning matters: good planning creates good patterns, good patterns accelerate future work.
