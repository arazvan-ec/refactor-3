# Claude Code - AI-Powered Workflow System

Sistema completo para uso modular y escalable de Claude Code con **consultor AI**, **roles detallados**, **reglas específicas**, **workflows configurables** y **ejecución en paralelo**.

---

## Empieza Aquí

| Tu Situación | Lee Esto | Tiempo |
|--------------|----------|--------|
| **Soy nuevo, quiero empezar rápido** | [QUICKSTART.md](./QUICKSTART.md) | 5 min |
| **Quiero un ejemplo práctico** | [TUTORIAL.md](./TUTORIAL.md) | 30-45 min |
| **No entiendo un término** | [GLOSSARY.md](./GLOSSARY.md) | Consulta |
| **Busco un archivo específico** | [INDEX.md](./INDEX.md) | Navegación |
| **Soy un agente Claude** | [CLAUDE.md](./CLAUDE.md) | Técnico |
| **Quiero toda la teoría** | Este README | 30+ min |

> **Recomendación**: Si es tu primera vez, empieza por [QUICKSTART.md](./QUICKSTART.md) y luego [TUTORIAL.md](./TUTORIAL.md).

---

## Ideas y Fundamentos del Workflow

Este sistema integra las metodologías más avanzadas en desarrollo de software asistido por IA. A continuación documentamos las ideas fundamentales, sus orígenes y las actualizaciones más recientes (Enero 2026).

### 1. Compound Engineering (Ingeniería Compuesta)

**Origen:** Evolución natural del "Vibe Coding" popularizado por Andrej Karpathy en 2025.

**Principio central:** *"Cada unidad de trabajo de ingeniería debe hacer que las unidades subsecuentes sean más fáciles, no más difíciles."*

**Fórmula fundamental:**
```
Productividad = (Velocidad de Código) × (Calidad del Feedback) × (Frecuencia de Iteración)
```

**Actualizaciones 2026:**
- **Evolución de Vibe Coding a Compound Engineering:** 2025 fue el año del vibe coding (mejoras de 30-70% en velocidad). 2026 trae compound engineering con mejoras proyectadas del 300-700%.
- **Cambio de rol:** De "escritor de código" a "orquestador de sistemas" - arquitectando sistemas de feedback, pipelines de testing y guardrails de instrucciones.
- **Tres pilares del cambio:**
  1. Transformación de rol: Code Writer → System Orchestrator
  2. Mentalidad: "Get It Right" → "Fail Fast and Correct"
  3. Verificación: Manual Review → Automated Validation

**Implementación en este workflow:**
- Captura de conocimiento explícito después de cada feature (`/workflows:compound`)
- Patrones reutilizables documentados en reglas del proyecto
- Learnings que se componen a través de features para acelerar desarrollo futuro

