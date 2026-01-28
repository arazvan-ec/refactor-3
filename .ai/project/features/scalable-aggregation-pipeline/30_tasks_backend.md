# Backend Tasks: Scalable Aggregation Pipeline

## Task Overview

| ID | Task | Priority | Effort | Dependencies |
|----|------|----------|--------|--------------|
| BE-001 | Create AggregationContext | P0 | 1h | - |
| BE-002 | Create AsyncAggregatorInterface | P0 | 30m | - |
| BE-003 | Create AbstractAsyncAggregator | P0 | 1h | BE-002 |
| BE-004 | Create AggregatorPipeline | P0 | 2h | BE-001, BE-002 |
| BE-005 | Create TransformationContext | P0 | 30m | BE-001 |
| BE-006 | Create ResponseTransformerInterface | P0 | 30m | - |
| BE-007 | Create TransformerPipeline | P0 | 1h | BE-005, BE-006 |
| BE-008 | Create TagsAggregator | P1 | 1h | BE-003 |
| BE-009 | Create SignaturesAggregator | P1 | 1h | BE-003 |
| BE-010 | Create MultimediaAggregator | P1 | 1h | BE-003 |
| BE-011 | Create MultimediaOpeningAggregator | P1 | 1h | BE-003 |
| BE-012 | Create PhotosFromBodyAggregator | P1 | 1h | BE-003 |
| BE-013 | Create MembershipLinksAggregator | P1 | 1.5h | BE-003 |
| BE-014 | Create InsertedNewsAggregator | P1 | 2h | BE-003, BE-009 |
| BE-015 | Create RecommendedEditorialsAggregator | P1 | 2h | BE-003, BE-009 |
| BE-016 | Create CommentsAggregator | P1 | 30m | BE-003 |
| BE-017 | Create Compiler Passes | P0 | 1h | BE-004, BE-007 |
| BE-018 | Create EditorialOrchestratorScalable | P0 | 2h | All above |
| BE-019 | Update Kernel.php | P0 | 15m | BE-017 |
| BE-020 | Create services configuration | P0 | 1h | All above |

**Total Estimated Effort**: ~18 hours

---

## BE-001: Create AggregationContext

**Priority**: P0 - Critical Path
**Reference**: `src/Application/Aggregator/AggregationContext.php` (already started)
**Methodology**: TDD

### Requirements
- Immutable editorial and section storage
- Mutable shared data for inter-aggregator communication
- Resolved data storage per aggregator key
- Pending promises tracking

### Tests to Write FIRST
```php
// tests/Application/Aggregator/AggregationContextTest.php
- test_can_be_created_with_editorial_and_section()
- test_get_editorial_returns_editorial()
- test_get_section_returns_section()
- test_shared_data_can_be_set_and_retrieved()
- test_resolved_data_can_be_set_and_retrieved()
- test_pending_promises_can_be_added_and_retrieved()
```

### Acceptance Criteria
- [ ] All tests pass
- [ ] PHPStan Level 9 passes
- [ ] Context is immutable for source data

### Verification
```bash
./bin/phpunit tests/Application/Aggregator/AggregationContextTest.php
./vendor/bin/phpstan analyse src/Application/Aggregator/AggregationContext.php
```

---

## BE-002: Create AsyncAggregatorInterface

**Priority**: P0 - Critical Path
**Reference**: `src/Application/Aggregator/AsyncAggregatorInterface.php` (already started)
**Methodology**: Contract-first

### Requirements
- Define getKey(), supports(), aggregate(), resolve()
- Define getPriority(), getDependencies()
- Full PHPDoc with types

### Acceptance Criteria
- [ ] Interface defined with all methods
- [ ] PHPDoc complete with @param and @return
- [ ] PHPStan Level 9 passes

---

## BE-003: Create AbstractAsyncAggregator

**Priority**: P0 - Critical Path
**Reference**: `src/Application/Aggregator/AbstractAsyncAggregator.php` (already started)
**Methodology**: TDD

