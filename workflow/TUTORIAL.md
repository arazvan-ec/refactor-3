# Tutorial: Construyendo una Feature Completa

> Ejemplo práctico paso a paso: Sistema de Tareas (Todo List)

---

## Escenario

Vamos a construir un sistema de tareas simple con:
- Backend: API REST para CRUD de tareas
- Frontend: Interfaz React para gestionar tareas
- Tests: Cobertura completa

**Tiempo estimado**: 30-45 minutos siguiendo el workflow.

---

## Fase 1: Planificación (80% del éxito)

### Paso 1.1: Iniciar la Planificación

```bash
/workflows:plan task-management
```

El Planner automáticamente:
1. Analiza requisitos
2. Diseña la arquitectura
3. Define contratos de API
4. Crea tareas para backend y frontend

### Paso 1.2: Revisar Documentos Generados

Después de planificar, encontrarás estos archivos:

```
.ai/project/features/task-management/
├── FEATURE_task-management.md     # Definición de la feature
├── 00_requirements_analysis.md    # Análisis de requisitos
├── 10_architecture.md             # Arquitectura propuesta
├── 20_api_contracts.md            # Contratos de API
├── 30_tasks_backend.md            # Tareas backend
├── 31_tasks_frontend.md           # Tareas frontend
└── 50_state.md                    # Estado actual
```

### Paso 1.3: Verificar el Contrato de API

Abre `20_api_contracts.md` y verifica que incluya:

```yaml
# Ejemplo de contrato esperado
endpoints:
  - GET    /api/tasks          # Listar todas las tareas
  - POST   /api/tasks          # Crear tarea
  - GET    /api/tasks/{id}     # Obtener tarea
  - PUT    /api/tasks/{id}     # Actualizar tarea
  - DELETE /api/tasks/{id}     # Eliminar tarea

schemas:
  Task:
    id: integer
    title: string (required, max 255)
    description: string (optional)
    completed: boolean (default: false)
    created_at: datetime
    updated_at: datetime
```

**Importante**: Si el contrato no está completo, pide al Planner que lo complete ANTES de continuar.

---

## Fase 2: Implementación Backend

### Paso 2.1: Iniciar Trabajo Backend

```bash
/workflows:work task-management --mode=roles --role=backend
```

El Backend Engineer:
1. Lee el contrato de API
2. Crea entidades (DDD)
3. Implementa repositorios
4. Crea casos de uso
5. Expone endpoints
6. Escribe tests (TDD)

### Paso 2.2: Estructura Esperada (DDD)

```
backend/src/
├── Domain/
│   └── Task/
│       ├── Entity/
│       │   └── Task.php
│       ├── ValueObject/
│       │   └── TaskTitle.php
│       └── Repository/
│           └── TaskRepositoryInterface.php
├── Application/
│   └── Task/
│       ├── UseCase/
│       │   ├── CreateTask.php
│       │   ├── GetTask.php
│       │   ├── ListTasks.php
│       │   ├── UpdateTask.php
│       │   └── DeleteTask.php
│       └── DTO/
│           ├── TaskRequest.php
│           └── TaskResponse.php
└── Infrastructure/
    └── Task/
        ├── Persistence/
        │   └── DoctrineTaskRepository.php
        └── HTTP/
            └── TaskController.php
```

### Paso 2.3: Verificar Tests

El backend debe incluir tests para:

```php
// tests/Unit/Domain/Task/Entity/TaskTest.php
public function test_can_create_task(): void
public function test_can_mark_task_as_completed(): void
public function test_title_cannot_be_empty(): void

// tests/Integration/Task/TaskApiTest.php
public function test_can_list_tasks(): void
public function test_can_create_task(): void
public function test_can_update_task(): void
public function test_can_delete_task(): void
```

### Paso 2.4: Checkpoint Backend

Después de que backend esté listo:

```bash
/workflows:sync
```

Verifica `50_state.md`:
```markdown
## Estado Actual

### Backend
- [x] Entidad Task creada
- [x] Repositorio implementado
- [x] Casos de uso completados
- [x] API endpoints funcionando
- [x] Tests pasando (>80% coverage)

Status: COMPLETED
```

---

## Fase 3: Implementación Frontend

### Paso 3.1: Iniciar Trabajo Frontend

```bash
/workflows:work task-management --mode=roles --role=frontend
```

El Frontend Engineer:
1. Lee el contrato de API
2. Crea componentes React
3. Implementa servicios de API
4. Agrega estado (hooks/store)
5. Escribe tests

### Paso 3.2: Estructura Esperada

```
frontend/src/
├── features/
│   └── tasks/
│       ├── components/
│       │   ├── TaskList.tsx
│       │   ├── TaskItem.tsx
│       │   ├── TaskForm.tsx
│       │   └── TaskFilters.tsx
│       ├── hooks/
│       │   ├── useTasks.ts
│       │   └── useTaskMutations.ts
│       ├── services/
│       │   └── taskApi.ts
│       └── types/
│           └── task.ts
└── tests/
    └── features/
        └── tasks/
            ├── TaskList.test.tsx
            ├── TaskForm.test.tsx
            └── taskApi.test.ts
```

### Paso 3.3: Verificar Integración

El frontend debe conectar con el backend:

