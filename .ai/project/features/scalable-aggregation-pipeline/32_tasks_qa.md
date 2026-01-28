# QA Tasks: Scalable Aggregation Pipeline

## Task Overview

| ID | Task | Priority | Effort | Dependencies |
|----|------|----------|--------|--------------|
| QA-001 | Verificar output JSON idéntico | P0 | 2h | BE-018 |
| QA-002 | Test de regresión completo | P0 | 1h | QA-001 |
| QA-003 | Benchmark de performance | P1 | 2h | BE-018 |
| QA-004 | Test de edge cases | P1 | 2h | BE-018 |
| QA-005 | Verificar error handling | P1 | 1h | BE-018 |
| QA-006 | Test de concurrencia | P2 | 1h | BE-018 |
| QA-007 | Code review final | P0 | 2h | All |

**Total Estimated Effort**: ~11 hours

---

## QA-001: Verificar Output JSON Idéntico

**Priority**: P0 - Critical
**Methodology**: Comparison Testing
**Max Iterations**: 10

### Objective
Verificar que el nuevo `EditorialOrchestratorScalable` produce exactamente el mismo JSON que el original `EditorialOrchestrator`.

### Test Strategy

```php
// tests/Orchestrator/Chain/OutputComparisonTest.php

class OutputComparisonTest extends KernelTestCase
{
    /**
     * @dataProvider editorialIdsProvider
     */
    public function test_scalable_produces_same_output_as_original(string $editorialId): void
    {
        $request = new Request(['id' => $editorialId]);

        $originalOutput = $this->originalOrchestrator->execute($request);
        $scalableOutput = $this->scalableOrchestrator->execute($request);

        // Deep comparison
        self::assertEquals(
            json_encode($originalOutput, JSON_PRETTY_PRINT),
            json_encode($scalableOutput, JSON_PRETTY_PRINT),
            "Output mismatch for editorial $editorialId"
        );
    }

    public static function editorialIdsProvider(): array
    {
        return [
            'standard editorial' => ['12345'],
            'blog editorial' => ['23456'],
            'editorial with inserted news' => ['34567'],
            'editorial with recommendations' => ['45678'],
            'editorial with membership cards' => ['56789'],
            'editorial without multimedia' => ['67890'],
            'editorial with video' => ['78901'],
            'editorial with widget' => ['89012'],
        ];
    }
}
```

### Verification Script

```bash
#!/bin/bash
# scripts/compare_orchestrator_output.sh

EDITORIAL_IDS="12345 23456 34567 45678 56789"

for ID in $EDITORIAL_IDS; do
    echo "Testing editorial $ID..."

    ORIGINAL=$(curl -s "http://localhost/v1/editorials/$ID?orchestrator=original")
    SCALABLE=$(curl -s "http://localhost/v1/editorials/$ID?orchestrator=scalable")

    if [ "$ORIGINAL" == "$SCALABLE" ]; then
        echo "✅ Editorial $ID: MATCH"
    else
        echo "❌ Editorial $ID: MISMATCH"
        diff <(echo "$ORIGINAL" | jq .) <(echo "$SCALABLE" | jq .)
    fi
done
```

### Acceptance Criteria
- [ ] 100% de editoriales de prueba producen output idéntico
- [ ] Sin diferencias en ningún campo
- [ ] Sin diferencias en orden de campos

### Escape Hatch
Si después de 10 iteraciones hay diferencias:
1. Documentar las diferencias exactas
2. Evaluar si son aceptables (ej: orden de arrays)
3. Ajustar el nuevo orquestador o los tests

---

## QA-002: Test de Regresión Completo

**Priority**: P0 - Critical
**Methodology**: Automated Testing

### Requirements
Ejecutar toda la suite de tests existente y verificar que pasan.

### Commands

```bash
# Full test suite
make tests

# Individual components
make test_unit           # PHPUnit
make test_stan           # PHPStan Level 9
make test_cs             # Code style
make test_infection      # Mutation testing (79% MSI)
```

### Acceptance Criteria
- [ ] `make test_unit` pasa al 100%
- [ ] `make test_stan` sin errores
- [ ] `make test_cs` sin errores
- [ ] `make test_infection` >= 79% MSI

---

## QA-003: Benchmark de Performance

**Priority**: P1
**Methodology**: Performance Testing

### Objective
Verificar que el nuevo pipeline no degrada el rendimiento.

### Test Script

```php
// tests/Performance/OrchestratorBenchmarkTest.php

class OrchestratorBenchmarkTest extends KernelTestCase
{
    private const ITERATIONS = 100;
    private const MAX_DEGRADATION_PERCENT = 10;

    public function test_scalable_performance_is_acceptable(): void
    {
        $editorialId = '12345';
        $request = new Request(['id' => $editorialId]);

        // Warm up
        $this->originalOrchestrator->execute($request);
        $this->scalableOrchestrator->execute($request);

        // Benchmark original
        $originalStart = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $this->originalOrchestrator->execute($request);
        }
        $originalTime = microtime(true) - $originalStart;

        // Benchmark scalable
        $scalableStart = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $this->scalableOrchestrator->execute($request);
        }
        $scalableTime = microtime(true) - $scalableStart;

        // Calculate degradation
        $degradation = (($scalableTime - $originalTime) / $originalTime) * 100;

        echo sprintf(
            "\nOriginal: %.4fs | Scalable: %.4fs | Degradation: %.2f%%\n",
            $originalTime,
            $scalableTime,
            $degradation
        );

        self::assertLessThanOrEqual(
            self::MAX_DEGRADATION_PERCENT,
            $degradation,
            "Performance degradation exceeds {$degradation}%"
        );
    }
}
```

### Metrics to Capture

