# Framework Rules - Multi-Agent Workflow

**Framework Version**: 2.0.0
**Last Updated**: 2026-01-28

---

## Purpose

These are the **fundamental rules** of the Multi-Agent Workflow framework. They apply to ALL projects using this plugin and should NOT be modified per-project. Project-specific rules go in `.ai/extensions/rules/`.

---

## Core Principles

### 0. Mandatory Routing - Ask Before Acting (HIGHEST PRIORITY)

**Rule**: Every interaction MUST pass through the workflow router before executing any work.

```
USER REQUEST → ROUTING → CLARIFICATION (if needed) → WORKFLOW SELECTION → EXECUTION
```

#### Routing Protocol

1. **Analyze** the user's request immediately
2. **Classify** the work type:
   - Feature (new functionality)
   - Bug (fix existing)
   - Refactor (improve without changing behavior)
   - Investigation (research/analysis)
   - Documentation
   - Review
   - Other
3. **Calculate confidence** (0-100%):
   - Clear keywords: +20
   - File references: +15
   - Action verbs: +15
   - Expected behavior described: +20
   - Vague language: -25
4. **If confidence < 60%**: ASK clarifying questions
5. **If confidence >= 60%**: Propose workflow and confirm

#### Clarifying Questions (When to Ask)

```
MUST ASK when:
- Request is vague or ambiguous
- Multiple workflows could apply
- Scope is unclear
- Sensitive areas (auth, payments, security) may be touched
- User says "just do it" without specifications
- First-time interaction in session
```

#### Self-Correction Protocol

If you realize you started work without proper routing:

```markdown
## Self-Correction

STOP - I started work without proper routing.

1. Pause current work
2. Ask clarifying questions NOW
3. Confirm workflow selection with user
4. Then continue with proper context
```

#### Prohibitions

- **NEVER** assume what the user wants without clarification
- **NEVER** pick a workflow without explaining why
- **NEVER** skip routing because "it seems obvious"
- **NEVER** proceed when confidence is low

---

### 1. Explicit Context - No Implicit Memory

**Rule**: All shared knowledge must be explicitly in files. Never assume implicit context.

```
NO: "Remember we said earlier that..."
YES: "Read the file `.ai/project/features/FEATURE_X/50_state.md`"
```

### 2. Immutable Roles

**Rule**: One Claude instance = one fixed role during the entire session.

- Don't switch from Backend to Frontend mid-task
- Don't implement code if you're QA
- Don't make design decisions if you're Backend/Frontend

### 3. Workflow is Law

**Rule**: Follow the defined YAML workflow without skipping stages.

- Don't implement before Planning is `COMPLETED`
- Don't QA before Implementation is `COMPLETED`
- If you need to change workflow, document why in `DECISIONS.md`

### 4. Synchronized State

**Rule**: Use `50_state.md` to communicate state between roles.

- Update your `50_state.md` frequently
- Read `50_state.md` from other roles before starting
- Use standard states: `PENDING`, `IN_PROGRESS`, `BLOCKED`, `WAITING_API`, `COMPLETED`, `APPROVED`, `REJECTED`

### 5. Git as Synchronization

**Rule**: Git is the synchronization mechanism between instances.

- `git pull` before starting work
- `git push` after completing tasks
- Clear and descriptive commits
- Don't force push unless absolutely necessary

---

## Context Window Management

**Rule**: Manage context as a limited resource. Treat memory like a Commodore 64.

### Context Management Principles

```
Context Window ~ 100k tokens (approximately)
   - Code read
   - Conversation history
   - Tool results
   - Errors and outputs

Warning Signs of Full Context:
   - Slower responses
   - "Forgetting" recent information
   - Incomplete or cut-off responses
   - Reference errors to previous code
```

### Signals to Restart Session

- Read more than 20 files in the session
- Been in the same session for more than 2 hours
- Conversation has more than 50 messages
- Forgetting things discussed earlier
- Responses becoming slower or incomplete

### Session Restart Protocol

1. Save current state in `50_state.md`
2. Commit all work with checkpoint message
3. Document resume point (last checkpoint, next task, relevant files)
4. Start new session reading: role.md, 50_state.md, relevant files

---

## Comprehension Debt Management (The 80% Problem)

**Source**: Addy Osmani, "The 80% Problem in Agentic Coding" (2026)

**Rule**: Code generation speed must NOT exceed comprehension speed.

> *"It's trivially easy to review code you could no longer write from scratch."* - Addy Osmani

### Comprehension Debt Indicators

| Level | Indicator | Action |
|-------|-----------|--------|
| CRITICAL | "Works but I don't know why" | STOP - Understand before continuing |
| CRITICAL | Can't predict behavior for new inputs | Write more tests, study edge cases |
| HIGH | Need to re-read file for every change | Document key concepts |
| MEDIUM | Copy patterns without understanding why | Study the pattern first |
| LOW | Can explain code from memory | Healthy state |

### Mandatory Comprehension Checkpoints

Before marking COMPLETED, verify:

```markdown
## Comprehension Checkpoint

- [ ] Can explain what code does WITHOUT looking at it
- [ ] Could rewrite it if necessary
- [ ] Understand ALL abstractions used
- [ ] No "magic" code without explanation
- [ ] Could explain this to a new team member
- [ ] Decisions documented with "why"

**Score**: [1-5] (minimum 3 to continue)
```

