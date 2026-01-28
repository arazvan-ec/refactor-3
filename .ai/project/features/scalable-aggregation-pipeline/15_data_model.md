# Data Model: Scalable Aggregation Pipeline

## 1. Overview

Este documento describe los modelos de datos utilizados en el pipeline de agregación escalable. No se crean nuevas entidades de dominio; se definen estructuras de datos internas para el pipeline.

## 2. Core Data Structures

### 2.1 AggregationContext

```
┌─────────────────────────────────────────────────────────────────┐
│                      AggregationContext                          │
├─────────────────────────────────────────────────────────────────┤
│ Immutable (readonly)                                             │
│ ├── editorial: NewsBase                                          │
│ └── section: Section                                             │
├─────────────────────────────────────────────────────────────────┤
│ Mutable State                                                    │
│ ├── sharedData: array<string, array>                            │
│ │   └── Inter-aggregator communication                          │
│ ├── resolvedData: array<string, array>                          │
│ │   └── Final results per aggregator key                        │
│ └── pendingPromises: array<string, array<Promise>>              │
│     └── Promises awaiting resolution                            │
└─────────────────────────────────────────────────────────────────┘
```

**Type Definition:**
```php
final class AggregationContext
{
    // Immutable source
    private readonly NewsBase $editorial;
    private readonly Section $section;

    // Mutable state
    /** @var array<string, array<string, mixed>> */
    private array $sharedData = [];

    /** @var array<string, array<string, mixed>> */
    private array $resolvedData = [];

    /** @var array<string, array<int, Promise|mixed>> */
    private array $pendingPromises = [];
}
```

### 2.2 TransformationContext

```
┌─────────────────────────────────────────────────────────────────┐
│                    TransformationContext                         │
├─────────────────────────────────────────────────────────────────┤
│ Immutable                                                        │
│ └── aggregationContext: AggregationContext                      │
├─────────────────────────────────────────────────────────────────┤
│ Mutable                                                          │
│ └── response: array<string, mixed>                              │
│     └── JSON response being built                               │
└─────────────────────────────────────────────────────────────────┘
```

**Type Definition:**
```php
final class TransformationContext
{
    private readonly AggregationContext $aggregationContext;

    /** @var array<string, mixed> */
    private array $response = [];
}
```

## 3. Aggregator Data Contracts

### 3.1 Tags Aggregator Output

```json
{
    "tags": [
        {
            "id": "string (UUID)",
            "name": "string",
            "slug": "string",
            "url": "string (optional)"
        }
    ]
}
```

**Source:** `Ec\Tag\Domain\Model\Tag`

### 3.2 Signatures Aggregator Output

```json
{
    "signatures": [
        {
            "aliasId": "string",
            "name": "string",
            "firstName": "string (optional)",
            "lastName": "string (optional)",
            "image": "string (URL, optional)",
            "url": "string (URL)",
            "twitter": "string (handle, optional)",
            "bio": "string (optional)"
        }
    ]
}
```

**Source:** `Ec\Journalist\Domain\Model\Journalist` → `JournalistsDataTransformer`

### 3.3 Multimedia Aggregator Output

```json
{
    "multimedia": {
        "<multimedia_id>": {
            "id": "string",
            "type": "photo|video|widget",
            "file": "string (path)",
            "clippings": { ... }
        }
    }
}
```

**Source:** `Ec\Multimedia\Domain\Model\Multimedia`

### 3.4 Multimedia Opening Aggregator Output

```json
{
    "multimediaOpening": {
        "type": "photo|video|widget",
        "id": "string",
        "shots": {
            "landscape": { ... },
            "portrait": { ... }
        },
        "caption": "string (optional)",
        "credit": "string (optional)"
    }
}
```

**Source:** `MultimediaOrchestratorHandler` output

### 3.5 Photos From Body Aggregator Output

```json
{
    "photoFromBodyTags": {
        "<photo_id>": {
            "id": "string",
            "file": "string",
            "width": "int",
            "height": "int",
            "clippings": { ... }
        }
    }
}
```

**Source:** `Ec\Multimedia\Domain\Model\Photo\Photo`

### 3.6 Membership Links Aggregator Output

```json
{
    "membershipLinkCombine": {
        "<original_url>": "<resolved_url>",
        "<original_url_2>": "<resolved_url_2>"
    }
}
```

**Source:** `QueryMembershipClient::getMembershipUrl()`

### 3.7 Inserted News Aggregator Output

```json
{
    "insertedNews": {
        "<editorial_id>": {
            "editorial": "Editorial object",
            "section": "Section object",
            "signatures": [
                { "aliasId": "...", "name": "...", ... }
            ],
            "multimediaId": "string"
        }
    }
}
```

### 3.8 Recommended Editorials Aggregator Output

