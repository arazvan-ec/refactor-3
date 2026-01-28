# Dependencies: Scalable Aggregation Pipeline

## 1. External Dependencies (No Changes Required)

### Microservice Clients (ec/*)

| Package | Version | Used By |
|---------|---------|---------|
| `ec/editorial-client` | ^2.2 | All aggregators (source data) |
| `ec/section-client` | ^3.0 | Context, SignaturesAggregator |
| `ec/tag-client` | ^4.0 | TagsAggregator |
| `ec/journalist-client` | ^5.1 | SignaturesAggregator |
| `ec/multimedia-client` | ^5.0 | MultimediaAggregator, PhotosFromBodyAggregator |
| `ec/membership-client` | ^1.5 | MembershipLinksAggregator |
| `ec/widget-client` | ^2.0 | MultimediaOpeningAggregator |

### HTTP & Async

| Package | Version | Purpose |
|---------|---------|---------|
| `guzzlehttp/promises` | ^2.0 | Promise handling in aggregators |
| `php-http/httplug` | ^2.0 | HTTP client abstraction |
| `php-http/guzzle7-adapter` | ^1.0 | Guzzle integration |

### Framework

| Package | Version | Purpose |
|---------|---------|---------|
| `symfony/framework-bundle` | 6.4.* | Core framework |
| `symfony/dependency-injection` | 6.4.* | DI container, Compiler Passes |
| `psr/log` | ^3.0 | Logging interface |
| `monolog/monolog` | ^3.0 | Logger implementation |

## 2. Internal Dependencies (Existing Code)

### Data Transformers

| Class | Package | Used By |
|-------|---------|---------|
| `BodyDataTransformer` | App | BodyTransformer |
| `JournalistsDataTransformer` | App | SignaturesAggregator |
| `MultimediaDataTransformer` | App | MultimediaTransformer |
| `StandfirstDataTransformer` | App | StandfirstTransformer |
| `RecommendedEditorialsDataTransformer` | App | RecommendedEditorialsTransformer |
| `DetailsAppsDataTransformer` | App | EditorialBaseTransformer |
| `MediaDataTransformerHandler` | App | MultimediaTransformer |

### Services

| Class | Purpose | Used By |
|-------|---------|---------|
| `MultimediaOrchestratorHandler` | Multimedia type routing | MultimediaOpeningAggregator |
| `Thumbor` | Image processing | MultimediaProcessor |
| `QueryLegacyClient` | Legacy API | CommentsAggregator |

### Existing Interfaces (To Reuse)

| Interface | Purpose |
|-----------|---------|
| `EditorialOrchestratorInterface` | Orchestrator contract |
| `BodyElementDataTransformer` | Body element transformation |
| `MediaDataTransformer` | Media transformation |

## 3. New Internal Dependencies (To Create)

### Core Infrastructure

```
src/Application/Aggregator/
├── AsyncAggregatorInterface.php      # Contract for aggregators
├── AbstractAsyncAggregator.php       # Base implementation
└── AggregationContext.php            # Context object

src/Application/Transformer/
├── ResponseTransformerInterface.php  # Contract for transformers
├── AbstractResponseTransformer.php   # Base implementation
└── TransformationContext.php         # Context object

src/Application/Pipeline/
├── AggregatorPipeline.php            # Aggregation coordinator
└── TransformerPipeline.php           # Transformation coordinator
```

### Aggregator Implementations

```
src/Application/Aggregator/Impl/
├── TagsAggregator.php
├── SignaturesAggregator.php
├── MultimediaAggregator.php
├── MultimediaOpeningAggregator.php
├── PhotosFromBodyAggregator.php
├── MembershipLinksAggregator.php
├── InsertedNewsAggregator.php
├── RecommendedEditorialsAggregator.php
└── CommentsAggregator.php
```

### Transformer Implementations

```
src/Application/Transformer/Impl/
├── EditorialBaseTransformer.php
├── SignaturesTransformer.php
├── BodyTransformer.php
├── MultimediaTransformer.php
├── StandfirstTransformer.php
└── RecommendedEditorialsTransformer.php
```

### Compiler Passes

```
src/DependencyInjection/Compiler/
├── AggregatorPipelineCompiler.php
└── TransformerPipelineCompiler.php
```

## 4. Dependency Graph

