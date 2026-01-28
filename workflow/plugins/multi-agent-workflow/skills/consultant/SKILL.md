# AI Consultant Skill

Intelligent project analysis to recommend the best workflow and configuration.

## What This Skill Does

- Detects project type and frameworks (PHP/Symfony, Node/React, Python, etc.)
- Analyzes codebase structure and complexity
- Recommends appropriate workflow template
- Identifies existing code patterns to follow
- Generates project-specific configuration

## When to Use

- **New project setup**: Initialize workflow for a new codebase
- **First time with workflow**: Get recommendations for existing project
- **Switching workflows**: Determine if different workflow fits better
- **Onboarding**: Understand project structure quickly

## How to Use

### Via Slash Command

```
/multi-agent-workflow:consult
/multi-agent-workflow:consult --interactive
```

### Analysis Process

The consultant performs:

1. **Framework Detection**
   ```
   Checking for: composer.json, package.json, requirements.txt, Cargo.toml
   Detected: Symfony 6.x + React 18.x
   ```

2. **Structure Analysis**
   ```
   Backend structure:
     src/Domain/ ✓ (DDD detected)
     src/Application/ ✓
     src/Infrastructure/ ✓

   Frontend structure:
     src/components/ ✓
     src/services/ ✓
     src/hooks/ ✓
   ```

3. **Complexity Assessment**
   ```
   Files: 150
   Lines of code: 25,000
   Test coverage: 72%
   Complexity: MEDIUM
   ```

4. **Workflow Recommendation**
   ```
   Recommended: default workflow

   Reasoning:
   - DDD structure already in place
   - Both backend and frontend present
   - Medium complexity suits parallel development
   - Test infrastructure exists
   ```

## Workflow Recommendations

### `default` - Recommended when:
- Project has both backend and frontend
- Medium to large features
- Team wants parallel development
- ~1-2 weeks of work

### `task-breakdown` - Recommended when:
- Complex, multi-week features
- Need detailed estimation
- Distributed team
- High documentation requirements
- New team members

### `implementation-only` - Recommended when:
- Planning already done externally
- Simple bug fixes
- Small, well-defined changes

## Output: Project Context File

The consultant generates `.ai/project/context.md`:

```markdown
# Project Context

## Detected Stack
- **Backend**: Symfony 6.4, PHP 8.2
- **Frontend**: React 18, TypeScript 5
- **Database**: PostgreSQL 15
- **Testing**: PHPUnit, Jest, Cypress

## Architecture
- **Pattern**: Domain-Driven Design (DDD)
- **API Style**: REST
- **Auth**: JWT

## Code Patterns to Follow

### Backend
- Entities: `src/Domain/Entity/*.php`
- Use Cases: `src/Application/UseCase/*.php`
- Controllers: `src/Infrastructure/HTTP/Controller/*.php`
- Tests: `tests/Unit/`, `tests/Integration/`

### Frontend
- Components: `src/components/*.tsx`
- Services: `src/services/*.ts`
- Hooks: `src/hooks/*.ts`
- Tests: `src/__tests__/`

## Recommended Workflow
`default` - Standard 4-role parallel workflow

## Team Configuration
- 1 Planner instance
- 1-2 Backend instances (can parallelize)
- 1-2 Frontend instances
- 1 QA instance
```

## Interactive Mode

With `--interactive`, the consultant asks questions:

1. What type of feature are you building?
2. How complex is this feature?
3. Do you have existing code patterns to follow?
4. What's your team size?
5. Do you need detailed estimation?

Based on answers, provides tailored recommendations.