```json
{
    "recommendedEditorials": {
        "<editorial_id>": {
            "editorial": "Editorial object",
            "section": "Section object",
            "signatures": [ ... ],
            "multimediaId": "string"
        }
    },
    "recommendedNews": ["Editorial", "Editorial", ...]
}
```

### 3.9 Comments Aggregator Output

```json
{
    "countComments": "int"
}
```

**Source:** `QueryLegacyClient::findCommentsByEditorialId()`

## 4. Transformer Data Contracts

### 4.1 Final Response Structure

```json
{
    // From EditorialBaseTransformer
    "id": "string",
    "title": "string",
    "subtitle": "string (optional)",
    "url": "string",
    "canonicalUrl": "string",
    "sectionId": "string",
    "sectionName": "string",
    "sectionUrl": "string",
    "publishDate": "ISO8601",
    "modifiedDate": "ISO8601",
    "editorialType": "string",
    "tags": [ ... ],
    "countComments": "int",

    // From SignaturesTransformer
    "signatures": [ ... ],

    // From BodyTransformer
    "body": [
        { "type": "paragraph", "content": "...", "links": [...] },
        { "type": "subhead", "content": "..." },
        { "type": "picture", "id": "...", "shots": {...} },
        { "type": "insertedNews", "editorial": {...} },
        { "type": "membershipCard", "buttons": [...] },
        ...
    ],

    // From MultimediaTransformer
    "multimedia": {
        "type": "photo|video|widget",
        ...
    },

    // From StandfirstTransformer
    "standfirst": {
        "text": "string",
        "links": [ ... ]
    },

    // From RecommendedEditorialsTransformer
    "recommendedEditorials": [
        {
            "id": "string",
            "title": "string",
            "url": "string",
            "multimedia": { ... },
            "signatures": [ ... ]
        }
    ]
}
```

## 5. Data Flow Diagram

```
┌──────────────┐
│  Editorial   │
│  (NewsBase)  │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────────────────────────────────────────┐
│                     AGGREGATION PHASE                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────┐  ┌───────────┐  ┌────────────┐  ┌─────────────┐    │
│  │  Tags   │  │ Signatures│  │ Multimedia │  │  Comments   │    │
│  │ Output  │  │  Output   │  │   Output   │  │   Output    │    │
│  └────┬────┘  └─────┬─────┘  └──────┬─────┘  └──────┬──────┘    │
│       │             │               │               │            │
│       └─────────────┴───────────────┴───────────────┘            │
│                             │                                     │
│                             ▼                                     │
│                   ┌─────────────────┐                            │
│                   │ AggregationCtx  │                            │
│                   │  .resolvedData  │                            │
│                   └────────┬────────┘                            │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌──────────────────────────────────────────────────────────────────┐
│                   TRANSFORMATION PHASE                            │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────┐  ┌──────────┐  ┌──────────┐  ┌─────────────┐   │
│  │ Editorial   │  │   Body   │  │Multimedia│  │ Recommended │   │
│  │ Transformer │  │Transform │  │Transform │  │ Transformer │   │
│  └──────┬──────┘  └────┬─────┘  └────┬─────┘  └──────┬──────┘   │
│         │              │             │               │           │
│         └──────────────┴─────────────┴───────────────┘           │
│                             │                                     │
│                             ▼                                     │
│                   ┌─────────────────┐                            │
│                   │TransformCtx     │                            │
│                   │  .response      │                            │
│                   └────────┬────────┘                            │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  JSON Response  │
                    └─────────────────┘
```

## 6. Domain Objects Reference

### External Domain Objects (Read-Only)

| Object | Package | Used By |
|--------|---------|---------|
| `NewsBase` | `ec/editorial-client` | Context source |
| `Editorial` | `ec/editorial-client` | Extended editorial data |
| `Section` | `ec/section-client` | Context source |
| `Tag` | `ec/tag-client` | TagsAggregator |
| `Journalist` | `ec/journalist-client` | SignaturesAggregator |
| `Multimedia` | `ec/multimedia-client` | MultimediaAggregator |
| `Photo` | `ec/multimedia-client` | PhotosFromBodyAggregator |
| `Body` | `ec/editorial-client` | Multiple aggregators |
| `BodyElement` | `ec/editorial-client` | Body transformation |

### Internal Data Structures (New)

| Structure | Purpose | Mutable |
|-----------|---------|---------|
| `AggregationContext` | Hold aggregation state | Partially |
| `TransformationContext` | Hold transformation state | Yes |

## 7. Validation Rules

### 7.1 AggregationContext
- `editorial` must not be null
- `section` must not be null
- `sharedData` keys must be non-empty strings
- `resolvedData` keys must match aggregator keys

### 7.2 Aggregator Output
- Must be array (can be empty)
- Keys must be strings
- Values must be serializable

### 7.3 Transformer Output
- Must add to response (not replace)
- Keys must not conflict with other transformers
- Values must be JSON-serializable

---

**Status**: COMPLETED
**Reviewed by**: Planner Agent
**Date**: 2026-01-28
