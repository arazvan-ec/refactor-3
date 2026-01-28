# Decision Log: Scalable Aggregation Pipeline

## Decision 1: Two-Phase Aggregation (aggregate → resolve)

**Date**: 2026-01-28
**Status**: ACCEPTED

### Context
The original `EditorialOrchestrator` mixed async HTTP calls with synchronous processing, making it impossible to parallelize independent operations.

### Decision
Implement two-phase execution:
1. **Phase 1 (aggregate)**: Start all async operations, return promises
2. **Phase 2 (resolve)**: Wait for promises, return final data

### Why
- Enables true parallel execution of HTTP requests
- Dependencies can be resolved in correct order during Phase 2
- Each aggregator remains simple and focused

### Trade-offs
| Pro | Con |
|-----|-----|
| Better parallelization | Slightly more complex mental model |
| Clear separation of concerns | Two methods instead of one |
| Testable phases independently | Need to understand promise mechanics |

### Revisit Conditions
- If GuzzleHttp changes promise handling
- If we need streaming responses instead of batch

---

## Decision 2: Topological Sort for Dependencies

**Date**: 2026-01-28
**Status**: ACCEPTED

### Context
Some aggregators depend on data from other aggregators (e.g., InsertedNewsAggregator needs signature fetching logic).

### Decision
Use topological sort during the resolve phase to ensure dependencies are resolved before dependents.

### Why
- Ensures correct execution order
- Allows aggregators to declare dependencies declaratively
- Pipeline automatically handles resolution order

### Trade-offs
| Pro | Con |
|-----|-----|
| Automatic dependency resolution | Circular dependencies cause failure |
| Declarative dependencies | Small overhead for sorting |
| No manual ordering needed | Requires understanding graph theory |

### Revisit Conditions
- If circular dependencies become common (consider refactoring)
- If performance becomes an issue with many aggregators

---

## Decision 3: Priority Values (CRITICAL=100, HIGH=90, NORMAL=80, LOW=70)

**Date**: 2026-01-28
**Status**: ACCEPTED

### Context
Need to control which aggregators start their async operations first.

### Decision
Use priority constants with gaps of 10:
- `CRITICAL (100)`: Core data with no dependencies
- `HIGH (90)`: Important data depending on editorial/section
- `NORMAL (80)`: Standard multimedia aggregators
- `LOW (70)`: Aggregators depending on other aggregators

### Why
- Gaps of 10 allow inserting new aggregators between existing ones
- Constants document intent better than magic numbers
- Higher values first is intuitive (more important = higher)

### Trade-offs
| Pro | Con |
|-----|-----|
| Self-documenting code | Arbitrary scale (could be 1-4) |
| Room for future insertions | Need to document meaning |
| Matches Symfony priority conventions | |

### Revisit Conditions
- If we need more than 4 levels
- If the scale becomes confusing

---

## Decision 4: Tag-Based Auto-Registration via Compiler Pass

**Date**: 2026-01-28
**Status**: ACCEPTED

### Context
Adding a new aggregator should be as simple as possible (Open/Closed Principle).

### Decision
Use Symfony service tags (`app.aggregator`, `app.response_transformer`) with Compiler Passes for auto-registration.

### Why
- No code changes needed in pipeline when adding aggregators
- Configuration is declarative (YAML)
- Standard Symfony pattern

### Trade-offs
| Pro | Con |
|-----|-----|
| Zero-code registration | Magic (registration not visible in code) |
| Follows Symfony conventions | Requires understanding Compiler Passes |
| Easy to enable/disable via YAML | |

### Revisit Conditions
- If we need dynamic registration at runtime
- If service tags become a performance concern

---

## Decision 5: Separate SignatureFetcherTrait

**Date**: 2026-01-28
**Status**: ACCEPTED (Post-comprehension review)

### Context
`InsertedNewsAggregator` and `RecommendedEditorialsAggregator` both had identical `fetchSignature()` methods.

### Decision
Extract shared logic into `SignatureFetcherTrait`.

### Why
- DRY: Eliminates ~40 lines of duplicate code
- Single point of maintenance for signature fetching
- Consistent error handling across aggregators

### Trade-offs
| Pro | Con |
|-----|-----|
| No code duplication | Trait adds abstraction |
| Consistent behavior | Abstract methods add boilerplate |
| Easy to test shared logic | |

### Revisit Conditions
- If signature fetching logic diverges between aggregators
- If composition (service injection) would be clearer

---

## Decision 6: EditorialTypeConstants for Twitter Logic

**Date**: 2026-01-28
**Status**: ACCEPTED (Post-comprehension review)

### Context
`TWITTER_TYPES` constant was duplicated in `SignaturesAggregator` and the original orchestrator.

### Decision
Create `EditorialTypeConstants` class with centralized editorial type classifications.

### Why
- Single source of truth for editorial type behavior
- Self-documenting with `shouldIncludeTwitter()` helper
- Easily extendable for future type-based logic

### Trade-offs
| Pro | Con |
|-----|-----|
| Centralized constants | Extra class file |
| Helper method is expressive | Indirection to find constant |
| Easy to extend | |

### Revisit Conditions
- If editorial types become dynamic (database-driven)
- If more type-based logic needs centralization

---

**Last Updated**: 2026-01-28
**Reviewed by**: Comprehension Guardian (workflows:comprehension)
