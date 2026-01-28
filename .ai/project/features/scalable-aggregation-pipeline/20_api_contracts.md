# API Contracts: Scalable Aggregation Pipeline

## 1. Core Interfaces

### 1.1 AsyncAggregatorInterface

```php
<?php
namespace App\Application\Aggregator;

interface AsyncAggregatorInterface
{
    /**
     * Get the unique key for this aggregator.
     * Used as identifier in context and results.
     *
     * @return string Unique key (e.g., 'tags', 'signatures')
     */
    public function getKey(): string;

    /**
     * Check if this aggregator should run for the given context.
     *
     * @param AggregationContext $context The context with editorial data
     * @return bool True if should execute
     */
    public function supports(AggregationContext $context): bool;

    /**
     * Start async data fetching. Called in Phase 1.
     * Should start promises but NOT wait for them.
     *
     * @param AggregationContext $context The context
     * @return array<string, mixed> Promises or immediate data
     */
    public function aggregate(AggregationContext $context): array;

    /**
     * Resolve pending data. Called in Phase 2.
     * Wait for promises and return final data.
     *
     * @param array<string, mixed> $pendingData Data from aggregate()
     * @param AggregationContext $context The context
     * @return array<string, mixed> Resolved data
     */
    public function resolve(array $pendingData, AggregationContext $context): array;

    /**
     * Execution priority. Higher = runs first.
     * Used for sorting before dependency resolution.
     *
     * @return int Priority (default: 0)
     */
    public function getPriority(): int;

    /**
     * Keys of aggregators this depends on.
     * Those aggregators will be resolved before this one.
     *
     * @return array<string> Dependency keys
     */
    public function getDependencies(): array;
}
```

### 1.2 ResponseTransformerInterface

```php
<?php
namespace App\Application\Transformer;

interface ResponseTransformerInterface
{
    /**
     * Get the unique key for this transformer.
     *
     * @return string Unique key (e.g., 'editorial', 'body')
     */
    public function getKey(): string;

    /**
     * Check if this transformer should run.
     *
     * @param TransformationContext $context The context
     * @return bool True if should execute
     */
    public function supports(TransformationContext $context): bool;

    /**
     * Transform data and add to response.
     * Modifies context.response directly.
     *
     * @param TransformationContext $context The context
     * @return void
     */
    public function transform(TransformationContext $context): void;

    /**
     * Execution priority. Higher = runs first.
     *
     * @return int Priority (default: 0)
     */
    public function getPriority(): int;
}
```

## 2. Context Objects

### 2.1 AggregationContext

```php
<?php
namespace App\Application\Aggregator;

final class AggregationContext
{
    // Constructor
    public function __construct(
        private readonly NewsBase $editorial,
        private readonly Section $section,
    );

    // Getters
    public function getEditorial(): NewsBase;
    public function getEditorialAsEditorial(): Editorial;
    public function getSection(): Section;
    public function getEditorialId(): string;
    public function getSiteId(): string;
    public function getEditorialType(): string;

    // Shared Data (inter-aggregator communication)
    public function setSharedData(string $key, array $data): self;
    public function getSharedData(string $key): array;
    public function hasSharedData(string $key): bool;

    // Resolved Data (final aggregated results)
    public function setResolvedData(string $aggregatorKey, array $data): self;
    public function getResolvedData(string $aggregatorKey): array;
    public function getAllResolvedData(): array;

    // Pending Promises
    public function addPendingPromises(string $key, array $promises): self;
    public function getPendingPromises(string $key): array;
    public function getAllPendingPromises(): array;
    public function clearPendingPromises(string $key): self;
}
```

### 2.2 TransformationContext

```php
<?php
namespace App\Application\Transformer;

final class TransformationContext
{
    // Constructor
    public function __construct(
        private readonly AggregationContext $aggregationContext,
    );

    // Getters
    public function getAggregationContext(): AggregationContext;
    public function getEditorial(): NewsBase;
    public function getSection(): Section;
    public function getAggregatedData(string $key): array;
    public function getAllAggregatedData(): array;

    // Response Building
    public function setResponseField(string $key, mixed $value): self;
    public function getResponseField(string $key): mixed;
    public function getResponse(): array;
    public function mergeResponse(array $data): self;
}
```

