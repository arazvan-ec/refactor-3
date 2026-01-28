# Architecture: Scalable Aggregation Pipeline

## 1. High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           EDITORIAL ORCHESTRATOR                             │
│                              (Coordinator Only)                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          AGGREGATION PIPELINE                                │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                     AggregatorPipeline                                │   │
│  │  • Topological sort by dependencies                                   │   │
│  │  • Parallel execution of independent aggregators                      │   │
│  │  • Two-phase: aggregate() -> resolve()                               │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                      │                                       │
│           ┌──────────────────────────┼──────────────────────────┐           │
│           ▼                          ▼                          ▼           │
│    ┌─────────────┐           ┌─────────────┐           ┌─────────────┐     │
│    │    Tags     │           │  Signatures │           │ Multimedia  │     │
│    │ Aggregator  │           │  Aggregator │           │ Aggregator  │     │
│    └─────────────┘           └─────────────┘           └─────────────┘     │
│           │                          │                          │           │
│           ▼                          ▼                          ▼           │
│    ┌─────────────┐           ┌─────────────┐           ┌─────────────┐     │
│    │  Inserted   │           │ Recommended │           │ Membership  │     │
│    │    News     │           │ Editorials  │           │   Links     │     │
│    └─────────────┘           └─────────────┘           └─────────────┘     │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                        TRANSFORMATION PIPELINE                               │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                   TransformerPipeline                                 │   │
│  │  • Priority-based execution                                          │   │
│  │  • Composable transformers                                           │   │
│  │  • Context sharing between transformers                              │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                      │                                       │
│           ┌──────────────────────────┼──────────────────────────┐           │
│           ▼                          ▼                          ▼           │
│    ┌─────────────┐           ┌─────────────┐           ┌─────────────┐     │
│    │  Editorial  │           │    Body     │           │ Multimedia  │     │
│    │ Transformer │           │ Transformer │           │ Transformer │     │
│    └─────────────┘           └─────────────┘           └─────────────┘     │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
                            ┌─────────────────┐
                            │  JSON Response  │
                            └─────────────────┘
```

## 2. Component Design

### 2.1 AggregationContext

```php
final class AggregationContext
{
    // Immutable source data
    private readonly NewsBase $editorial;
    private readonly Section $section;

    // Mutable shared state
    private array $sharedData = [];      // Inter-aggregator communication
    private array $resolvedData = [];    // Final aggregated data
    private array $pendingPromises = []; // Promises awaiting resolution
}
```

**Responsibilities:**
- Hold immutable source data (editorial, section)
- Enable data sharing between aggregators
- Track pending promises for batch resolution

### 2.2 AsyncAggregatorInterface

```php
interface AsyncAggregatorInterface
{
    // Identity
    public function getKey(): string;           // Unique identifier
    public function getPriority(): int;         // Execution order (higher = first)
    public function getDependencies(): array;   // Keys of required aggregators

    // Execution
    public function supports(AggregationContext $context): bool;
    public function aggregate(AggregationContext $context): array;  // Start async
    public function resolve(array $pendingData, AggregationContext $context): array;
}
```

**Two-Phase Execution:**
1. **aggregate()**: Start async operations, return promises
2. **resolve()**: Wait for promises, return final data

### 2.3 AggregatorPipeline

```php
final class AggregatorPipeline
{
    private array $aggregators = [];

    public function addAggregator(AsyncAggregatorInterface $aggregator): self;
    public function execute(AggregationContext $context): array;
    public function executeOnly(array $keys, AggregationContext $context): array;
}
```

**Algorithm:**
```
1. Sort aggregators by priority and dependencies (topological sort)
2. Phase 1 - Start all async operations:
   for each aggregator:
     if aggregator.supports(context):
       pendingData[key] = aggregator.aggregate(context)
3. Phase 2 - Resolve in dependency order:
   for each aggregator in sorted order:
     wait for dependencies
     result = aggregator.resolve(pendingData[key], context)
     context.setResolvedData(key, result)
4. Return combined results
```

### 2.4 TransformationContext

```php
final class TransformationContext
{
    // Source data from aggregation
    private readonly array $aggregatedData;
    private readonly AggregationContext $aggregationContext;

