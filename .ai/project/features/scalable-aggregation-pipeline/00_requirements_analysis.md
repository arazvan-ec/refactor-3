# Requirements Analysis: Scalable Aggregation Pipeline

## 1. Problem Statement

El `EditorialOrchestrator` actual tiene 537 líneas con múltiples responsabilidades:
- Obtención de datos de 7+ microservicios (tags, signatures, multimedia, etc.)
- Lógica de promesas async mezclada con lógica de negocio
- Código duplicado para insertedNews y recommendedEditorials
- Transformación de datos entrelazada con agregación
- Imposible agregar nuevos agregadores sin modificar el código existente

**Impacto:**
- Cada nuevo dato requiere modificar EditorialOrchestrator
- Alto riesgo de regresiones
- Tests difíciles de mantener
- Violación de Open/Closed Principle

## 2. Business Requirements

### BR-001: Agregación Escalable
**Descripción:** El sistema debe permitir agregar nuevos agregadores de datos sin modificar código existente.
**Criterio de aceptación:** Agregar un nuevo agregador solo requiere crear una clase y registrarla con un tag.

### BR-002: Ejecución Async Paralela
**Descripción:** Todas las peticiones HTTP deben ejecutarse en paralelo cuando sea posible.
**Criterio de aceptación:** Los agregadores sin dependencias entre sí se ejecutan simultáneamente.

### BR-003: Transformación Modular
**Descripción:** La transformación de datos a JSON debe ser plug-and-play.
**Criterio de aceptación:** Agregar un nuevo transformador solo requiere crear una clase y registrarla con un tag.

### BR-004: Pipeline Declarativo
**Descripción:** La configuración del pipeline debe ser declarativa (YAML), no imperativa (código).
**Criterio de aceptación:** Se puede ver qué agregadores y transformadores están activos en archivos YAML.

## 3. Technical Requirements

### TR-001: AsyncAggregatorInterface
```php
interface AsyncAggregatorInterface {
    public function getKey(): string;
    public function supports(AggregationContext $context): bool;
    public function aggregate(AggregationContext $context): array;
    public function resolve(array $pendingData, AggregationContext $context): array;
    public function getPriority(): int;
    public function getDependencies(): array;
}
```

### TR-002: ResponseTransformerInterface
```php
interface ResponseTransformerInterface {
    public function getKey(): string;
    public function supports(TransformationContext $context): bool;
    public function transform(TransformationContext $context): array;
    public function getPriority(): int;
}
```

### TR-003: Pipeline Orchestration
```php
// Uso final en EditorialOrchestrator
$aggregatedData = $this->aggregatorPipeline->execute($context);
$response = $this->transformerPipeline->transform($aggregatedData);
```

### TR-004: Compiler Pass Auto-Registration
- Tag `app.aggregator` para agregadores
- Tag `app.response_transformer` para transformadores
- Auto-discovery y registro via Compiler Pass

## 4. Current State Analysis

### Agregadores Identificados en EditorialOrchestrator:
| # | Agregador | Líneas | Async | Dependencias |
|---|-----------|--------|-------|--------------|
| 1 | Editorial (principal) | 4-5 | No | - |
| 2 | Section | 1 | No | Editorial |
| 3 | Tags | 8 | No (loop síncrono) | Editorial |
| 4 | Signatures | 10 | No (loop síncrono) | Editorial, Section |
| 5 | Multimedia Opening | 10 | Sí | Editorial |
| 6 | Multimedia (main) | 6 | Sí | Editorial |
| 7 | Photos from Body Tags | 15 | No | Editorial |
| 8 | Membership Links | 20 | Sí (Promise) | Editorial, Section |
| 9 | Inserted News | 35 | Sí (parcial) | Editorial |
| 10 | Recommended Editorials | 45 | Sí (parcial) | Editorial |
| 11 | Comments Count | 3 | No | Editorial |

### Transformadores Identificados:
| # | Transformador | Uso |
|---|---------------|-----|
| 1 | DetailsAppsDataTransformer | Editorial + Section + Tags |
| 2 | JournalistsDataTransformer | Signatures |
| 3 | BodyDataTransformer | Body elements |
| 4 | MultimediaDataTransformer | Multimedia principal |
| 5 | MediaDataTransformerHandler | Opening multimedia |
| 6 | StandfirstDataTransformer | Standfirst |
| 7 | RecommendedEditorialsDataTransformer | Recommended |

## 5. Constraints

### C-001: Backward Compatibility
El nuevo sistema debe producir exactamente la misma respuesta JSON que el sistema actual.

### C-002: Performance
La ejecución paralela no debe ser más lenta que la actual.

### C-003: Existing Tests
Los tests existentes deben seguir pasando.

### C-004: Gradual Migration
Debe ser posible migrar gradualmente, agregador por agregador.

## 6. Out of Scope

- Cambios en los clientes HTTP externos (QueryEditorialClient, etc.)
- Cambios en el formato de respuesta JSON
- Refactorización de los transformadores existentes de body elements

## 7. Success Metrics

| Métrica | Actual | Objetivo |
|---------|--------|----------|
| Líneas en EditorialOrchestrator | 537 | < 100 |
| Tiempo para agregar nuevo agregador | 2-4h | 30min |
| Código duplicado | ~50 líneas | 0 |
| Archivos a modificar para nuevo dato | 3+ | 1 |

## 8. Risks

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Respuesta JSON diferente | Media | Alto | Tests de comparación |
| Performance degradada | Baja | Medio | Benchmarks antes/después |
| Breaking changes | Media | Alto | Migración gradual |

---

**Status**: COMPLETED
**Reviewed by**: Planner Agent
**Date**: 2026-01-28