```typescript
// services/taskApi.ts
export const taskApi = {
  list: () => fetch('/api/tasks').then(r => r.json()),
  create: (data: CreateTaskDto) => fetch('/api/tasks', {
    method: 'POST',
    body: JSON.stringify(data)
  }).then(r => r.json()),
  // ... etc
};
```

### Paso 3.4: Checkpoint Frontend

```bash
/workflows:sync
```

Verifica `50_state.md`:
```markdown
### Frontend
- [x] Componentes creados
- [x] Hooks implementados
- [x] API service conectado
- [x] Tests pasando (>70% coverage)

Status: COMPLETED
```

---

## Fase 4: Review y QA

### Paso 4.1: Ejecutar Review

```bash
/workflows:review task-management
```

El QA Engineer ejecuta:
1. **Tests automatizados**: Unit + Integration
2. **Security Review**: Validación de inputs, OWASP
3. **Performance Review**: N+1 queries, memory leaks
4. **DDD Compliance**: Arquitectura correcta
5. **Code Review**: Estándares y patrones

### Paso 4.2: Ralph Wiggum Loop

Si hay errores, el QA automáticamente:

```
Iteración 1: Error en validación de TaskTitle
  → Arreglar: Agregar validación de longitud máxima
  → Re-test: PASS

Iteración 2: Test de integración falla
  → Arreglar: Corregir endpoint URL
  → Re-test: PASS

Resultado: APPROVED después de 2 iteraciones
```

### Paso 4.3: Verificar Aprobación

```bash
/workflows:status
```

```markdown
## Feature: task-management

Status: APPROVED ✅

### Checklist
- [x] Backend completo
- [x] Frontend completo
- [x] Tests pasando
- [x] Security review OK
- [x] Performance review OK
- [x] DDD compliance OK
```

---

## Fase 5: Captura de Learnings

### Paso 5.1: Compound Engineering

```bash
/workflows:compound task-management
```

Esto captura:
- Patrones que funcionaron bien
- Anti-patrones encontrados
- Mejoras para futuras features
- Actualiza reglas del proyecto si es necesario

### Paso 5.2: Ejemplo de Learning Capturado

```markdown
## Learnings - task-management

### Patrones Exitosos
1. Separar validación en ValueObjects (TaskTitle) previno bugs
2. Usar DTO para requests/responses mejoró type-safety

### Anti-Patterns Encontrados
1. Inicialmente se intentó poner lógica en Controller → Mover a UseCase

### Mejoras Aplicadas
- Agregado test helper para crear Tasks en tests
- Actualizado template de API contracts con ejemplos
```

---

## Resumen del Flujo Completo

```
┌──────────────────────────────────────────────────────────────────────┐
│                         TIMELINE COMPLETO                            │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  /workflows:plan task-management                                     │
│       │                                                              │
│       ▼                                                              │
│  ┌─────────────────┐                                                 │
│  │   PLANIFICACIÓN │  ← 80% del esfuerzo mental                      │
│  │   (10-15 min)   │    Genera: specs, arquitectura, contratos       │
│  └────────┬────────┘                                                 │
│           │                                                          │
│           ▼                                                          │
│  /workflows:work --role=backend                                      │
│       │                                                              │
│       ▼                                                              │
│  ┌─────────────────┐                                                 │
│  │    BACKEND      │  ← TDD + DDD                                    │
│  │   (10-15 min)   │    Genera: entities, repos, APIs, tests         │
│  └────────┬────────┘                                                 │
│           │                                                          │
│           ▼                                                          │
│  /workflows:work --role=frontend                                     │
│       │                                                              │
│       ▼                                                              │
│  ┌─────────────────┐                                                 │
│  │   FRONTEND      │  ← Componentes + Hooks                          │
│  │   (10-15 min)   │    Genera: components, services, tests          │
│  └────────┬────────┘                                                 │
│           │                                                          │
│           ▼                                                          │
│  /workflows:review task-management                                   │
│       │                                                              │
│       ▼                                                              │
│  ┌─────────────────┐                                                 │
│  │     REVIEW      │  ← Ralph Wiggum Loop                            │
│  │    (5-10 min)   │    Auto-corrige hasta 10 veces                  │
│  └────────┬────────┘                                                 │
│           │                                                          │
│           ▼                                                          │
│  /workflows:compound task-management                                 │
│       │                                                              │
│       ▼                                                              │
│  ┌─────────────────┐                                                 │
│  │   COMPOUND      │  ← Captura learnings                            │
│  │    (2-5 min)    │    Mejora el sistema para siguiente feature     │
│  └─────────────────┘                                                 │
│                                                                      │
│  TOTAL: 35-60 minutos para feature completa con tests               │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Tips para Éxito

### Do's
- Revisar contratos de API antes de implementar
- Hacer checkpoint después de cada fase
- Confiar en el Ralph Wiggum loop para arreglos
- Usar `/workflows:status` frecuentemente

### Don'ts
- No saltarse la planificación
- No ignorar tests fallidos
- No hacer sesiones muy largas (máx 2 horas)
- No cambiar de rol a mitad de sesión

---

## Siguiente Paso

Ahora que completaste el tutorial, estás listo para:

1. **Aplicar a tu proyecto**: Usa `/workflows:plan` con tu propia feature
2. **Explorar agentes avanzados**: Ver [INDEX.md](./INDEX.md) para más opciones
3. **Personalizar reglas**: Modifica `global_rules.md` según tu proyecto