    // Building response
    private array $response = [];
}
```

### 2.5 ResponseTransformerInterface

```php
interface ResponseTransformerInterface
{
    public function getKey(): string;
    public function getPriority(): int;
    public function supports(TransformationContext $context): bool;
    public function transform(TransformationContext $context): void;
}
```

### 2.6 TransformerPipeline

```php
final class TransformerPipeline
{
    private array $transformers = [];

    public function addTransformer(ResponseTransformerInterface $transformer): self;
    public function transform(AggregationContext $aggregationContext): array;
}
```

## 3. Dependency Graph

```
                    ┌─────────────┐
                    │  Editorial  │ (source)
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
       ┌──────────┐  ┌──────────┐  ┌──────────┐
       │ Section  │  │   Tags   │  │ Comments │
       └────┬─────┘  └──────────┘  └──────────┘
            │
    ┌───────┼───────┐
    ▼       ▼       ▼
┌────────┐ ┌────────┐ ┌────────────┐
│Signat- │ │Member- │ │Multimedia  │
│ures   │ │ship    │ │(Opening)   │
└────────┘ └────────┘ └────────────┘
    │
    ▼
┌────────────────────────┐
│  InsertedNews &        │
│  RecommendedEditorials │
│  (use Signatures)      │
└────────────────────────┘
```

## 4. Registration Pattern (Compiler Pass)

### 4.1 Service Tags

```yaml
# config/packages/aggregator_pipeline.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    # Aggregators - auto-registered via tag
    App\Application\Aggregator\Impl\TagsAggregator:
        tags:
            - { name: 'app.aggregator', priority: 100 }

    App\Application\Aggregator\Impl\SignaturesAggregator:
        tags:
            - { name: 'app.aggregator', priority: 90 }

    # Transformers - auto-registered via tag
    App\Application\Transformer\Impl\EditorialTransformer:
        tags:
            - { name: 'app.response_transformer', priority: 100 }
```

### 4.2 Compiler Pass

```php
class AggregatorPipelineCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $pipelineDefinition = $container->findDefinition(AggregatorPipeline::class);
        $aggregators = $container->findTaggedServiceIds('app.aggregator');

        foreach ($aggregators as $serviceId => $tags) {
            $pipelineDefinition->addMethodCall('addAggregator', [
                new Reference($serviceId)
            ]);
        }
    }
}
```

## 5. Adding a New Aggregator (Developer Experience)

### Step 1: Create Aggregator Class

```php
// src/Application/Aggregator/Impl/GalleriesAggregator.php
final class GalleriesAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryGalleryClient $galleryClient,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'galleries';
    }

    public function getDependencies(): array
    {
        return ['editorial']; // Needs editorial data first
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorial();

        return [
            'promises' => $this->galleryClient->findByEditorial(
                $editorial->id(),
                async: true
            ),
        ];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        return $this->resolvePromises($pendingData['promises']);
    }
}
```

### Step 2: Register with Tag

```yaml
App\Application\Aggregator\Impl\GalleriesAggregator:
    tags:
        - { name: 'app.aggregator', priority: 50 }
```

### Step 3: Done!

No changes to EditorialOrchestrator or any other file.

## 6. Sequence Diagram

```
┌──────────┐     ┌──────────┐     ┌───────────────┐     ┌───────────────┐
│Controller│     │Orchestr- │     │AggregatorPipe-│     │Transformer    │
│          │     │ator      │     │line           │     │Pipeline       │
└────┬─────┘     └────┬─────┘     └───────┬───────┘     └───────┬───────┘
     │                │                   │                     │
     │ GET /editorial │                   │                     │
     │────────────────>                   │                     │
     │                │                   │                     │
     │                │ execute(context)  │                     │
     │                │──────────────────>│                     │
     │                │                   │                     │
     │                │                   │ aggregate() x N     │
     │                │                   │────────────────────>│
     │                │                   │                     │
     │                │                   │ resolve() x N       │
     │                │                   │<────────────────────│
     │                │                   │                     │
     │                │ aggregatedData    │                     │
     │                │<──────────────────│                     │
     │                │                   │                     │
     │                │ transform(data)   │                     │
     │                │──────────────────────────────────────────>
     │                │                   │                     │
     │                │ response          │                     │
     │                │<──────────────────────────────────────────
     │                │                   │                     │
     │ JsonResponse   │                   │                     │
     │<────────────────                   │                     │
     │                │                   │                     │
