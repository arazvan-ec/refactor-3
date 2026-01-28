# Project Rules - [PROJECT_NAME]

**Project**: [PROJECT_NAME]
**Last Updated**: 2026-01-28
**Version**: 1.0

---

## Purpose

This file contains **project-specific rules** that extend the framework rules. These rules are tailored to this project's stack, conventions, and requirements.

**Base Framework Rules**: See `plugins/multi-agent-workflow/core/rules/framework_rules.md`

---

## Project Stack

### Backend
- **Language**: [PHP 8.x / Node.js / Python / etc.]
- **Framework**: [Symfony / Laravel / Express / Django / etc.]
- **Database**: [PostgreSQL / MySQL / MongoDB / etc.]

### Frontend
- **Language**: TypeScript
- **Framework**: [React / Vue / Angular / etc.]
- **State Management**: [Redux / Zustand / Pinia / etc.]

### Infrastructure
- **Container**: Docker
- **CI/CD**: [GitHub Actions / GitLab CI / etc.]
- **Deployment**: [Kubernetes / AWS / Vercel / etc.]

---

## Testing Requirements

### Backend

- **Minimum Coverage**: 80%
- **Required Tests**:
  - Unit tests for Use Cases
  - Unit tests for Domain entities
  - Integration tests for Repositories
  - Integration tests for API endpoints

### Frontend

- **Minimum Coverage**: 70%
- **Required Tests**:
  - Unit tests for critical components
  - Integration tests for important flows
  - E2E tests for main use cases

### Test Execution

- **All tests** must pass before `COMPLETED`
- **CI/CD** must be green
- **Don't** push if tests fail locally

---

## Code Style

### Backend (PHP)

- **Standard**: PSR-12
- **Linter**: PHP_CodeSniffer
- **Formatter**: PHP CS Fixer
- Run before commit:
  ```bash
  ./vendor/bin/php-cs-fixer fix
  ```

### Frontend (TypeScript/React)

- **Standard**: ESLint + Prettier
- **Config**: Use standard React config
- Run before commit:
  ```bash
  npm run lint:fix
  npm run format
  ```

---

## Git Workflow

### Branching Strategy

- **main**: Production (only release merges)
- **develop**: Active development
- **feature/[feature-name]**: New features
- **bugfix/[bug-name]**: Bug fixes
- **hotfix/[hotfix-name]**: Urgent production fixes

### Commit Messages

Format:
```
<type>(<scope>): <subject>

<body>

<footer>
```

Types:
- `feat`: New functionality
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code format (no logic change)
- `refactor`: Refactoring
- `test`: Tests
- `chore`: Maintenance tasks

Example:
```
feat(user): Add user registration endpoint

- Implement CreateUserUseCase
- Add UserRepository
- Add POST /api/users endpoint
- Add unit and integration tests

Closes #123
```

---

## Quality Metrics

### Backend

- **Test Coverage**: > 80%
- **Complexity**: Cyclomatic < 10 per function
- **Duplication**: < 5%
- **Technical Debt**: Low (SonarQube A rating)

### Frontend

- **Test Coverage**: > 70%
- **Bundle Size**: < 500KB (gzipped)
- **Lighthouse Score**: > 90
- **Accessibility**: WCAG 2.1 AA

---

## Security Requirements

### All roles must:

1. **No secrets commits**
   - No `.env` with real credentials
   - No API keys in code
   - Use environment variables

2. **Validate inputs**
   - Backend: validate all API inputs
   - Frontend: validate all user inputs (double validation with backend)

3. **Prevent common vulnerabilities**
   - No SQL injection
   - No XSS (Cross-Site Scripting)
   - No CSRF (Cross-Site Request Forgery)
   - Don't expose sensitive info in logs

4. **HTTPS only**
   - Production always uses HTTPS
   - Cookies with `Secure` and `HttpOnly`

---

## Dependency Management

### Backend (PHP/Composer)

- Use Composer for dependencies
- Specific versions (no `^` or `~` in prod)
- Update dependencies regularly
- Check vulnerabilities with `composer audit`

### Frontend (NPM/Yarn)

- Use npm or yarn (be consistent)
- Lock file (`package-lock.json` or `yarn.lock`) committed
- Update dependencies regularly
- Check vulnerabilities with `npm audit`

---

## Definition of Done

A feature is **DONE** when:

- Code implemented according to `FEATURE_X.md`
- Tests written and passing (coverage > minimum)
- Code review done (by QA)
- Documentation updated
- CI/CD green
- QA approves (`APPROVED` in `50_state.md`)
- Deployed to staging
- Planner gives final approval

---

## Mandatory Documentation

### Every feature must have:

1. **FEATURE_X.md** - Feature definition (created by Planner)
2. **50_state.md** - State updated by each role
3. **30_tasks.md** - Task breakdown (created by Planner)
4. **DECISIONS.md** - Important decisions (updated by Planner)

### All code must have:

1. **Tests** - Unit, integration, or E2E as appropriate
2. **Inline documentation** (PHPDoc, JSDoc)
3. **README** if it's a complex module

---

## DDD Rules (If Applicable)

If this project uses Domain-Driven Design, see:
- `.ai/extensions/rules/ddd_rules.md`

---

## Custom Rules

### [Add project-specific rules here]

Example:
```markdown
### API Versioning
- All API endpoints must be versioned: `/api/v1/...`
- Breaking changes require new version
- Old versions supported for 6 months
```

---

**Last update**: 2026-01-28
**Updated by**: Planner
**Recent changes**: Initial project rules setup
**Next review**: Monthly or when necessary