### Requirements
- Implement default getPriority() returning 0
- Implement default getDependencies() returning []
- Implement default supports() returning true
- Provide resolvePromises() helper
- Provide safeExecute() helper

### Tests to Write FIRST
```php
// tests/Application/Aggregator/AbstractAsyncAggregatorTest.php
- test_default_priority_is_zero()
- test_default_dependencies_is_empty()
- test_default_supports_returns_true()
- test_resolve_promises_returns_fulfilled_values()
- test_resolve_promises_logs_rejected_promises()
- test_safe_execute_returns_default_on_exception()
```

### Acceptance Criteria
- [ ] All tests pass
- [ ] Helper methods work correctly
- [ ] Logging configured

---

## BE-004: Create AggregatorPipeline

**Priority**: P0 - Critical Path
**Reference**: `src/Application/Pipeline/AggregatorPipeline.php` (already started)
**Methodology**: TDD

### Requirements
- Register aggregators with addAggregator()
- Sort by priority and dependencies (topological sort)
- Two-phase execution: aggregate() then resolve()
- Handle dependencies correctly
- executeOnly() for selective execution

### Tests to Write FIRST
```php
// tests/Application/Pipeline/AggregatorPipelineTest.php
- test_can_add_aggregator()
- test_execute_calls_all_aggregators()
- test_execute_respects_priority_order()
- test_execute_respects_dependencies()
- test_execute_only_runs_specified_aggregators()
- test_skips_unsupported_aggregators()
- test_handles_aggregator_exceptions_gracefully()
- test_topological_sort_handles_complex_dependencies()
```

### Acceptance Criteria
- [ ] All tests pass
- [ ] Dependency resolution correct
- [ ] No circular dependency issues
- [ ] Errors don't break pipeline

---

## BE-005: Create TransformationContext

**Priority**: P0 - Critical Path
**File**: `src/Application/Transformer/TransformationContext.php`
**Methodology**: TDD

### Requirements
- Hold reference to AggregationContext
- Provide access to aggregated data
- Mutable response building

### Tests to Write FIRST
```php
// tests/Application/Transformer/TransformationContextTest.php
- test_can_access_aggregation_context()
- test_can_get_aggregated_data_by_key()
- test_can_set_response_field()
- test_can_merge_response()
- test_get_response_returns_all_fields()
```

### Acceptance Criteria
- [ ] All tests pass
- [ ] Provides clean API for transformers

---

## BE-006: Create ResponseTransformerInterface

**Priority**: P0 - Critical Path
**File**: `src/Application/Transformer/ResponseTransformerInterface.php`
**Methodology**: Contract-first

### Requirements
- Define getKey(), supports(), transform(), getPriority()
- transform() modifies context, returns void

### Acceptance Criteria
- [ ] Interface complete
- [ ] PHPDoc complete

---

## BE-007: Create TransformerPipeline

**Priority**: P0 - Critical Path
**File**: `src/Application/Pipeline/TransformerPipeline.php`
**Methodology**: TDD

### Requirements
- Register transformers
- Sort by priority
- Execute all supported transformers
- Return final response

### Tests to Write FIRST
```php
// tests/Application/Pipeline/TransformerPipelineTest.php
- test_can_add_transformer()
- test_transform_calls_all_transformers()
- test_transform_respects_priority()
- test_skips_unsupported_transformers()
- test_returns_combined_response()
```

### Acceptance Criteria
- [ ] All tests pass
- [ ] Priority respected
- [ ] Response correctly combined

---

## BE-008: Create TagsAggregator

**Priority**: P1
**File**: `src/Application/Aggregator/Impl/TagsAggregator.php`
**Reference**: EditorialOrchestrator lines 222-230
**Methodology**: TDD

### Requirements
- Key: 'tags'
- Priority: 100
- No dependencies
- Fetch all tags from editorial

### Tests to Write FIRST
```php
// tests/Application/Aggregator/Impl/TagsAggregatorTest.php
- test_get_key_returns_tags()
- test_aggregate_fetches_all_tags()
- test_resolve_returns_tag_array()
- test_handles_tag_fetch_errors_gracefully()
```