> **Fuentes:**
> - [Compound Engineering - Vinci Rufus](https://www.vincirufus.com/posts/compound-engineering/)
> - [AI Engineering Trends 2025 - The New Stack](https://thenewstack.io/ai-engineering-trends-in-2025-agents-mcp-and-vibe-coding/)

---

### 2. Ralph Wiggum Pattern & Loom

**Origen:** Geoffrey Huntley, desarrollador australiano, introdujo el patrón a mediados de 2025.

**Concepto:** Un loop infinito que alimenta el mismo prompt a un agente de IA una y otra vez hasta que la tarea se complete. El progreso no persiste en el context window del LLM - vive en archivos y git history.

**Filosofía clave:** *"Mejor fallar predeciblemente que tener éxito impredeciblemente."*

**Actualizaciones 2026:**
- **The Weaving Loom:** Huntley está desarrollando infraestructura para "software evolutivo" - replanteando los últimos 40 años de ingeniería de software. Incluye:
  - "Spool" - un host de código fuente que replica GitHub pero usa JJ como primitiva de control de versiones
  - Auto-healing de software: sistemas que identifican problemas, los arreglan, despliegan automáticamente y verifican
- **Adopción masiva:** Diciembre 2025 llevó el patrón Ralph a la cima de los timelines de AI
- **Costos revolucionarios:** El desarrollo de software ahora puede hacerse mientras duermes por $10.42/hora
- **Principio de "Principal Skinner":** Cada loop de Wiggum necesita supervisión - bounded iteration con escape hatches

**Implementación en este workflow:**
```python
while tests_failing and iterations < MAX_ITERATIONS (10):
    analyze_error()
    fix_code()
    run_tests()
    if passing: checkpoint_complete()

if iterations >= MAX_ITERATIONS:
    mark_blocked()
    document_for_planner()
```

> **Fuentes:**
> - [Everything is a Ralph Loop - Geoffrey Huntley](https://ghuntley.com/loop/)
> - [Ralph Wiggum as "software engineer" - ghuntley.com](https://ghuntley.com/ralph/)
> - [GitHub: how-to-ralph-wiggum](https://github.com/ghuntley/how-to-ralph-wiggum)
> - [Inventing the Ralph Wiggum Loop - Dev Interrupted](https://linearb.io/dev-interrupted/podcast/inventing-the-ralph-wiggum-loop)

---

### 3. Agent Harnesses para Long-Running Agents

**Origen:** Investigación de Anthropic sobre agentes que trabajan en múltiples context windows.

**Problema central:** *"Los agentes long-running deben trabajar en sesiones discretas, y cada nueva sesión comienza sin memoria de lo que vino antes."*

**Arquitectura de dos agentes:**
1. **Initializer Agent:** Configura el ambiente con listas de features, repositorios git y archivos de tracking de progreso
2. **Coding Agent:** Hace progreso incremental sesión por sesión mientras mantiene estados de código limpio

**Actualizaciones 2026:**
- **2025 fue Agentes. 2026 es Agent Harnesses.** El harness determina si los agentes tienen éxito o fallan
- **Gartner predice:** 40% de aplicaciones enterprise tendrán agentes AI embebidos para fin de 2026 (vs <5% en 2025)
- **Multi-Agent Systems:** Incremento del 1,445% en consultas sobre sistemas multi-agente (Q1 2024 a Q2 2025)
- **Patrones de fallo identificados:**
  - Intentar hacer demasiado de una vez (one-shot)
  - Declarar prematuramente el proyecto como completo
  - Marcar features como completos sin testing apropiado

**Implementación en este workflow:**
- `50_state.md` como archivo de progreso central
- Checkpoints con documentación explícita para resume
- Testing obligatorio antes de marcar features completos
- Handoff limpio entre sesiones (como turnos de ingenieros humanos)

> **Fuentes:**
> - [Effective Harnesses for Long-Running Agents - Anthropic](https://www.anthropic.com/engineering/effective-harnesses-for-long-running-agents)
> - [2026: The Year of Agent Harnesses - Medium](https://aakashgupta.medium.com/2025-was-agents-2026-is-agent-harnesses-heres-why-that-changes-everything-073e9877655e)

---

### 4. Agent-Native Architecture

**Principio:** Diseñar sistemas donde los agentes autónomos son la interfaz primaria, no features suplementarios.

**Actualizaciones 2026:**
- **Tier emergente de startups "agent-native":** Compañías construyendo productos con arquitecturas agent-first desde cero, sin restricciones de codebases legacy o patrones UI existentes
- **Mercado proyectado:** De $7.8 billion hoy a $52+ billion para 2030
- **7 patrones de diseño clave para 2026:**
  1. ReAct (Reasoning + Acting)
  2. Reflection (Auto-evaluación)
  3. Tool Use (Uso de herramientas)
  4. Planning (Planificación)
  5. Multi-Agent Collaboration
  6. Sequential Workflows
  7. Human-in-the-Loop
- **Tres tipos de memoria long-term:** Episódica, Semántica y Procedural

**Implementación en este workflow:**

| Principio | Implementación |
|-----------|----------------|
| **Parity** | Claude puede hacer todo lo que el usuario |
| **Granularity** | Herramientas atómicas, features = prompts |
| **Composability** | Claude compone herramientas según necesidad |
| **Files as Interface** | Todo estado en Markdown/YAML |
| **Context Injection** | `context.md` inyectado en todos los roles |
| **Completion Signals** | Señales explícitas en `50_state.md` |

> **Fuentes:**
> - [7 Agentic AI Trends 2026 - MachineLearningMastery](https://machinelearningmastery.com/7-agentic-ai-trends-to-watch-in-2026/)
> - [5 Key Trends in Agentic Development 2026 - The New Stack](https://thenewstack.io/5-key-trends-shaping-agentic-development-in-2026/)

---

### 5. Model Context Protocol (MCP)

**Origen:** Estándar abierto introducido por Anthropic en Noviembre 2024 para estandarizar cómo los sistemas AI integran datos con herramientas externas.

**Analogía:** *"Estandarización tipo USB-C, permitiendo que cualquier modelo AI se conecte seamlessly con cualquier fuente de datos o herramienta."*

**Actualizaciones 2026:**
- **Adopción masiva:** OpenAI adoptó MCP en Marzo 2025. Ahora usado por Claude, Cursor, Microsoft Copilot, Gemini, VS Code, ChatGPT
- **Linux Foundation:** Anthropic donó MCP a la Agentic AI Foundation (AAIF) en Diciembre 2025, co-fundada con Block y OpenAI
- **Métricas de crecimiento:** 97M+ descargas mensuales de SDK (Python + TypeScript), 10,000+ servidores MCP publicados
- **Platinum members:** AWS, Anthropic, Block, Bloomberg, Cloudflare, Google, Microsoft, OpenAI
- **2026 outlook:** MCP es el backbone que hace posible que AI sea verdaderamente agentic

**Relevancia para este workflow:**
- Skills atómicos diseñados para ser consumidos como herramientas MCP
- Arquitectura compatible con el estándar de herramientas MCP
- Preparado para integración con el ecosistema MCP growing

> **Fuentes:**
> - [Introducing Model Context Protocol - Anthropic](https://www.anthropic.com/news/model-context-protocol)
> - [MCP Wikipedia](https://en.wikipedia.org/wiki/Model_Context_Protocol)
> - [MCP & AI-Assisted Coding 2026 - DEV Community](https://dev.to/blackgirlbytes/my-predictions-for-mcp-and-ai-assisted-coding-in-2026-16bm)
> - [Donating MCP to AAIF - Anthropic](https://www.anthropic.com/news/donating-the-model-context-protocol-and-establishing-of-the-agentic-ai-foundation)

---

### 6. Subagents y Multi-Agent Coordination

**Principio:** Equipos orquestados de agentes especializados en lugar de un solo agente all-purpose.

**Actualizaciones 2026:**
- **Revolución de microservicios en AI:** Single all-purpose agents reemplazados por equipos orquestados
- **Patrón "Puppeteer":** Orquestadores que coordinan agentes especialistas (researcher, coder, analyst)
- **Best practices de Anthropic:**
  - Usar subagents especialmente para problemas complejos
  - Pedir a Claude investigar y planificar primero mejora significativamente el performance
  - Subagents no pueden spawnear otros subagents (usar Skills o chain desde main conversation)
- **Alternativa emergente:** Usar Task(...) de Claude para spawner clones del agente general con contexto en CLAUDE.md

**Implementación en este workflow:**
- 16 agentes especializados en 5 categorías:
  - **roles/** (4): planner, backend, frontend, qa
  - **review/** (4): security, performance, ddd-compliance, code-review
  - **research/** (3): codebase-analyzer, git-historian, dependency-auditor
  - **workflow/** (3): bug-reproducer, spec-analyzer, style-enforcer
  - **design/** (2): api-designer, ui-verifier

> **Fuentes:**
> - [Claude Code Best Practices - Anthropic](https://www.anthropic.com/engineering/claude-code-best-practices)
> - [Create Custom Subagents - Claude Code Docs](https://code.claude.com/docs/en/sub-agents)
> - [Building Agents with Claude Agent SDK - Anthropic](https://www.anthropic.com/engineering/building-agents-with-the-claude-agent-sdk)

---

### 7. Test-Driven Development (TDD) & Domain-Driven Design (DDD)

**Principios clásicos mejorados con AI:**

**TDD en contexto AI (2026):**
- Testing obligatorio ANTES de marcar features completos
- Los modelos no exhiben naturalmente comportamientos deseados como desarrollo incremental o testing comprehensivo sin prompt engineering cuidadoso
- Browser automation (Puppeteer) para testing como lo haría un humano

**DDD con AI:**
- Separación clara de capas permite que agentes trabajen en paralelo sin conflictos
- Domain layer puro facilita generación de código predecible
- Value Objects y Entities con factories previenen estados inválidos

**Implementación en este workflow:**
- TDD obligatorio para roles Backend y Frontend
- Ciclo Red → Green → Refactor documentado
- DDD compliance checker como agente dedicado
- Reglas comprehensivas en `rules/ddd_rules.md`

---

### 8. Context Window Management

**Principio "Commodore 64":** Tratar la memoria como un recurso limitado.

**Actualizaciones 2026:**
- **Problema resuelto por Anthropic:** Dos agentes (initializer + coding) que preservan contexto entre sesiones
- **Archivos clave:**
  - `claude-progress.txt` para tracking de progreso
  - Git history como memoria persistente
  - Feature lists en JSON (más resiliente que Markdown contra overwrites del modelo)

**Implementación en este workflow:**
- Checkpoints explícitos con prompts de resume
- `50_state.md` como fuente de verdad
- Protocolos de restart de sesión
- Documentación de próximos pasos antes de cerrar

---

### 9. Spec-Driven Development (Desarrollo Dirigido por Especificaciones)

**Principio central:** *"Las especificaciones son el contrato. El código es la implementación del contrato."*

**Filosofía:** En desarrollo tradicional, las especificaciones suelen ser documentos que se desactualizan rápidamente. En este workflow, las especificaciones son **artefactos vivos** que:
- Se crean **ANTES** de cualquier línea de código
- Son la **fuente de verdad** para validar implementaciones
- Permiten **desarrollo paralelo** (frontend puede mockear mientras backend implementa)
- Son **verificables automáticamente** contra el código

**El problema que resuelve:**
```
❌ Sin Spec-Driven:
  Developer recibe: "Implementa autenticación"
  Developer pregunta: "¿Qué campos? ¿Qué errores? ¿Qué formato?"
  → Ciclos de ida y vuelta, retrabajos, bugs por malentendidos

✅ Con Spec-Driven:
  Developer recibe: Contrato completo con request/response/errores
  Developer implementa: Exactamente lo especificado
  Validator verifica: Implementación cumple 100% del contrato
  → Cero ambigüedad, desarrollo paralelo, validación automática
```

**Flujo Spec-Driven en este workflow:**

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   PLANNER   │────▶│ API DESIGNER│────▶│  ENGINEERS  │────▶│SPEC ANALYZER│
│             │     │             │     │             │     │             │
│ Define      │     │ Create      │     │ Implement   │     │ Validate    │
│ Feature     │     │ Contracts   │     │ Against     │     │ Compliance  │
│ Requirements│     │ (APIs, UI)  │     │ Contracts   │     │             │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                           │                   │                   │
                           ▼                   ▼                   ▼
                    ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
                    │20_api_      │     │   Código    │     │ Compliance  │
                    │contracts.md │     │   + Tests   │     │ Report 85%  │
                    └─────────────┘     └─────────────┘     └─────────────┘
```

**Agentes involucrados:**

| Agente | Rol en Spec-Driven | Output |
|--------|-------------------|--------|
| **Planner** | Define qué se necesita, acceptance criteria | `FEATURE_X.md`, `00_requirements.md` |
| **API Designer** | Crea contratos técnicos detallados | `20_api_contracts.md`, OpenAPI specs |
| **Backend/Frontend** | Implementan contra contratos | Código que cumple especificaciones |
| **Spec Analyzer** | Valida compliance | Reporte con % de cumplimiento |

**Estructura de un API Contract:**

```markdown
## Endpoint: POST /api/users

### Request
| Field    | Type   | Required | Validation              |
|----------|--------|----------|-------------------------|
| email    | string | Yes      | Valid email format      |
| name     | string | Yes      | 2-50 characters         |
| password | string | Yes      | Min 8 chars, 1 number   |

### Responses
| Status | Condition        | Body                           |
|--------|------------------|--------------------------------|
| 201    | Success          | `{ id, email, name, created }` |
| 400    | Validation error | `{ error: { details: [...] }}` |
| 409    | Email exists     | `{ error: { code: "EMAIL_EXISTS" }}` |

### Verification
- Backend: `curl -X POST localhost:8000/api/users -d '{...}'`
- Frontend: Submit form → success toast → redirect /dashboard
```

**Spec Analyzer Output (ejemplo):**

```markdown
# Spec Compliance Report: user-authentication

**Compliance**: 85% (17/20 items)

## API Contract Compliance
| Endpoint         | Spec Items | Implemented | Status |
|------------------|------------|-------------|--------|
| POST /api/users  | 4          | 3           | 75%    |
| GET /api/users   | 3          | 3           | 100%   |

## Gaps Found
1. ❌ 409 response not implemented (POST /api/users)
2. ❌ Email uniqueness validation missing

## Recommendations
1. Add `findByEmail()` to UserRepository
2. Implement check in CreateUserUseCase
```

**Beneficios clave:**

1. **Desarrollo Paralelo Real**
   - Frontend puede crear mocks basados en contratos
   - Backend implementa mientras frontend avanza
   - Integración es "plug and play" porque ambos siguen el mismo contrato

2. **Validación Automática**
   - Spec Analyzer detecta gaps antes de QA
   - Reduce ciclos de feedback
   - Compliance medible (porcentaje)

3. **Documentación Siempre Actualizada**
   - Contratos SON la documentación
   - Si el código pasa validación, la documentación es correcta
   - No hay docs desactualizados

4. **Onboarding Acelerado**
   - Nuevos developers leen contratos, no código legacy
   - Claridad sobre qué debe hacer cada endpoint
   - Menos preguntas, más productividad

**Regla de oro:**
> *"Si un engineer puede empezar a implementar SIN hacer preguntas, la especificación está completa. Si tiene que preguntar algo, la especificación está incompleta."*

**Implementación en este workflow:**
- `/workflows:plan` genera especificaciones antes de código
- `agents/design/api-designer.md` crea contratos detallados
- `agents/workflow/spec-analyzer.md` valida compliance
- `20_api_contracts.md` es el artefacto central de especificación

> **Fuentes:**
> - [Design by Contract - Bertrand Meyer](https://en.wikipedia.org/wiki/Design_by_contract)
> - [API-First Development - Swagger](https://swagger.io/resources/articles/adopting-an-api-first-approach/)
> - [Contract-First API Design - Microsoft](https://learn.microsoft.com/en-us/azure/architecture/best-practices/api-design)

---

### 10. The 80% Problem & Comprehension Debt

**Origen:** Addy Osmani, "The 80% Problem in Agentic Coding" (Enero 2026)

**El Problema del 80%:**

Los modelos AI ahora generan aproximadamente **80% del código**, dejando solo 20% para ediciones humanas. Pero este cambio porcentual oculta problemas profundos:

#### Comprehension Debt (Deuda de Comprensión)

> *"Es trivialmente fácil revisar código que ya no podrías escribir desde cero."* - Addy Osmani

**Definición**: La brecha entre el código que puedes revisar vs el código que podrías escribir.

```
┌─────────────────────────────────────────────────────────────────┐
│                    DEUDA DE COMPRENSIÓN                          │
│                                                                  │
│   Código que puedes    Código que podrías   =    Deuda de       │
│      REVISAR        -    ESCRIBIR               Comprensión     │
│                                                                  │
│   ⚠️ Si la brecha crece, pierdes control del sistema            │
└─────────────────────────────────────────────────────────────────┘
```

#### Cambio en la Naturaleza de los Errores

| Antes de AI (Errores de Sintaxis) | Después de AI (Errores Conceptuales) |
|-----------------------------------|--------------------------------------|
| Puntos y coma faltantes | Suposiciones incorrectas |
| Errores de tipos | Decisiones arquitectónicas erróneas |
| Errores de import | Features construidos sobre premisas defectuosas |
| Fáciles de detectar en IDE | Difíciles de detectar hasta producción |

#### El Problema Real

```
┌────────────────────────────────────────────────────────────────┐
│                        PROYECTO                                │
├────────────────────────────────────┬───────────────────────────┤
│           70% RÁPIDO               │        30% DIFÍCIL        │
│                                    │                           │
│  • Scaffolding                     │  • Edge cases             │
│  • CRUD básico                     │  • Error handling robusto │
│  • UI inicial                      │  • Security               │
│  • Happy paths                     │  • Performance            │
│                                    │  • Integración real       │
│                                    │  • Casos de borde         │
│         ⚡ AI excels here          │    🧠 Human expertise     │
└────────────────────────────────────┴───────────────────────────┘
```

**Patrones de fallo en el 30%:**

| Patrón | Descripción | Costo |
|--------|-------------|-------|
| **Two-step-back** | Fixes que introducen nuevos bugs | 2-3x tiempo |
| **Hidden complexity** | Dependencias no obvias | Rewrite parcial |
| **Diminishing returns** | Cada fix toma más tiempo | Frustración |
| **Security gaps** | Vulnerabilidades no detectadas | Riesgo real |

**Por qué este workflow mitiga el 80% Problem:**
- **Spec-Driven**: Especificaciones completas antes de código previenen "sorpresas" en el 20% difícil
- **TDD**: Tests escritos primero atrapan edge cases temprano
- **Ralph Wiggum Loop**: Bounded iteration evita loops infinitos
- **Quality Gates**: Checklist explícito para código crítico
- **Comprehension Guardian**: Nuevo agente que verifica comprensión antes de aprobar
- **Self-Review Pattern**: Agentes critican su propio código con "contexto fresco"

#### Estrategias de Mitigación Implementadas

| Estrategia | Descripción | Implementación |
|------------|-------------|----------------|
| **Self-Review** | Agente critica su propio código | Checklist obligatorio antes de COMPLETED |
| **Comprehension Checkpoints** | Verificar entendimiento periódicamente | Cada 3 iteraciones TDD |
| **Decision Documentation** | Documentar el "por qué" | DECISIONS.md obligatorio |
| **Bounded Iteration** | No loops infinitos | Max 10 iteraciones (Ralph Wiggum) |
| **Fresh Context Review** | Revisar como si otro lo escribió | Self-Review Pattern |

#### Comprehension Guardian Agent (NUEVO)

Agente especializado que:
- Detecta acumulación de deuda de comprensión
- Requiere self-review antes de COMPLETED
- Verifica checkpoints de conocimiento
- Asegura documentación de decisiones

```bash
# Ejecutar verificación de comprensión
/workflows:comprehension feature-name

# Generar reporte
/workflows:comprehension-report feature-name
```

> **Referencia completa**: `.ai/workflow/docs/COMPREHENSION_DEBT.md`

---

**Three Pillars of Trust (Modelo de Confianza):**

Cuánta supervisión necesita cada tarea depende de tres factores:

```
                    TRUST MODEL

     ┌─────────────────────────────────────┐
     │                                     │
     │   FAMILIARITY ──▶ TRUST ──▶ CONTROL │
     │                                     │
     │   ¿Conoces       ¿Ha        ¿Cuánta │
     │   la tarea?      entregado  supervisión│
     │                  bien?      necesita?│
     └─────────────────────────────────────┘

Calibración de Control:

  HIGH CONTROL (supervisión constante)
    │
    │  • Nueva tecnología
    │  • Código crítico (auth, payments)
    │  • Primer feature de un tipo
    │
    ├──────────────────────────────────────
    │
    │  • Tecnología conocida
    │  • Patrones establecidos
    │  • Features similares a anteriores
    │
    │  LOW CONTROL (más autonomía)
    ▼
```

**Aplicación práctica:**

| Situación | Familiarity | Trust | Control Recomendado |
|-----------|-------------|-------|---------------------|
| Primer auth feature | Baja | Baja | 🔴 Alto - review cada paso |
| Segundo auth feature | Alta | Media | 🟡 Medio - review checkpoints |
| Décimo auth feature | Alta | Alta | 🟢 Bajo - review final |
| Feature con nueva API externa | Baja | N/A | 🔴 Alto - investigar primero |
| Refactor de código conocido | Alta | Alta | 🟢 Bajo - confiar en tests |

---

**"Junior Developer Code" Philosophy:**

> *"Treat AI-generated code like code from a junior developer. It needs careful review and testing before submission."* — Addy Osmani

**Implicaciones:**
- ✅ AI puede escribir código funcional rápidamente
- ⚠️ Pero puede no entender el contexto completo
- ⚠️ Puede introducir vulnerabilidades sutiles
- ⚠️ Puede no seguir patrones del proyecto
- ✅ **Siempre requiere review humano o automatizado**

**Cómo este workflow implementa esta filosofía:**
- QA Agent revisa TODO el código generado
- Security Review Agent busca vulnerabilidades
- DDD Compliance Agent verifica patrones
- Spec Analyzer valida contra contratos

---

**Quality Gates for Production:**

Checklist obligatorio antes de considerar código "production-ready":

```markdown
## Quality Gates Checklist

### Logic & Correctness
- [ ] ¿La lógica es correcta para todos los inputs esperados?
- [ ] ¿Se manejan todos los edge cases identificados?
- [ ] ¿El código hace exactamente lo que la spec dice?

### Security
- [ ] ¿Input validation en todos los entry points?
- [ ] ¿No hay secrets hardcodeados?
- [ ] ¿SQL/NoSQL injection prevenido?
- [ ] ¿XSS prevenido (si aplica)?
- [ ] ¿Auth/authz correctamente implementados?

### Performance
- [ ] ¿No hay N+1 queries?
- [ ] ¿Operaciones costosas son async o cached?
- [ ] ¿Memory leaks prevenidos?

### Error Handling
- [ ] ¿Todos los errores tienen handling apropiado?
- [ ] ¿Errores loggeados con contexto suficiente?
- [ ] ¿Usuario recibe mensajes útiles (no stack traces)?

### Testing
- [ ] ¿Coverage > 80% (backend) / > 70% (frontend)?
- [ ] ¿Tests cubren happy path Y edge cases?
- [ ] ¿Integration tests para APIs?

### Documentation
- [ ] ¿API contracts actualizados?
- [ ] ¿Decisiones arquitectónicas documentadas?
- [ ] ¿README actualizado si hay cambios de setup?

### Dependencies
- [ ] ¿No hay vulnerabilidades conocidas en deps?
- [ ] ¿Licencias son compatibles?
```

**Implementación en este workflow:**
- Este checklist está integrado en el QA Agent
- Security Review Agent cubre la sección de Security
- Performance Review Agent cubre Performance
- Spec Analyzer cubre Logic & Correctness contra specs

> **Fuentes:**
> - [Beyond Vibe Coding - Addy Osmani](https://beyond.addy.ie/)
> - [The 70% Problem in AI-Assisted Development](https://addyosmani.com/blog/)

---

## Arquitectura del Sistema

```
Este workflow integra todas estas ideas en un sistema cohesivo:

┌─────────────────────────────────────────────────────────────────┐
│                     COMPOUND ENGINEERING                        │
│  (Cada iteración acelera las siguientes)                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐        │
│  │   PLANNER   │───▶│   BACKEND   │    │  FRONTEND   │        │
│  │  (80% plan) │    │  (TDD+DDD)  │    │   (TDD)     │        │
│  └─────────────┘    └──────┬──────┘    └──────┬──────┘        │
│                            │                  │                │
│                            ▼                  ▼                │
│                     ┌─────────────────────────────┐            │
│                     │           QA               │            │
│                     │   (Ralph Wiggum Loop)      │            │
│                     └─────────────────────────────┘            │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              AGENT HARNESS LAYER                         │  │
│  │  • 50_state.md (progress tracking)                       │  │
│  │  • Checkpoints (session preservation)                    │  │
│  │  • Git as memory (context persistence)                   │  │
│  │  • MCP-compatible skills                                 │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Usar como Plugin de Claude Code

Este proyecto puede instalarse como **plugin de Claude Code**, permitiendo usarlo en cualquier proyecto.

### Instalación

```bash
# 1. Agregar el marketplace
/plugin marketplace add https://github.com/arazvan-ec/workflow

# 2. Instalar el plugin
/plugin install multi-agent-workflow
```

### Comandos del Plugin

```bash
# Planificar un feature (80% del esfuerzo)
/workflows:plan user-authentication

# Ejecutar implementación
/workflows:work user-authentication

# Review multi-agente
/workflows:review user-authentication

# Capturar learnings (Compound Engineering)
/workflows:compound user-authentication

# Trabajar como rol específico
/workflows:role planner user-authentication
/workflows:role backend user-authentication

# Sincronizar y ver estado
/workflows:sync user-authentication
/workflows:status user-authentication
```

---

## Estructura del Plugin

```
plugins/multi-agent-workflow/
├── .claude-plugin/
│   └── plugin.json          # Manifest del plugin (v2.0.0)
├── agents/
│   ├── roles/               # 4 roles core (planner, backend, frontend, qa)
│   ├── review/              # 4 agentes de review
│   ├── research/            # 3 agentes de investigación
│   ├── workflow/            # 3 agentes de proceso
│   └── design/              # 2 agentes de diseño
├── commands/workflows/      # 7 comandos de workflow
├── skills/                  # 10 skills reutilizables
├── rules/                   # Reglas (global, DDD, project-specific)
├── CLAUDE.md               # Documentación para Claude
└── README.md               # Esta documentación
```

---

## Workflows Disponibles

### `plan` - Planning Exhaustivo (80% del esfuerzo)
```
Requirements → Architecture → API Contracts → Task Breakdown
```
- Genera documentos detallados de especificación
- Ideal para features complejos
- Sigue el principio de Compound Engineering

### `work` - Ejecución Paralela
```
Backend ⟷ Frontend (paralelo) → QA
```
- Backend y Frontend trabajan simultáneamente
- QA con Ralph Wiggum loop (max 10 iteraciones)
- Auto-correction con bounded iteration

### `review` - Review Multi-Agente
```
Security → Performance → DDD Compliance → Code Quality
```
- 4 agentes especializados de review
- Cada uno con su área de expertise

### `compound` - Captura de Conocimiento
```
Analyze → Extract Patterns → Update Rules → Measure Acceleration
```
- Extrae patrones y anti-patrones
- Actualiza reglas del proyecto automáticamente
- Mide el efecto compuesto (tiempo ahorrado)

---

## Principios Fundamentales

1. **Contexto Explícito** - Todo en archivos, nada en memoria implícita
2. **Roles Inmutables** - Una instancia = un rol fijo
3. **Estado Sincronizado** - `50_state.md` como fuente de verdad
4. **Bounded Iteration** - Max 10 intentos antes de escalar
5. **Fail Fast and Correct** - Errores baratos cuando regenerar toma segundos
6. **Testing Before Completion** - Nunca marcar done sin verificar
7. **Knowledge Compounding** - Cada feature acelera los siguientes
8. **Spec-Driven** - Contratos completos antes de implementar, validación automática de compliance
9. **Trust Model** - Calibrar supervisión según familiaridad, confianza y riesgo
10. **Quality Gates** - Checklist obligatorio de producción antes de aprobar cualquier feature
11. **Evolution Governance** - Análisis exhaustivo obligatorio antes de implementar nuevas tendencias, herramientas o refactors
12. **Comprehension Debt Management** - Velocidad de generación no debe exceder velocidad de comprensión (80% Problem awareness)

---

## Anti-patterns (Evitar)

| Evitar | Hacer |
|--------|-------|
| "Recuerda que antes dijimos..." | "Lee `context.md` y `50_state.md`" |
| Cambiar de rol a mitad de camino | Mantener rol fijo durante toda la sesión |
| Sesiones largas sin checkpoint | Checkpoint después de cada milestone |
| Implementar sin leer context.md | Siempre leer context.md primero |
| One-shot de features completos | Desarrollo incremental feature por feature |
| Marcar complete sin testing | Verificar con tests antes de marcar done |
| Adoptar tendencias "porque están de moda" | Análisis exhaustivo con proceso de Evolution Governance |

---

## Documentación Adicional

- **`rules/global_rules.md`** - Reglas universales para todos los roles
- **`rules/ddd_rules.md`** - Arquitectura DDD completa
- **`CLAUDE.md`** - Guía técnica para agentes Claude

---

## Referencias y Recursos

### Compound Engineering & Vibe Coding
- [Compound Engineering - Vinci Rufus](https://www.vincirufus.com/posts/compound-engineering/)
- [From Vibe Coding to Context Engineering - MIT Technology Review](https://www.technologyreview.com/2025/11/05/1127477/from-vibe-coding-to-context-engineering-2025-in-software-development/)
- [Vibe Coding - Wikipedia](https://en.wikipedia.org/wiki/Vibe_coding)

### Ralph Wiggum & Loom
- [Everything is a Ralph Loop - ghuntley.com](https://ghuntley.com/loop/)
- [Ralph Wiggum as "software engineer"](https://ghuntley.com/ralph/)
- [GitHub: how-to-ralph-wiggum](https://github.com/ghuntley/how-to-ralph-wiggum)
- [2026: Year of the Ralph Loop Agent - DEV](https://dev.to/alexandergekov/2026-the-year-of-the-ralph-loop-agent-1gkj)

### Anthropic Engineering
- [Effective Harnesses for Long-Running Agents](https://www.anthropic.com/engineering/effective-harnesses-for-long-running-agents)
- [Claude Code Best Practices](https://www.anthropic.com/engineering/claude-code-best-practices)
- [Building Agents with Claude Agent SDK](https://www.anthropic.com/engineering/building-agents-with-the-claude-agent-sdk)

### Model Context Protocol (MCP)
- [Introducing MCP - Anthropic](https://www.anthropic.com/news/model-context-protocol)
- [Donating MCP to AAIF - Anthropic](https://www.anthropic.com/news/donating-the-model-context-protocol-and-establishing-of-the-agentic-ai-foundation)

### Agentic AI Trends
- [7 Agentic AI Trends 2026 - MachineLearningMastery](https://machinelearningmastery.com/7-agentic-ai-trends-to-watch-in-2026/)
- [5 Key Trends in Agentic Development 2026 - The New Stack](https://thenewstack.io/5-key-trends-shaping-agentic-development-in-2026/)
- [Best AI Coding Agents 2026 - Faros AI](https://www.faros.ai/blog/best-ai-coding-agents-2026)

### Spec-Driven Development
- [Design by Contract - Wikipedia](https://en.wikipedia.org/wiki/Design_by_contract)
- [API-First Development - Swagger](https://swagger.io/resources/articles/adopting-an-api-first-approach/)
- [Contract-First API Design - Microsoft](https://learn.microsoft.com/en-us/azure/architecture/best-practices/api-design)

### The 80% Problem & Comprehension Debt
- [The 80% Problem in Agentic Coding - Addy Osmani](https://addyo.substack.com/p/the-80-problem-in-agentic-coding)
- [Beyond Vibe Coding - Addy Osmani](https://beyond.addy.ie/)
- [Vibe Coding Hangover - DEV Community](https://dev.to/maximiliano_allende97/the-vibe-coding-hangover-why-im-returning-to-engineering-rigor-in-2026-49hl)

---

## Licencia

MIT License

---

**¿Listo para empezar?**

```bash
/workflows:plan my-feature
```

*Sistema actualizado: 28 Enero 2026 - Añadido The 80% Problem & Comprehension Debt (Addy Osmani)*
