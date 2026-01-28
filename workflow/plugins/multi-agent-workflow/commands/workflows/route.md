# /workflows:route - Workflow Router (Mandatory Entry Point)

**Version**: 1.0.0
**Category**: Core
**Priority**: CRITICAL - Must be invoked for ALL new interactions

---

## Purpose

This command is the **MANDATORY entry point** for all interactions with the Multi-Agent Workflow plugin. It analyzes user needs and routes to the appropriate workflow, asking clarifying questions when necessary.

## Invocation

```bash
# Automatic (should be triggered for any new request)
/workflows:route

# With initial context
/workflows:route "I need to add user authentication"
```

## Workflow Decision Matrix

### Quick Classification Questions

When the need is unclear, ask these questions in order:

```
┌─────────────────────────────────────────────────────────────┐
│                    WORKFLOW ROUTER                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. ¿Qué tipo de trabajo necesitas?                        │
│     [ ] Nueva funcionalidad (feature)                      │
│     [ ] Corrección de bug                                  │
│     [ ] Refactoring/mejora                                 │
│     [ ] Investigación/análisis                             │
│     [ ] Documentación                                      │
│     [ ] Revisión de código                                 │
│     [ ] Configuración/setup                                │
│     [ ] Otro (describir)                                   │
│                                                             │
│  2. ¿Cuál es la complejidad estimada?                      │
│     [ ] Simple (< 1 hora, pocos archivos)                  │
│     [ ] Media (1-4 horas, varios componentes)              │
│     [ ] Compleja (> 4 horas, múltiples capas)              │
│     [ ] No estoy seguro                                    │
│                                                             │
│  3. ¿Necesitas coordinación multi-agente?                  │
│     [ ] Sí, backend + frontend                             │
│     [ ] Sí, múltiples capas (domain, app, infra)           │
│     [ ] No, un solo rol es suficiente                      │
│     [ ] No estoy seguro                                    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## Routing Logic

### Decision Tree

```
USER REQUEST
     │
     ▼
┌────────────────┐
│ Is it a        │──YES──▶ /workflows:plan (task-breakdown workflow)
│ new feature?   │
└───────┬────────┘
        │NO
        ▼
┌────────────────┐
│ Is it a        │──YES──▶ Invoke bug-reproducer agent
│ bug fix?       │         Then /workflows:plan (implementation-only)
└───────┬────────┘
        │NO
        ▼
┌────────────────┐
│ Is it code     │──YES──▶ /workflows:review
│ review?        │
└───────┬────────┘
        │NO
        ▼
┌────────────────┐
│ Is it          │──YES──▶ Invoke codebase-analyzer or git-historian
│ investigation? │
└───────┬────────┘
        │NO
        ▼
┌────────────────┐
│ Is it          │──YES──▶ /workflows:work --role=planner (docs only)
│ documentation? │
└───────┬────────┘
        │NO
        ▼
┌────────────────┐
│ Is it          │──YES──▶ Invoke consultant skill
│ consulting?    │
└───────┬────────┘
        │NO
        ▼
   ASK CLARIFYING QUESTIONS
```

## Workflow Selection Matrix

| User Need | Complexity | Multi-Agent | Recommended Workflow | Command |
|-----------|------------|-------------|---------------------|---------|
| New feature | Any | Yes | task-breakdown | `/workflows:plan --workflow=task-breakdown` |
| New feature | Simple | No | default | `/workflows:plan --workflow=default` |
| Bug fix | Any | No | implementation-only | `/workflows:plan --workflow=implementation-only` |
| Refactoring | Complex | Yes | task-breakdown | `/workflows:plan --workflow=task-breakdown` |
| Refactoring | Simple | No | implementation-only | `/workflows:work` |
| Investigation | Any | No | N/A | Invoke research agents |
| Code review | Any | Yes | N/A | `/workflows:review` |
| Documentation | Any | No | N/A | `/workflows:work --role=planner` |
| Setup/Config | Any | No | N/A | Invoke consultant skill |

## Clarifying Questions Templates

### For Features

```markdown
Para ayudarte mejor con esta nueva funcionalidad, necesito algunos detalles:

1. **Descripción**: ¿Puedes describir la funcionalidad en 2-3 oraciones?
2. **Usuarios afectados**: ¿Quién usará esta funcionalidad?
3. **Stack involucrado**: ¿Backend, frontend, o ambos?
4. **Integraciones**: ¿Se conecta con APIs externas o servicios?
5. **Seguridad**: ¿Maneja datos sensibles (auth, pagos, PII)?
6. **Prioridad**: ¿Es urgente o puede planificarse?
```

### For Bugs

```markdown
Para diagnosticar y corregir este bug, necesito:

1. **Comportamiento actual**: ¿Qué está pasando ahora?
2. **Comportamiento esperado**: ¿Qué debería pasar?
3. **Pasos para reproducir**: ¿Cómo puedo ver el error?
4. **Frecuencia**: ¿Siempre ocurre o es intermitente?
5. **Entorno**: ¿Local, staging, producción?
6. **Logs/errores**: ¿Hay mensajes de error específicos?
```

### For Refactoring

```markdown
Para planificar este refactoring, necesito saber:

