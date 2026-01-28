# Análisis de Violaciones y Plan de Refactorización

## PARTE 1: Violaciones Identificadas

### 1. Violaciones de SOLID

#### 1.1 Single Responsibility Principle (SRP) - CRÍTICO

**Archivo:** `EditorialOrchestrator.php` (537 líneas)
**Problema:** La clase tiene múltiples razones para cambiar:
- Lógica de obtención de datos de múltiples fuentes
- Lógica de transformación de datos
- Lógica de agregación de respuesta
- Lógica de manejo de promesas asíncronas
- Lógica de firmas de periodistas
- Lógica de noticias insertadas
- Lógica de editoriales recomendados
- Lógica de membresía

**Evidencia en código:**
```php
// Método execute() de ~175 líneas con:
- Obtención de editorial
- Obtención de sección
- Bucles para insertedNews (líneas 126-161)
- Bucles para recommendedEditorials (líneas 163-207)
- Obtención de multimedia asíncrono
- Transformación de body
- Agregación final
```

**Impacto:**
- Difícil de mantener
- Alto acoplamiento
- Tests complejos
- Difícil de escalar

---

#### 1.2 Open/Closed Principle (OCP) - MODERADO

**Problema:** Para agregar nuevos tipos de contenido relacionado (ej: galleries, polls), hay que modificar `EditorialOrchestrator`.

**Evidencia:**
```php
// Código duplicado para insertedNews y recommendedEditorials
// Si se agrega un nuevo tipo, hay que duplicar el patrón completo
foreach ($insertedNews as $insertedNew) { ... }
foreach ($recommendedEditorials as $recommendedEditorial) { ... }
```

---

#### 1.3 Dependency Inversion Principle (DIP) - LEVE

**Archivo:** `MultimediaTrait.php`
**Problema:** Dependencia oculta de `Thumbor` service

```php
trait MultimediaTrait {
    private Thumbor $thumbor;

    private function setThumbor(Thumbor $thumbor): void {
        $this->thumbor = $thumbor;  // Setter injection - anti-pattern
    }
}
```

**Impacto:**
- Dependencias no explícitas en constructor
- Difícil de testear
- Violación de constructor injection principle

---

### 2. Violaciones de Clean Code

#### 2.1 Métodos Demasiado Largos
| Método | Líneas | Máximo recomendado |
|--------|--------|-------------------|
| `execute()` | ~175 | 20 |
| `retrievePhotosFromBodyTags()` | 17 | OK |
| `getPromiseMembershipLinks()` | 23 | OK |

#### 2.2 Código Duplicado - DRY Violation

**Patrón repetido para insertedNews y recommendedEditorials:**
```php
// DUPLICADO 1: Obtener editorial relacionada
$editorial = $this->queryEditorialClient->findEditorialById($id);

// DUPLICADO 2: Verificar visibilidad
if ($editorial->isVisible()) { ... }

// DUPLICADO 3: Obtener sección
$section = $this->querySectionClient->findSectionById($editorial->sectionId());

// DUPLICADO 4: Obtener firmas
foreach ($editorial->signatures()->getArrayCopy() as $signature) {
    $result = $this->retrieveAliasFormat($signature->id()->id(), $section);
    ...
}

// DUPLICADO 5: Obtener multimedia
if (!empty($editorial->multimedia()->id()->id())) {
    $resolveData = $this->getAsyncMultimedia(...);
} else {
    $resolveData = $this->getMetaImage(...);
}
```

#### 2.3 Nombres de Variables Poco Descriptivos
```php
$result        // ¿Qué tipo de result?
$promise       // ¿Promise de qué?
$links         // ¿Links de qué tipo?
$idInserted    // Mejor: $insertedEditorialId
```

#### 2.4 Comentarios Excesivos con PHPDoc
Muchos `@phpstan-ignore` indican problemas de tipado que podrían resolverse con mejor diseño.

---

### 3. Problemas de Escalabilidad

#### 3.1 No hay abstracción para contenido relacionado
Si se necesita agregar:
- Galerías relacionadas
- Encuestas relacionadas
- Videos relacionados

Habría que duplicar el código existente.

#### 3.2 Lógica asíncrona mezclada con negocio
La gestión de promesas está entrelazada con la lógica de negocio.

#### 3.3 Trait con estado mutable
`MultimediaTrait` mantiene estado (`$thumbor`, `$sizes`) que complica su uso.

---

## PARTE 2: Plan de Refactorización

### Fase 1: Extracción de Servicios (Descomposición del God Object)

#### 1.1 Crear `RelatedContentFetcher` - Nuevo Servicio
**Propósito:** Encapsular la lógica de obtención de contenido relacionado
```php
interface RelatedContentFetcherInterface {
    public function fetchInsertedNews(Body $body): array;
    public function fetchRecommendedEditorials(RecommendedEditorials $editorials): array;
}
```