## 3. Pipeline Contracts

### 3.1 AggregatorPipeline

```php
<?php
namespace App\Application\Pipeline;

final class AggregatorPipeline
{
    /**
     * Register an aggregator.
     */
    public function addAggregator(AsyncAggregatorInterface $aggregator): self;

    /**
     * Execute all aggregators and return combined results.
     *
     * @param AggregationContext $context The context
     * @return array<string, mixed> Combined results keyed by aggregator key
     */
    public function execute(AggregationContext $context): array;

    /**
     * Execute only specific aggregators.
     *
     * @param array<string> $keys Aggregator keys to execute
     * @param AggregationContext $context The context
     * @return array<string, mixed> Results from specified aggregators
     */
    public function executeOnly(array $keys, AggregationContext $context): array;

    /**
     * Get registered aggregator keys.
     *
     * @return array<string>
     */
    public function getRegisteredAggregators(): array;

    /**
     * Check if aggregator is registered.
     */
    public function hasAggregator(string $key): bool;
}
```

### 3.2 TransformerPipeline

```php
<?php
namespace App\Application\Pipeline;

final class TransformerPipeline
{
    /**
     * Register a transformer.
     */
    public function addTransformer(ResponseTransformerInterface $transformer): self;

    /**
     * Transform aggregated data into response.
     *
     * @param AggregationContext $aggregationContext Context with aggregated data
     * @return array<string, mixed> Final JSON response
     */
    public function transform(AggregationContext $aggregationContext): array;

    /**
     * Get registered transformer keys.
     *
     * @return array<string>
     */
    public function getRegisteredTransformers(): array;
}
```

## 4. Aggregator Implementations (Contract)

### 4.1 TagsAggregator

```php
Key: 'tags'
Priority: 100
Dependencies: []

Input (from context):
  - editorial.tags(): ArrayCollection of Tag IDs

Output:
{
    "tags": [
        { "id": "string", "name": "string", "slug": "string" },
        ...
    ]
}
```

### 4.2 SignaturesAggregator

```php
Key: 'signatures'
Priority: 90
Dependencies: []

Input:
  - editorial.signatures(): ArrayCollection of Signature IDs
  - context.section: Section (for URL generation)

Output:
{
    "signatures": [
        {
            "aliasId": "string",
            "name": "string",
            "image": "string|null",
            "url": "string",
            "twitter": "string|null" // Only if editorial type is 'blog'
        },
        ...
    ]
}
```

### 4.3 MultimediaAggregator

```php
Key: 'multimedia'
Priority: 80
Dependencies: []

Input:
  - editorial.multimedia(): Multimedia object

Output:
{
    "promises": [Promise],  // In aggregate()
    "multimedia": {         // In resolve()
        "id": Multimedia object
    }
}
```

### 4.4 MultimediaOpeningAggregator

```php
Key: 'multimediaOpening'
Priority: 80
Dependencies: []

Input:
  - editorial.opening(): Opening object

Output:
{
    "multimediaOpening": {
        // Transformed opening multimedia data
    }
}
```

### 4.5 PhotosFromBodyAggregator

```php
Key: 'photosFromBody'
Priority: 80
Dependencies: []

Input:
  - editorial.body(): Body with BodyTagPicture elements

Output:
{
    "photoFromBodyTags": {
        "photoId1": Photo object,
        "photoId2": Photo object,
        ...
    }
}
```

### 4.6 MembershipLinksAggregator

```php
Key: 'membershipLinks'
Priority: 90
Dependencies: []

Input:
  - editorial.body(): Body with BodyTagMembershipCard elements
  - context.siteId: Site identifier

Output:
{
    "membershipLinkCombine": {
        "originalUrl1": "resolvedUrl1",
        "originalUrl2": "resolvedUrl2",
        ...
    }
}
```

### 4.7 InsertedNewsAggregator

```php
Key: 'insertedNews'
Priority: 70
Dependencies: ['signatures'] // Reuses signature fetching logic

Input:
  - editorial.body(): Body with BodyTagInsertedNews elements

Output:
{
    "insertedNews": {
        "editorialId1": {
            "editorial": Editorial,
            "section": Section,
            "signatures": [...],
            "multimediaId": "string"
        },
        ...
    }
}
```

