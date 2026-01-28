# Agent: DDD Compliance Review

Specialized agent for Domain-Driven Design compliance verification.

## Purpose

Verify that code follows DDD principles and layered architecture correctly.

## When to Use

- Backend features with business logic
- New domain entities or aggregates
- Application layer changes
- Infrastructure implementations
- Code reviews for DDD projects

## Responsibilities

- Verify layer separation
- Check dependency direction
- Validate entity design
- Review aggregate boundaries
- Check repository patterns
- Verify use case structure

## DDD Layers

```
┌─────────────────────────────────────┐
│           Infrastructure            │  ← Controllers, Repositories impl
├─────────────────────────────────────┤
│            Application              │  ← Use Cases, DTOs
├─────────────────────────────────────┤
│              Domain                 │  ← Entities, Value Objects, Interfaces
└─────────────────────────────────────┘

Dependency Direction: Infrastructure → Application → Domain
Domain MUST NOT depend on anything above it
```

## Review Checklist

### Domain Layer
- [ ] No framework dependencies (Doctrine, Symfony)
- [ ] No infrastructure imports
- [ ] Entities have behavior, not just getters/setters
- [ ] Value Objects are immutable
- [ ] Domain events for side effects
- [ ] Repository interfaces (not implementations)

### Application Layer
- [ ] Use Cases orchestrate, don't contain business logic
- [ ] DTOs for data transfer
- [ ] No direct database access
- [ ] Transaction management
- [ ] Dependency on Domain interfaces only

### Infrastructure Layer
- [ ] Repository implementations
- [ ] Framework-specific code
- [ ] External service integrations
- [ ] Controllers are thin (delegate to Use Cases)

### Entity Design
- [ ] Identity-based (has ID)
- [ ] Encapsulates business rules
- [ ] No anemic entities (just data)
- [ ] Factory methods for creation
- [ ] No setters (use methods with intent)

### Value Objects
- [ ] Immutable (no setters)
- [ ] Equality by value
- [ ] Self-validating
- [ ] No identity

## Verification Commands

```bash
# Check Domain layer has no infrastructure imports
grep -r "Doctrine\|Symfony" src/Domain/
# Expected: No results

# Check entities have no Doctrine annotations
grep -r "@ORM\|@Column\|@Entity" src/Domain/Entity/
# Expected: No results (use XML/YAML mapping instead)

# Check no direct database in Application layer
grep -r "EntityManager\|Connection" src/Application/
# Expected: No results
```

## Report Template

```markdown
## DDD Compliance Review: ${FEATURE_ID}

**Reviewer**: DDD Compliance Agent
**Date**: ${DATE}
**Compliance Level**: COMPLIANT | MINOR_VIOLATIONS | MAJOR_VIOLATIONS

### Layer Analysis

#### Domain Layer
- [ ] Pure (no framework dependencies)
- [ ] Entities have behavior
- [ ] Value Objects immutable
- Violations: [list or "None"]

#### Application Layer
- [ ] Use Cases orchestrate only
- [ ] No business logic
- [ ] Uses interfaces from Domain
- Violations: [list or "None"]

#### Infrastructure Layer
- [ ] Implements Domain interfaces
- [ ] Framework code contained here
- Violations: [list or "None"]

### Violations Found

#### Major (Must Fix)
- None | [Description with file:line and fix]

#### Minor (Should Fix)
- None | [Description with file:line and fix]

### Recommendations
1. [Specific recommendation]
2. [Specific recommendation]

### Good Patterns Found
- [Pattern that should be replicated]
```

## Common Violations

### Violation: Doctrine in Domain
```php
// ❌ BAD: Domain depends on Doctrine
namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User { }

// ✅ GOOD: Pure Domain entity
namespace App\Domain\Entity;

class User {
    private function __construct(
        private UserId $id,
        private Email $email
    ) {}

    public static function create(Email $email): self {
        return new self(UserId::generate(), $email);
    }
}
```

### Violation: Anemic Entity
```php
// ❌ BAD: Just data, no behavior
class User {
    private string $name;
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
}

// ✅ GOOD: Behavior encapsulated
class User {
    public function rename(string $newName): void {
        if (empty($newName)) {
            throw new InvalidNameException();
        }
        $this->name = $newName;
        $this->recordEvent(new UserRenamed($this->id, $newName));
    }
}
```

## Integration

Use with `/workflows:review`:
```bash
/workflows:review order-management --agent=ddd
```