#### 1.2 Crear `SignatureFetcher` - Nuevo Servicio
**Propósito:** Encapsular la obtención de firmas de periodistas
```php
interface SignatureFetcherInterface {
    public function fetchSignatures(Signatures $signatures, Section $section, bool $hasTwitter = false): array;
}
```

#### 1.3 Crear `MultimediaFetcher` - Nuevo Servicio
**Propósito:** Encapsular la lógica asíncrona de multimedia
```php
interface MultimediaFetcherInterface {
    public function fetchAsync(Multimedia $multimedia): array;
    public function resolvePromises(array $promises): array;
}
```

#### 1.4 Crear `MembershipLinkResolver` - Nuevo Servicio
**Propósito:** Resolver enlaces de membresía
```php
interface MembershipLinkResolverInterface {
    public function resolve(Editorial $editorial, string $siteId): array;
}
```

---

### Fase 2: Implementación del Strategy Pattern para Contenido Relacionado

#### 2.1 Crear interfaz base
```php
interface RelatedContentStrategyInterface {
    public function supports(string $type): bool;
    public function fetch(Editorial $editorial, Section $section): array;
    public function getType(): string;
}
```

#### 2.2 Implementaciones
- `InsertedNewsStrategy`
- `RecommendedEditorialsStrategy`
- (Futuro: `RelatedGalleriesStrategy`, `RelatedPollsStrategy`)

#### 2.3 Handler centralizado
```php
class RelatedContentHandler {
    public function __construct(
        private readonly iterable $strategies
    ) {}

    public function fetchAll(Editorial $editorial, Section $section): array;
}
```

---

### Fase 3: Refactorización del Trait

#### 3.1 Convertir `MultimediaTrait` en Servicio
```php
class MultimediaProcessor {
    public function __construct(
        private readonly Thumbor $thumbor,
        private readonly array $sizes
    ) {}

    public function getMultimediaId(Multimedia $multimedia): ?MultimediaId;
    public function getShotsLandscape(MultimediaModel $multimedia): array;
}
```

---

### Fase 4: Builder Pattern para Response

#### 4.1 Crear `EditorialResponseBuilder`
```php
class EditorialResponseBuilder {
    public function withEditorial(Editorial $editorial): self;
    public function withSection(Section $section): self;
    public function withTags(array $tags): self;
    public function withSignatures(array $signatures): self;
    public function withBody(array $body): self;
    public function withMultimedia(?array $multimedia): self;
    public function withRelatedContent(array $relatedContent): self;
    public function build(): array;
}
```

---

### Fase 5: EditorialOrchestrator Refactorizado

#### Resultado final esperado (~80 líneas):
```php
class EditorialOrchestrator implements EditorialOrchestratorInterface {
    public function execute(Request $request): array {
        $editorial = $this->fetchEditorial($request);
        $section = $this->sectionFetcher->fetch($editorial->sectionId());

        return $this->responseBuilder
            ->withEditorial($editorial)
            ->withSection($section)
            ->withTags($this->tagFetcher->fetch($editorial->tags()))
            ->withSignatures($this->signatureFetcher->fetch($editorial->signatures(), $section))
            ->withBody($this->bodyTransformer->transform($editorial->body(), $resolveData))
            ->withMultimedia($this->multimediaFetcher->fetch($editorial))
            ->withRelatedContent($this->relatedContentHandler->fetchAll($editorial, $section))
            ->build();
    }
}
```

---

## PARTE 3: Patrones de Diseño a Implementar

| Patrón | Ubicación | Beneficio |
|--------|-----------|-----------|
| **Strategy** | RelatedContentHandler | Extensibilidad para nuevos tipos |
| **Builder** | EditorialResponseBuilder | Construcción clara de respuesta |
| **Facade** | MultimediaFetcher | Simplifica lógica asíncrona |
| **Service Layer** | SignatureFetcher, etc. | SRP compliance |
| **Dependency Injection** | Reemplaza Trait | Explícita, testeable |

---

## PARTE 4: Orden de Implementación

1. **Crear nuevas interfaces** (sin breaking changes)
2. **Implementar servicios nuevos** (gradual)
3. **Refactorizar EditorialOrchestrator** para usar nuevos servicios
4. **Eliminar MultimediaTrait** (reemplazar con servicio)
5. **Actualizar tests**
6. **Documentar cambios**

---

## PARTE 5: Métricas de Éxito

| Métrica | Antes | Después Esperado |
|---------|-------|------------------|
| Líneas en EditorialOrchestrator | 537 | ~100 |
| Método execute() | ~175 líneas | ~30 líneas |
| Clases con SRP violado | 2 | 0 |
| Código duplicado | ~50 líneas | 0 |
| Complejidad ciclomática | Alta | Media |
| Testabilidad | Media | Alta |

---

*Documento generado: 2026-01-28*
*Autor: Claude Code Refactoring Agent*