```

## 7. Directory Structure

```
src/Application/
├── Aggregator/
│   ├── AsyncAggregatorInterface.php
│   ├── AbstractAsyncAggregator.php
│   ├── AggregationContext.php
│   └── Impl/
│       ├── TagsAggregator.php
│       ├── SignaturesAggregator.php
│       ├── MultimediaAggregator.php
│       ├── MultimediaOpeningAggregator.php
│       ├── PhotosFromBodyAggregator.php
│       ├── MembershipLinksAggregator.php
│       ├── InsertedNewsAggregator.php
│       ├── RecommendedEditorialsAggregator.php
│       └── CommentsAggregator.php
│
├── Transformer/
│   ├── ResponseTransformerInterface.php
│   ├── AbstractResponseTransformer.php
│   ├── TransformationContext.php
│   └── Impl/
│       ├── EditorialBaseTransformer.php
│       ├── SignaturesTransformer.php
│       ├── BodyTransformer.php
│       ├── MultimediaTransformer.php
│       ├── StandfirstTransformer.php
│       └── RecommendedEditorialsTransformer.php
│
├── Pipeline/
│   ├── AggregatorPipeline.php
│   └── TransformerPipeline.php
│
└── Builder/
    └── EditorialResponseBuilder.php
```

## 8. Configuration Example

```yaml
# config/packages/aggregator_pipeline.yaml
services:
    # =========================================================================
    # PIPELINES
    # =========================================================================
    App\Application\Pipeline\AggregatorPipeline:
        arguments:
            $logger: '@monolog.logger'

    App\Application\Pipeline\TransformerPipeline:
        arguments:
            $logger: '@monolog.logger'

    # =========================================================================
    # AGGREGATORS (ordered by priority)
    # =========================================================================

    # Priority 100: Core data (no dependencies)
    App\Application\Aggregator\Impl\TagsAggregator:
        tags: [{ name: 'app.aggregator', priority: 100 }]

    App\Application\Aggregator\Impl\CommentsAggregator:
        tags: [{ name: 'app.aggregator', priority: 100 }]

    # Priority 90: Depends on Section
    App\Application\Aggregator\Impl\SignaturesAggregator:
        tags: [{ name: 'app.aggregator', priority: 90 }]

    App\Application\Aggregator\Impl\MembershipLinksAggregator:
        tags: [{ name: 'app.aggregator', priority: 90 }]

    # Priority 80: Multimedia
    App\Application\Aggregator\Impl\MultimediaOpeningAggregator:
        tags: [{ name: 'app.aggregator', priority: 80 }]

    App\Application\Aggregator\Impl\MultimediaAggregator:
        tags: [{ name: 'app.aggregator', priority: 80 }]

    App\Application\Aggregator\Impl\PhotosFromBodyAggregator:
        tags: [{ name: 'app.aggregator', priority: 80 }]

    # Priority 70: Related content (depends on Signatures)
    App\Application\Aggregator\Impl\InsertedNewsAggregator:
        tags: [{ name: 'app.aggregator', priority: 70 }]

    App\Application\Aggregator\Impl\RecommendedEditorialsAggregator:
        tags: [{ name: 'app.aggregator', priority: 70 }]

    # =========================================================================
    # TRANSFORMERS (ordered by priority)
    # =========================================================================

    App\Application\Transformer\Impl\EditorialBaseTransformer:
        tags: [{ name: 'app.response_transformer', priority: 100 }]

    App\Application\Transformer\Impl\SignaturesTransformer:
        tags: [{ name: 'app.response_transformer', priority: 90 }]

    App\Application\Transformer\Impl\BodyTransformer:
        tags: [{ name: 'app.response_transformer', priority: 80 }]

    App\Application\Transformer\Impl\MultimediaTransformer:
        tags: [{ name: 'app.response_transformer', priority: 70 }]

    App\Application\Transformer\Impl\StandfirstTransformer:
        tags: [{ name: 'app.response_transformer', priority: 60 }]

    App\Application\Transformer\Impl\RecommendedEditorialsTransformer:
        tags: [{ name: 'app.response_transformer', priority: 50 }]
```

---

**Status**: COMPLETED
**Reviewed by**: Planner Agent
**Date**: 2026-01-28
