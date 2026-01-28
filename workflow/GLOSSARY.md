# Glosario de Términos

> Definiciones claras de todos los conceptos usados en este workflow.

---

## Metodologías

### Compound Engineering
**Qué es**: Filosofía donde cada tarea completada hace las siguientes más fáciles.

**Ejemplo**: Al crear un componente de botón bien documentado, todos los futuros botones son más rápidos de crear porque ya existe el patrón.

**Origen**: Evolución del "Vibe Coding" de Andrej Karpathy (2025).

---

### Ralph Wiggum Pattern
**Qué es**: Loop automático de auto-corrección donde la IA intenta arreglar errores hasta 10 veces.

**Por qué se llama así**: Referencia al personaje de Los Simpsons que sigue intentando aunque falle.

**Cómo funciona**:
```
Intento 1: Código falla → Analizar error → Arreglar
Intento 2: Código falla → Analizar error → Arreglar
...
Intento 10: Si sigue fallando → BLOCKED, pedir ayuda
```

**Origen**: Geoffrey Huntley.

---

### Vibe Coding
**Qué es**: Dejar que la IA genere código basándose en el "vibe" o intención general.

**Problema**: Funciona para el 70% pero el 30% restante es difícil de resolver.

**Solución**: Compound Engineering + especificaciones claras.

---

### Agent Harness
**Qué es**: Sistema que coordina múltiples agentes de IA trabajando en paralelo.

**Analogía**: Como un director de orquesta que coordina a los músicos.

---

### MCP (Model Context Protocol)
**Qué es**: Estándar abierto de Anthropic para que los modelos de IA accedan a herramientas y contexto.

**Analogía**: Como USB pero para conectar IAs con herramientas.

---

## Arquitectura

### DDD (Domain-Driven Design)
**Qué es**: Arquitectura que organiza el código alrededor del dominio del negocio.

**Capas**:
- **Domain**: Lógica de negocio pura (sin frameworks)
- **Application**: Casos de uso y orquestación
- **Infrastructure**: Base de datos, APIs externas, etc.

---

### TDD (Test-Driven Development)
**Qué es**: Escribir tests ANTES del código.

**Flujo**:
```
1. Escribir test que falla (Red)
2. Escribir código mínimo para pasar (Green)
3. Refactorizar (Refactor)
```

---

## Archivos Clave

### 50_state.md
**Qué es**: Archivo que contiene el estado actual del desarrollo.

**Por qué "50"**: Es la numeración usada para indicar que es un archivo de estado medio en el flujo.

**Contiene**:
- Estado de cada tarea (PENDING, IN_PROGRESS, COMPLETED)
- Checkpoints
- Bloqueos

**Regla**: Este archivo es la ÚNICA fuente de verdad.

---

### 30_tasks.md
**Qué es**: Lista de tareas a realizar para una feature.

**Por qué "30"**: Viene después de arquitectura (10) y contratos (20).

---

### FEATURE_X.md
**Qué es**: Documento que define una feature completa.

**Contiene**:
- Requisitos
- Criterios de aceptación
- Dependencias
- Estimaciones

---

## Roles/Agentes

### Planner
**Qué hace**: Define y descompone features en tareas.

**Cuándo usarlo**: Siempre al inicio de una feature.

---

### Backend Engineer
**Qué hace**: Implementa código del servidor (API, base de datos, lógica).

**Stack típico**: Laravel/Symfony (PHP), Node.js, etc.

---

### Frontend Engineer
**Qué hace**: Implementa la interfaz de usuario.

**Stack típico**: React, TypeScript.

---

### QA Engineer
**Qué hace**: Prueba el código y valida calidad.

**Herramienta principal**: Ralph Wiggum Loop.

---

## Estados de Tareas

| Estado | Significado |
|--------|-------------|
| `PENDING` | No iniciada |
| `IN_PROGRESS` | En desarrollo |
| `BLOCKED` | Esperando algo |
| `WAITING_API` | Esperando que backend termine |
| `COMPLETED` | Terminada |
| `APPROVED` | Aprobada por QA |
| `REJECTED` | Rechazada, necesita arreglos |

---

## Comandos

### /workflows:plan
Inicia la planificación de una feature.

### /workflows:work
Ejecuta el trabajo de implementación.

### /workflows:review
Ejecuta revisión de calidad.

### /workflows:compound
Captura aprendizajes para el efecto compuesto.

### /workflows:sync
Sincroniza estado entre sesiones.

### /workflows:status
Muestra el estado actual.

---

## Conceptos de Contexto

### Context Window
**Qué es**: La "memoria" limitada que tiene la IA durante una conversación.

**Problema**: Si es muy larga, la IA "olvida" el inicio.

**Solución**: Sesiones cortas + checkpoints + `50_state.md`.

---

### Checkpoint
**Qué es**: Punto de guardado del progreso.

**Cuándo hacer uno**:
- Después de completar una tarea
- Antes de terminar una sesión
- Cuando el contexto se vuelve muy largo

---

### Trust Model
**Qué es**: Nivel de supervisión según qué tan conocido es el código.

| Nivel | Supervisión | Ejemplo |
|-------|-------------|---------|
| 🔴 Alto | Mucha | Código nuevo, crítico |
| 🟡 Medio | Moderada | Código existente modificado |
| 🟢 Bajo | Poca | Código bien probado |

---

## Anti-Patterns

### Context Bleeding
**Qué es**: Cuando la IA "contamina" una sesión con información de otra.

**Solución**: Una instancia = un rol.

---

### One-Shot Feature
**Qué es**: Intentar hacer una feature completa de un solo golpe.

**Problema**: Muy propenso a errores.

**Solución**: Desarrollo incremental.

---

## Referencias Rápidas

- **README.md**: Documentación completa
- **QUICKSTART.md**: Guía de 5 minutos
- **INDEX.md**: Mapa de navegación
- **global_rules.md**: Reglas universales
- **ddd_rules.md**: Reglas de arquitectura DDD