```
┌─────────────────────────────────────────────────────────────────┐
│                    EditorialOrchestratorScalable                 │
└─────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              ▼                               ▼
┌──────────────────────┐          ┌──────────────────────┐
│  AggregatorPipeline  │          │ TransformerPipeline  │
└──────────┬───────────┘          └──────────┬───────────┘
           │                                  │
           ▼                                  ▼
┌──────────────────────┐          ┌──────────────────────┐
│ AsyncAggregator-     │          │ ResponseTransformer- │
│ Interface            │          │ Interface            │
└──────────┬───────────┘          └──────────┬───────────┘
           │                                  │
     ┌─────┴─────┐                     ┌─────┴─────┐
     ▼           ▼                     ▼           ▼
┌─────────┐ ┌─────────┐          ┌─────────┐ ┌─────────┐
│  Tags   │ │Signat-  │          │Editorial│ │  Body   │
│Aggregat.│ │ures     │          │Transf.  │ │Transf.  │
└────┬────┘ └────┬────┘          └────┬────┘ └────┬────┘
     │           │                    │           │
     ▼           ▼                    ▼           ▼
┌─────────┐ ┌─────────┐          ┌─────────┐ ┌─────────┐
│QueryTag │ │QueryJour│          │Details  │ │BodyData│
│Client   │ │nalist   │          │AppsTrans│ │Transf.  │
│(ec/*)   │ │Client   │          │(existing)│(existing)│
└─────────┘ └─────────┘          └─────────┘ └─────────┘
```

## 5. Task Dependencies

### Build Order

```
Phase 1: Core Infrastructure (can be parallel)
├── BE-001: AggregationContext
├── BE-002: AsyncAggregatorInterface
├── BE-005: TransformationContext
└── BE-006: ResponseTransformerInterface

Phase 2: Base Classes (depends on Phase 1)
├── BE-003: AbstractAsyncAggregator (needs BE-002)
└── (future) AbstractResponseTransformer (needs BE-006)

Phase 3: Pipelines (depends on Phase 2)
├── BE-004: AggregatorPipeline (needs BE-001, BE-002, BE-003)
└── BE-007: TransformerPipeline (needs BE-005, BE-006)

Phase 4: Implementations (depends on Phase 3)
├── BE-008 to BE-016: Aggregators (needs BE-003)
└── (future) Transformers (needs Phase 3)

Phase 5: Integration (depends on Phase 4)
├── BE-017: Compiler Passes
├── BE-018: EditorialOrchestratorScalable
├── BE-019: Update Kernel
└── BE-020: Services configuration
```

### Dependency Matrix

| Task | Depends On |
|------|-----------|
| BE-001 | - |
| BE-002 | - |
| BE-003 | BE-002 |
| BE-004 | BE-001, BE-002, BE-003 |
| BE-005 | BE-001 |
| BE-006 | - |
| BE-007 | BE-005, BE-006 |
| BE-008 | BE-003 |
| BE-009 | BE-003 |
| BE-010 | BE-003 |
| BE-011 | BE-003 |
| BE-012 | BE-003 |
| BE-013 | BE-003 |
| BE-014 | BE-003, BE-009 |
| BE-015 | BE-003, BE-009 |
| BE-016 | BE-003 |
| BE-017 | BE-004, BE-007 |
| BE-018 | All above |
| BE-019 | BE-017 |
| BE-020 | All above |

## 6. Risk Dependencies

### High Risk
| Dependency | Risk | Mitigation |
|------------|------|------------|
| Promise resolution | Async complexity | Use GuzzleHttp\Promise\Utils |
| Topological sort | Circular dependencies | Validate at compile time |
| Output compatibility | JSON differences | Comparison tests |

### Medium Risk
| Dependency | Risk | Mitigation |
|------------|------|------------|
| Existing transformers | Breaking changes | Don't modify, wrap |
| External clients | API changes | Version lock in composer |

### Low Risk
| Dependency | Risk | Mitigation |
|------------|------|------------|
| Symfony DI | Container issues | Follow existing patterns |
| Logger | Missing logs | Default NullLogger |

## 7. Version Constraints

```json
{
    "require": {
        "php": ">=8.2",
        "symfony/framework-bundle": "6.4.*",
        "guzzlehttp/promises": "^2.0"
    }
}
```

No new Composer dependencies required - all needed packages are already installed.

## 8. Configuration Dependencies

### Environment Variables

| Variable | Purpose | Required |
|----------|---------|----------|
| `EXTENSION` | URL extension | Yes (existing) |
| `THUMBOR_SERVER_URL` | Image server | Yes (existing) |

### Service Tags

| Tag | Purpose |
|-----|---------|
| `app.aggregator` | Auto-register aggregators |
| `app.response_transformer` | Auto-register transformers |

---

**Status**: COMPLETED
**Reviewed by**: Planner Agent
**Date**: 2026-01-28