| Metric | Target |
|--------|--------|
| Average response time | <= original + 10% |
| P95 response time | <= original + 15% |
| Memory usage | <= original + 20% |

### Acceptance Criteria
- [ ] No más del 10% de degradación en tiempo promedio
- [ ] Memoria dentro del límite
- [ ] Sin memory leaks detectados

---

## QA-004: Test de Edge Cases

**Priority**: P1
**Methodology**: Boundary Testing

### Edge Cases to Test

```php
// tests/Orchestrator/Chain/EdgeCasesTest.php

class EdgeCasesTest extends KernelTestCase
{
    // Editorial sin multimedia
    public function test_editorial_without_multimedia(): void;

    // Editorial sin tags
    public function test_editorial_without_tags(): void;

    // Editorial sin signatures
    public function test_editorial_without_signatures(): void;

    // Editorial sin body
    public function test_editorial_with_empty_body(): void;

    // Editorial sin recommended
    public function test_editorial_without_recommended(): void;

    // Editorial con muchos inserted news (>10)
    public function test_editorial_with_many_inserted_news(): void;

    // Editorial con muchos recommended (>10)
    public function test_editorial_with_many_recommended(): void;

    // Editorial tipo blog (con Twitter)
    public function test_blog_editorial_includes_twitter(): void;

    // Editorial tipo news (sin Twitter)
    public function test_news_editorial_excludes_twitter(): void;

    // Editorial legacy (sourceEditorial = null)
    public function test_legacy_editorial_fallback(): void;

    // Editorial no publicado
    public function test_unpublished_editorial_throws(): void;

    // Journalist no encontrado
    public function test_missing_journalist_handled_gracefully(): void;

    // Multimedia no encontrado
    public function test_missing_multimedia_handled_gracefully(): void;

    // Tag no encontrado
    public function test_missing_tag_skipped(): void;
}
```

### Acceptance Criteria
- [ ] Todos los edge cases probados
- [ ] Manejo graceful de errores
- [ ] Sin excepciones no manejadas

---

## QA-005: Verificar Error Handling

**Priority**: P1
**Methodology**: Fault Injection Testing

### Scenarios to Test

| Scenario | Expected Behavior |
|----------|-------------------|
| Editorial client timeout | Throw exception |
| Section client timeout | Throw exception |
| Tag client timeout | Skip tag, continue |
| Journalist client timeout | Skip signature, continue |
| Multimedia client timeout | Return null multimedia |
| Membership client timeout | Return empty links |
| Comments client timeout | Return 0 comments |

### Test Implementation

```php
public function test_aggregator_failure_does_not_break_pipeline(): void
{
    // Configure TagsAggregator to throw
    $this->mockTagClient
        ->method('findTagById')
        ->willThrowException(new \RuntimeException('Timeout'));

    // Should not throw, should skip tags
    $result = $this->scalableOrchestrator->execute($request);

    // Tags should be empty array
    self::assertSame([], $result['tags']);

    // Other fields should be present
    self::assertArrayHasKey('signatures', $result);
    self::assertArrayHasKey('body', $result);
}
```

### Acceptance Criteria
- [ ] Errores en agregadores no-críticos no rompen el pipeline
- [ ] Errores en agregadores críticos (editorial, section) lanzan excepción
- [ ] Logs adecuados para debugging

---

## QA-006: Test de Concurrencia

**Priority**: P2
**Methodology**: Load Testing

### Objective
Verificar que el pipeline maneja correctamente peticiones concurrentes.

### Test Script

```bash
# Usando Apache Bench
ab -n 1000 -c 50 http://localhost/v1/editorials/12345

# Métricas esperadas:
# - 0% error rate
# - Requests/sec estable
# - No memory leaks
```

### Acceptance Criteria
- [ ] 0% error rate bajo carga
- [ ] Sin race conditions
- [ ] Estado consistente

---

## QA-007: Code Review Final

**Priority**: P0
**Methodology**: Manual Review

### Checklist

#### SOLID Principles
- [ ] **S**: Cada agregador tiene una sola responsabilidad
- [ ] **O**: Se puede agregar nuevo agregador sin modificar existentes
- [ ] **L**: Todos los agregadores son sustituibles
- [ ] **I**: Interfaces pequeñas y focalizadas
- [ ] **D**: Dependencias inyectadas via interfaces

#### Clean Code
- [ ] Métodos < 20 líneas
- [ ] Nombres descriptivos
- [ ] Sin código duplicado
- [ ] Sin comentarios innecesarios
- [ ] Complejidad ciclomática aceptable

#### Security
- [ ] Sin secrets hardcodeados
- [ ] Input validation donde aplique
- [ ] Logs sin datos sensibles

#### Documentation
- [ ] Interfaces documentadas con PHPDoc
- [ ] README actualizado si aplica

### Acceptance Criteria
- [ ] Todos los items del checklist verificados
- [ ] Sin issues críticos
- [ ] Aprobado por revisor

---

## Test Execution Order

```
1. QA-001: Verificar output JSON (blocker)
   ↓
2. QA-002: Test de regresión (blocker)
   ↓
3. QA-005: Error handling
   ↓
4. QA-004: Edge cases
   ↓
5. QA-003: Performance benchmark
   ↓
6. QA-006: Concurrencia
   ↓
7. QA-007: Code review final
```

---

## Ralph Wiggum Loop Config

Para cada task de QA:

```yaml
max_iterations: 10
on_failure:
  - document_issue
  - create_bug_report
  - escalate_to_planner
on_success:
  - mark_completed
  - update_50_state.md
```

---

**Status**: READY FOR EXECUTION
**Reviewed by**: Planner Agent
**Date**: 2026-01-28
