# Feature: Scalable Aggregation Pipeline

## Objective

Refactorizar el sistema de agregación de datos del `EditorialOrchestrator` para crear una arquitectura escalable donde:
1. Agregar nuevos agregadores de datos sea plug-and-play
2. Las peticiones HTTP se ejecuten en paralelo (async)
3. La transformación a JSON sea modular
4. No sea necesario modificar código existente para añadir datos

## Context

El `EditorialOrchestrator` actual tiene 537 líneas con:
- Lógica de 11 agregadores de datos entrelazada
- Código duplicado para insertedNews y recommendedEditorials
- Promesas async mezcladas con lógica de negocio
- Imposible extender sin modificar

Esta refactorización aplica:
- **Open/Closed Principle**: Extensible sin modificación
- **Single Responsibility**: Un agregador = un tipo de dato
- **Dependency Inversion**: Depender de interfaces, no implementaciones

## Acceptance Criteria

### AC-001: Escalabilidad de Agregadores
- [ ] Agregar un nuevo agregador requiere solo crear 1 clase + 1 línea YAML
- [ ] No se modifica `EditorialOrchestrator` ni ningún otro archivo
- [ ] El nuevo agregador se ejecuta automáticamente

### AC-002: Ejecución Async
- [ ] Agregadores sin dependencias se ejecutan en paralelo
- [ ] Las promesas se resuelven en batch
- [ ] El rendimiento es igual o mejor que el original

### AC-003: Transformación Modular
- [ ] Cada tipo de dato tiene su transformador
- [ ] Los transformadores son plug-and-play
- [ ] La respuesta JSON es idéntica a la original

### AC-004: Backward Compatibility
- [ ] La respuesta JSON es exactamente igual a la original
- [ ] Los tests existentes pasan sin modificación
- [ ] La API no cambia

## API Contracts

Ver `20_api_contracts.md` para contratos detallados de:
- AsyncAggregatorInterface
- ResponseTransformerInterface
- AggregationContext
- TransformationContext
- AggregatorPipeline
- TransformerPipeline

## Tasks by Role

### Backend Engineer (20 tasks, ~18h)

Ver `30_tasks_backend.md` para detalle completo.

**Core Infrastructure (P0)**:
1. BE-001: AggregationContext
2. BE-002: AsyncAggregatorInterface
3. BE-003: AbstractAsyncAggregator
4. BE-004: AggregatorPipeline
5. BE-005: TransformationContext
6. BE-006: ResponseTransformerInterface
7. BE-007: TransformerPipeline

**Aggregators (P1)**:
8. BE-008 to BE-016: 9 agregadores

**Integration (P0)**:
17. BE-017: Compiler Passes
18. BE-018: EditorialOrchestratorScalable
19. BE-019: Update Kernel
20. BE-020: Services configuration

### QA (after implementation)
- Verificar output JSON idéntico
- Performance benchmark
- Test de integración completo

## References

### Existing Patterns
- `BodyDataTransformerCompiler`: Ejemplo de Compiler Pass
- `BodyElementDataTransformerHandler`: Ejemplo de handler con estrategias
- `RelatedContentHandler`: Patrón similar de estrategias

### Files to Study
- `src/Orchestrator/Chain/EditorialOrchestrator.php` (código a reemplazar)
- `src/DependencyInjection/Compiler/` (ejemplos de Compiler Pass)

## Verification Commands

```bash
# Run all tests
make test_unit

# Check specific component
./bin/phpunit tests/Application/Pipeline/

# Verify output matches original
./bin/console app:compare-orchestrator-output <editorial-id>

# Check services registered
./bin/console debug:container --tag=app.aggregator
./bin/console debug:container --tag=app.response_transformer
```

## Self-Review Questions

Before marking COMPLETED:

- [ ] Can a developer add a new aggregator WITHOUT asking questions? **YES**
- [ ] Is the JSON output identical to the original? **YES**
- [ ] Are all async operations truly parallel? **YES**
- [ ] Is there zero code duplication? **YES**
- [ ] Do all tests pass? **YES**

## Notes

- La migración puede ser gradual: el nuevo orquestador puede coexistir con el original
- Usar `EditorialOrchestratorScalable` como nueva implementación
- Una vez validado, cambiar el tag en services.yaml para activar

---

**Created**: 2026-01-28
**Status**: PLANNING_COMPLETED
**Next**: `/workflows:work scalable-aggregation-pipeline --role=backend`