### 4.8 RecommendedEditorialsAggregator

```php
Key: 'recommendedEditorials'
Priority: 70
Dependencies: ['signatures']

Input:
  - editorial.recommendedEditorials(): RecommendedEditorials

Output:
{
    "recommendedEditorials": {
        "editorialId1": {
            "editorial": Editorial,
            "section": Section,
            "signatures": [...],
            "multimediaId": "string"
        },
        ...
    },
    "recommendedNews": [Editorial, Editorial, ...]  // For transformer
}
```

### 4.9 CommentsAggregator

```php
Key: 'comments'
Priority: 100
Dependencies: []

Input:
  - editorial.id(): Editorial ID

Output:
{
    "countComments": int
}
```

## 5. Transformer Implementations (Contract)

### 5.1 EditorialBaseTransformer

```php
Key: 'editorialBase'
Priority: 100

Input:
  - aggregationContext.editorial
  - aggregationContext.section
  - aggregatedData['tags']

Output (added to response):
{
    "id": "string",
    "title": "string",
    "url": "string",
    "sectionId": "string",
    "sectionName": "string",
    "tags": [...],
    "publishDate": "ISO8601",
    // ... other base fields from DetailsAppsDataTransformer
}
```

### 5.2 SignaturesTransformer

```php
Key: 'signatures'
Priority: 90

Input:
  - aggregatedData['signatures']

Output (added to response):
{
    "signatures": [...]
}
```

### 5.3 BodyTransformer

```php
Key: 'body'
Priority: 80

Input:
  - aggregationContext.editorial.body()
  - aggregatedData (for resolveData)

Output (added to response):
{
    "body": [
        { "type": "paragraph", "content": "...", "links": [...] },
        { "type": "subhead", "content": "..." },
        { "type": "picture", ... },
        ...
    ]
}
```

### 5.4 MultimediaTransformer

```php
Key: 'multimedia'
Priority: 70

Input:
  - aggregatedData['multimedia']
  - aggregatedData['multimediaOpening']
  - aggregationContext.editorial

Output (added to response):
{
    "multimedia": {
        "type": "photo|video|widget",
        "id": "string",
        // ... type-specific fields
    }
}
```

### 5.5 StandfirstTransformer

```php
Key: 'standfirst'
Priority: 60

Input:
  - aggregationContext.editorial.standFirst()

Output (added to response):
{
    "standfirst": {
        "text": "string",
        "links": [...]
    }
}
```

### 5.6 RecommendedEditorialsTransformer

```php
Key: 'recommendedEditorials'
Priority: 50

Input:
  - aggregatedData['recommendedEditorials']
  - aggregatedData['recommendedNews']

Output (added to response):
{
    "recommendedEditorials": [
        {
            "id": "string",
            "title": "string",
            "url": "string",
            "multimedia": {...},
            ...
        },
        ...
    ]
}
```

## 6. Error Contracts

### 6.1 AggregatorException

```php
<?php
namespace App\Application\Aggregator\Exception;

class AggregatorException extends \RuntimeException
{
    public static function aggregatorNotFound(string $key): self;
    public static function dependencyNotResolved(string $key, string $dependency): self;
    public static function circularDependency(array $cycle): self;
}
```

### 6.2 TransformerException

```php
<?php
namespace App\Application\Transformer\Exception;

class TransformerException extends \RuntimeException
{
    public static function transformerNotFound(string $key): self;
    public static function missingAggregatedData(string $key): self;
}
```

## 7. Verification Commands

```bash
# Test individual aggregator
./bin/phpunit tests/Application/Aggregator/Impl/TagsAggregatorTest.php

# Test pipeline
./bin/phpunit tests/Application/Pipeline/AggregatorPipelineTest.php

# Test full flow
./bin/phpunit tests/Orchestrator/Chain/EditorialOrchestratorScalableTest.php

# Compare output with original
./bin/console app:compare-orchestrator-output <editorial-id>
```

---

**Status**: COMPLETED
**Reviewed by**: Planner Agent
**Date**: 2026-01-28
