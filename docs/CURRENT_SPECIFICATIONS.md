# SNAAPI - Especificaciones Actuales del Sistema

## 1. Visión General

SNAAPI es un **API Gateway** construido con Symfony 6.4 que agrega contenido de múltiples microservicios para aplicaciones móviles. El sistema **no persiste datos localmente** - todo el contenido se obtiene vía clientes HTTP de servicios externos.

## 2. Arquitectura Actual

### 2.1 Flujo de Request
```
HTTP Request
    ↓
Controller (EditorialController)
    ↓
OrchestratorChainHandler [Chain of Responsibility]
    ↓
EditorialOrchestrator [God Object - 537 líneas]
    ↓
Consultas paralelas a microservicios:
    ├── QueryEditorialClient
    ├── QuerySectionClient
    ├── QueryMultimediaClient
    ├── QueryJournalistClient
    ├── QueryTagClient
    ├── QueryMembershipClient
    └── QueryLegacyClient
    ↓
DataTransformers [Strategy Pattern]
    ↓
JsonResponse
```

### 2.2 Estructura de Capas (DDD)
```
src/
├── Controller/          # Infraestructura: Puntos de entrada HTTP
├── Application/         # Capa de Aplicación: Casos de uso, DTOs
│   └── DataTransformer/ # Transformación dominio → respuesta API
├── Orchestrator/        # Capa de Aplicación: Agregación de servicios
├── Infrastructure/      # Infraestructura: Servicios externos, traits
└── DependencyInjection/ # Framework: Compiler passes
```

## 3. Componentes Principales

### 3.1 EditorialOrchestrator (Componente Crítico)
**Archivo:** `src/Orchestrator/Chain/EditorialOrchestrator.php`
**Líneas:** 537
**Responsabilidades actuales (violación SRP):**
- Obtención de editorial principal
- Obtención de sección
- Obtención de tags
- Obtención de multimedia
- Obtención de periodistas/firmas
- Obtención de noticias insertadas
- Obtención de editoriales recomendados
- Obtención de enlaces de membresía
- Transformación de body
- Transformación de multimedia
- Agregación de respuesta final

### 3.2 DataTransformers
**Jerarquía de clases:**
```
BodyElementDataTransformer (interface)
    └── ElementTypeDataTransformer (abstract)
        └── ElementContentDataTransformer (abstract)
            └── ElementContentWithLinksDataTransformer (abstract)
                ├── ParagraphDataTransformer
                ├── SubHeadDataTransformer
                ├── NumberedListDataTransformer
                ├── UnorderedListDataTransformer
                ├── GenericListDataTransformer
                └── BodyTagHtmlDataTransformer
```

**Transformadores especializados:**
- `BodyTagPictureDataTransformer`
- `BodyTagVideoDataTransformer`
- `BodyTagVideoYoutubeDataTransformer`
- `BodyTagInsertedNewsDataTransformer`
- `BodyTagMembershipCardDataTransformer`

### 3.3 Patrones de Diseño Identificados

| Patrón | Ubicación | Implementación |
|--------|-----------|----------------|
| Chain of Responsibility | `OrchestratorChainHandler` | Enruta requests por tipo de contenido |
| Strategy | `BodyElementDataTransformerHandler` | Despacha a transformadores específicos |
| Template Method | Jerarquía de transformadores | `read()` en clases base |
| Service Locator | Compiler Passes | Registro dinámico de servicios |

### 3.4 Microservicios Externos (Bounded Contexts)
- **Editorial Service:** Contenido editorial principal
- **Section Service:** Jerarquía de secciones
- **Multimedia Service:** Fotos, videos, widgets
- **Journalist Service:** Información de autores
- **Tag Service:** Etiquetas
- **Membership Service:** Enlaces de suscripción
- **Legacy Service:** Compatibilidad retroactiva

## 4. Métricas de Calidad Actuales

| Métrica | Valor Requerido |
|---------|-----------------|
| PHPStan | Level 9 |
| Code Style | PSR-12 + Symfony |
| PHPUnit | 10 strict mode |
| Mutation Testing | 79% MSI mínimo |

## 5. Configuración de Servicios

### 5.1 Inyección de Dependencias
- Autowire: habilitado
- Autoconfigure: habilitado
- Constructor injection exclusivamente
- Compiler Passes para registro dinámico

### 5.2 Tags de Servicios
| Tag | Propósito |
|-----|-----------|
| `app.data_transformer` | Transformadores de body elements |
| `app.media_data_transformer` | Transformadores de multimedia |
| `app.orchestrators` | Orquestadores editoriales |
| `app.multimedia.orchestrators` | Orquestadores de multimedia |

## 6. Tests Existentes

**Total:** 53 archivos de test
- Unit tests para transformadores
- Unit tests para compiler passes
- Unit tests para handlers
- Unit tests para servicios de infraestructura
- Integration tests para controller

## 7. Endpoints API

### 7.1 Editorial Detail
```
GET /v1/editorials/{id}
Response: Editorial completa con body, multimedia, firmas, tags
```

## 8. Características de Cache
- CDN: `sMaxAge: 64000`
- Cliente: `maxAge: 60`
- `staleWhileRevalidate: 60`
- `staleIfError: 259200` (3 días)

## 9. Messaging (Eventos)
- **Transport:** AMQP (RabbitMQ)
- **Buses:** `cms.messenger.bus`, `editorial.event.bus`
- **Eventos:** `EventEditorial` para invalidación de cache

---

*Documento generado: 2026-01-28*
*Versión del sistema: Symfony 6.4*