1. **Alcance**: ¿Qué archivos/módulos están involucrados?
2. **Motivación**: ¿Por qué es necesario este cambio?
3. **Riesgos**: ¿Qué podría romperse?
4. **Tests existentes**: ¿Hay cobertura de tests actual?
5. **Deadline**: ¿Hay restricciones de tiempo?
```

### For Investigation

```markdown
Para investigar esto efectivamente, necesito entender:

1. **Pregunta principal**: ¿Qué necesitas saber exactamente?
2. **Contexto**: ¿Por qué necesitas esta información?
3. **Alcance**: ¿Dónde debería buscar? (archivos, commits, docs)
4. **Formato de respuesta**: ¿Necesitas un reporte, código, o solo respuesta?
```

## Execution Protocol

### Step 1: Initial Analysis

```markdown
## Análisis Inicial de Solicitud

**Solicitud original**: [User's request]

**Clasificación automática**:
- Tipo detectado: [feature/bug/refactor/investigation/docs/review/other]
- Complejidad estimada: [simple/medium/complex/unknown]
- Multi-agente requerido: [yes/no/unknown]
- Confianza en clasificación: [high/medium/low]

**Decisión**:
- Si confianza HIGH → Proceder con workflow recomendado
- Si confianza MEDIUM/LOW → Hacer preguntas de clarificación
```

### Step 2: Clarification (if needed)

Present the relevant clarifying questions based on detected type.
Wait for user response before proceeding.

### Step 3: Workflow Recommendation

```markdown
## Recomendación de Workflow

Basado en tu solicitud, recomiendo:

**Workflow**: [workflow name]
**Comando**: `/workflows:[command] --workflow=[workflow-name]`
**Razón**: [brief explanation]

**Pasos siguientes**:
1. [step 1]
2. [step 2]
3. [step 3]

¿Quieres proceder con este workflow o prefieres ajustar algo?
```

### Step 4: Handoff

Once confirmed, invoke the appropriate workflow command with all gathered context.

## Integration with Trust Model

The router also considers the trust model when routing:

| File Pattern | Trust Level | Routing Adjustment |
|--------------|-------------|-------------------|
| `**/auth/**`, `**/security/**` | LOW | Force task-breakdown + pair review |
| `**/payment/**`, `**/billing/**` | LOW | Force task-breakdown + security review |
| `src/**/*` | MEDIUM | Standard workflow |
| `tests/**/*`, `docs/**/*` | HIGH | Can use implementation-only |

## Enforcement Rules

### CRITICAL: This router is MANDATORY

1. **Every new user request** must pass through this router
2. **No direct workflow execution** without routing first
3. **If skipped**, Claude must self-correct and run the router
4. **Exceptions**: Only for continuing an already-routed task

### Self-Check Protocol

Before executing ANY code change, Claude must verify:

```markdown
## Router Verification

- [ ] Did this request pass through /workflows:route?
- [ ] Was a workflow explicitly selected?
- [ ] Were clarifying questions asked if needed?
- [ ] Is the chosen workflow appropriate for the task?

If ANY checkbox is NO → STOP and run /workflows:route first
```

## Examples

### Example 1: Clear Feature Request

```
User: "Necesito agregar autenticación con Google OAuth"

Router Analysis:
- Type: New feature (detected with HIGH confidence)
- Complexity: Medium (OAuth integration)
- Multi-agent: Yes (backend + possibly frontend)
- Trust level: LOW (auth-related)

Recommendation:
/workflows:plan user-auth-google --workflow=task-breakdown

Additional notes: Due to LOW trust level, will require:
- Pair review for all auth-related code
- Security review before merge
```

### Example 2: Unclear Request

```
User: "El login no funciona"

Router Analysis:
- Type: Possibly bug, possibly feature (LOW confidence)
- Complexity: Unknown
- Multi-agent: Unknown

Clarifying Questions:
1. ¿El login funcionaba antes y dejó de funcionar? (bug)
   O ¿necesitas implementar login por primera vez? (feature)
2. ¿Qué error ves exactamente?
3. ¿En qué entorno ocurre?

[Wait for response before proceeding]
```

### Example 3: Investigation Request

```
User: "¿Cómo funciona el sistema de permisos actual?"

Router Analysis:
- Type: Investigation (HIGH confidence)
- Complexity: N/A
- Multi-agent: No

Recommendation:
Invoking codebase-analyzer agent to investigate the permissions system.
No workflow needed - this is a research task.
```

---

## Summary

The `/workflows:route` command ensures:

1. **Correct workflow selection** for every task
2. **Gathering necessary context** through clarifying questions
3. **Appropriate trust level** application
4. **Consistent entry point** for all interactions

**Remember**: When in doubt, ASK. It's better to clarify than to execute the wrong workflow.