### Mandatory Self-Review

Before completing code, the agent must critique their own work:

```markdown
## Self-Review Checklist

### Code Critique
- [ ] Would I write this the same way manually?
- [ ] Are there abstractions I don't fully understand?
- [ ] Did I copy patterns without understanding why?
- [ ] Is there "magic" values or logic I can't justify?

### Assumption Validation
- [ ] What assumptions did I make?
- [ ] Did I validate these assumptions or just proceed?
- [ ] What could fail that I haven't considered?

### Simplification Check
- [ ] Is this the simplest solution?
- [ ] Did I over-engineer? (YAGNI violations)
- [ ] Could it be 50% shorter doing the same thing?
```

---

## Trust Model (Supervision Calibration)

**Source**: Addy Osmani, "Beyond Vibe Coding" (2026)

**Rule**: Amount of supervision depends on three factors: Familiarity, Trust, Control.

```
FAMILIARITY ──> TRUST ──> CONTROL

Do you know the      Has it delivered    How much supervision
technology/task?     well before?        does it need?
```

### Control Levels

| Level | When to Apply | What It Means |
|-------|---------------|---------------|
| HIGH | New technology, critical code (auth, payments), first feature of a type | Review each step, frequent checkpoints, pair review |
| MEDIUM | Known technology, established patterns | Review at main checkpoints, mandatory tests |
| LOW | Features similar to previous ones, high confidence | Final review, trust automated tests |

### Decision Matrix

| Situation | Familiarity | Control |
|-----------|-------------|---------|
| First auth feature | Low | HIGH |
| Second auth feature (same pattern) | High | MEDIUM |
| Tenth similar CRUD | High | LOW |
| New external API | Low | HIGH |
| Refactor of known code | High | LOW |
| Feature with security requirements | Variable | HIGH always |

### The 70% Problem Awareness

> "AI helps you get to 70% fast, but the remaining 30% is where the real complexity is."

**Implication for Trust Model:**
- Initial 70% can have LOW CONTROL
- Final 30% (edge cases, security, integration) needs HIGH CONTROL
- Adjust supervision as feature progresses

---

## Workflow Evolution (Governance)

**IMPERATIVE Rule**: No new functionality, trend, tool, or refactor can be implemented without exhaustive prior analysis.

### Fundamental Principle

> *"The workflow evolves deliberately, not by fashion. Every addition must demonstrate value before implementation."*

### Mandatory Validation Process

Before implementing ANY workflow change:

```
EVOLUTION VALIDATION GATE

1. ANALYSIS ──> 2. EVALUATION ──> 3. PROOF ──> 4. DECISION

NO validation = Don't implement
WITH validation = Proceed with implementation
```

### Evaluation Scoring

| Criterion | Weight | Score (1-5) |
|----------|--------|-------------|
| **Real Problem** - Does it solve a problem we have? | 30% | |
| **Maturity** - Is it proven in production by others? | 20% | |
| **Compatibility** - Does it integrate with our system? | 20% | |
| **Complexity** - Does benefit justify complexity? | 15% | |
| **Maintainability** - Can we maintain it long-term? | 15% | |

**Minimum threshold**: Score >= 3.5 to proceed

### Prohibitions

- Implementing trends "because they're fashionable"
- Adding tools without concrete use case
- Major refactors without impact analysis
- Adopting technology just because "everyone uses it"
- Implementing features "just in case we need them"

---

## Permissions and Restrictions

### Reading

Each role can read:
- Their own role markdown (`.md`)
- All project rules (`rules/*.md`)
- Workflow YAMLs
- Feature states (`50_state.md`)
- Code relevant to their role

### Writing

Each role can only write to:
- Their assigned code area
- Their section of `50_state.md`
- Report/tasks files assigned to their role

**IMPORTANT**: Only **Planner** can modify project rules (with justification in `DECISIONS.md`).

---

## Global Prohibitions

All roles are **prohibited** from:

1. Committing code without tests
2. Pushing with failing tests
3. Changing code without documenting why
4. Skipping the workflow
5. Implementing features not defined by Planner
6. Changing API contracts without consensus
7. Force pushing to `main` or `develop`
8. Committing generated files (`.env`, `node_modules/`, `vendor/`, etc.)

---

## Conflict Management

### Git Conflicts

1. `git pull` before working
2. If conflict: stash, pull, stash pop, resolve manually
3. Never use `--force` without consulting

### Design Conflicts

1. Report in `50_state.md` with `BLOCKED` status
2. Planner makes the decision
3. Decision documented in `DECISIONS.md`

---

## Documentation File Locations

| Type | Location | Description |
|------|----------|-------------|
| Framework Rules | `plugins/.../core/rules/` | These rules (don't modify) |
| Project Rules | `.ai/extensions/rules/` | Project-specific rules |
| Role Definitions | `plugins/.../core/roles/` | Base role definitions |
| Workflows | `.ai/extensions/workflows/` | Project workflows |
| Trust Model | `.ai/extensions/trust/` | Project trust configuration |
| Features | `.ai/project/features/` | Feature specifications |
| Scripts | `.ai/extensions/scripts/` | Utility scripts |

---

**Note**: These rules are part of the Multi-Agent Workflow Plugin and should not be modified per-project. For project-specific rules, create files in `.ai/extensions/rules/`.