### Acceptance Criteria
- [ ] All tests pass
- [ ] Same output as original code

---

## BE-009: Create SignaturesAggregator

**Priority**: P1
**File**: `src/Application/Aggregator/Impl/SignaturesAggregator.php`
**Reference**: EditorialOrchestrator lines 243-253
**Methodology**: TDD

### Requirements
- Key: 'signatures'
- Priority: 90
- No dependencies (section from context)
- Transform using JournalistsDataTransformer
- Handle hasTwitter based on editorial type

### Tests to Write FIRST
```php
// tests/Application/Aggregator/Impl/SignaturesAggregatorTest.php
- test_get_key_returns_signatures()
- test_aggregate_fetches_all_signatures()
- test_resolve_includes_twitter_for_blog_type()
- test_resolve_excludes_twitter_for_other_types()
- test_handles_journalist_not_found()
```

---

## BE-010 to BE-016: Remaining Aggregators

Similar structure for:
- **BE-010**: MultimediaAggregator (lines 212-219)
- **BE-011**: MultimediaOpeningAggregator (lines 442-457)
- **BE-012**: PhotosFromBodyAggregator (lines 306-323)
- **BE-013**: MembershipLinksAggregator (lines 373-419)
- **BE-014**: InsertedNewsAggregator (lines 125-161)
- **BE-015**: RecommendedEditorialsAggregator (lines 163-207)
- **BE-016**: CommentsAggregator (lines 238-240)

Each follows same TDD pattern with specific tests.

---

## BE-017: Create Compiler Passes

**Priority**: P0
**Files**:
- `src/DependencyInjection/Compiler/AggregatorPipelineCompiler.php`
- `src/DependencyInjection/Compiler/TransformerPipelineCompiler.php`
**Reference**: `src/DependencyInjection/Compiler/BodyDataTransformerCompiler.php`
**Methodology**: TDD

### Requirements
- Find services tagged with 'app.aggregator'
- Add to AggregatorPipeline via addAggregator()
- Same for 'app.response_transformer'

### Tests to Write FIRST
```php
// tests/DependencyInjection/Compiler/AggregatorPipelineCompilerTest.php
- test_registers_tagged_aggregators()
- test_passes_aggregator_to_pipeline()
```

---

## BE-018: Create EditorialOrchestratorScalable

**Priority**: P0
**File**: `src/Orchestrator/Chain/EditorialOrchestratorScalable.php`
**Methodology**: TDD

### Requirements
- Use AggregatorPipeline for data fetching
- Use TransformerPipeline for response building
- Keep same interface (EditorialOrchestratorInterface)
- Produce identical JSON output

### Tests to Write FIRST
```php
// tests/Orchestrator/Chain/EditorialOrchestratorScalableTest.php
- test_implements_orchestrator_interface()
- test_returns_legacy_for_null_source_editorial()
- test_throws_not_published_for_invisible()
- test_produces_same_output_as_original()
```

### Acceptance Criteria
- [ ] Same JSON output as EditorialOrchestrator
- [ ] Uses pipelines
- [ ] < 100 lines of code

---

## BE-019: Update Kernel.php

**Priority**: P0
**File**: `src/Kernel.php`
**Methodology**: Direct

### Requirements
- Add AggregatorPipelineCompiler
- Add TransformerPipelineCompiler

### Verification
```bash
./bin/console debug:container --tag=app.aggregator
./bin/console debug:container --tag=app.response_transformer
```

---

## BE-020: Create Services Configuration

**Priority**: P0
**File**: `config/packages/aggregator_pipeline.yaml`
**Methodology**: Direct

### Requirements
- Configure all aggregators with tags
- Configure all transformers with tags
- Set priorities correctly

---

## Definition of Done (All Tasks)

- [ ] Tests written FIRST and passing
- [ ] PHPStan Level 9 passes
- [ ] Code follows SOLID principles
- [ ] No code duplication
- [ ] Documented in code

---

**Status**: READY FOR IMPLEMENTATION
**Reviewed by**: Planner Agent
**Date**: 2026-01-28
