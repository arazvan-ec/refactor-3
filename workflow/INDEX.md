# Índice de Navegación

> Mapa completo del repositorio para encontrar rápidamente lo que necesitas.

---

## Por Dónde Empezar

| Tu Situación | Lee Esto |
|--------------|----------|
| **Nuevo aquí** | [QUICKSTART.md](./QUICKSTART.md) |
| **Quiero un ejemplo** | [TUTORIAL.md](./TUTORIAL.md) |
| **No entiendo un término** | [GLOSSARY.md](./GLOSSARY.md) |
| **Quiero todo el detalle** | [README.md](./README.md) |
| **Soy un agente Claude** | [CLAUDE.md](./CLAUDE.md) |

---

## Estructura del Repositorio

```
workflow/
├── 📄 QUICKSTART.md          # Guía de 5 minutos
├── 📄 TUTORIAL.md            # Ejemplo práctico completo
├── 📄 GLOSSARY.md            # Definiciones de términos
├── 📄 INDEX.md               # Este archivo
├── 📄 README.md              # Documentación completa
├── 📄 CLAUDE.md              # Guía para agentes Claude
│
└── plugins/
    └── multi-agent-workflow/
        ├── 📁 agents/        # Agentes especializados
        ├── 📁 workflows/     # Comandos ejecutables
        ├── 📁 skills/        # Utilidades reutilizables
        └── 📁 rules/         # Reglas del proyecto
```

---

## Guías y Documentación

| Archivo | Descripción | Audiencia |
|---------|-------------|-----------|
| [QUICKSTART.md](./QUICKSTART.md) | Empezar en 5 minutos | Nuevos usuarios |
| [TUTORIAL.md](./TUTORIAL.md) | Ejemplo paso a paso | Todos |
| [GLOSSARY.md](./GLOSSARY.md) | Diccionario de términos | Todos |
| [README.md](./README.md) | Documentación completa | Referencia |
| [CLAUDE.md](./CLAUDE.md) | Guía técnica para IA | Agentes Claude |

---

## Reglas del Proyecto

| Archivo | Propósito | Cuándo Consultarlo |
|---------|-----------|-------------------|
| [global_rules.md](./plugins/multi-agent-workflow/rules/global_rules.md) | Reglas universales | Siempre |
| [ddd_rules.md](./plugins/multi-agent-workflow/rules/ddd_rules.md) | Arquitectura DDD | Backend |

---

## Agentes Disponibles

### Roles Principales

| Agente | Archivo | Función |
|--------|---------|---------|
| **Planner** | [planner.md](./plugins/multi-agent-workflow/agents/roles/planner.md) | Planifica features y arquitectura |
| **Backend** | [backend.md](./plugins/multi-agent-workflow/agents/roles/backend.md) | Implementa APIs y lógica servidor |
| **Frontend** | [frontend.md](./plugins/multi-agent-workflow/agents/roles/frontend.md) | Implementa UI React/TypeScript |
| **QA** | [qa.md](./plugins/multi-agent-workflow/agents/roles/qa.md) | Testing y validación |

### Agentes de Review

| Agente | Archivo | Especialidad |
|--------|---------|--------------|
| **Security** | [security-review.md](./plugins/multi-agent-workflow/agents/review/security-review.md) | OWASP, vulnerabilidades |
| **Performance** | [performance-review.md](./plugins/multi-agent-workflow/agents/review/performance-review.md) | Optimización, N+1 |
| **DDD Compliance** | [ddd-compliance.md](./plugins/multi-agent-workflow/agents/review/ddd-compliance.md) | Arquitectura correcta |
| **Code Review** | [code-review-ts.md](./plugins/multi-agent-workflow/agents/review/code-review-ts.md) | Estándares TypeScript |

### Agentes de Research

| Agente | Archivo | Especialidad |
|--------|---------|--------------|
| **Codebase Analyzer** | [codebase-analyzer.md](./plugins/multi-agent-workflow/agents/research/codebase-analyzer.md) | Analiza estructura existente |
| **Git Historian** | [git-historian.md](./plugins/multi-agent-workflow/agents/research/git-historian.md) | Historial y decisiones |
| **Dependency Auditor** | [dependency-auditor.md](./plugins/multi-agent-workflow/agents/research/dependency-auditor.md) | Vulnerabilidades deps |

### Agentes de Workflow

| Agente | Archivo | Especialidad |
|--------|---------|--------------|
| **Bug Reproducer** | [bug-reproducer.md](./plugins/multi-agent-workflow/agents/workflow/bug-reproducer.md) | Reproducir y documentar bugs |
| **Spec Analyzer** | [spec-analyzer.md](./plugins/multi-agent-workflow/agents/workflow/spec-analyzer.md) | Validar vs especificaciones |
| **Style Enforcer** | [style-enforcer.md](./plugins/multi-agent-workflow/agents/workflow/style-enforcer.md) | Estándares de código |

### Agentes de Design

| Agente | Archivo | Especialidad |
|--------|---------|--------------|
| **API Designer** | [api-designer.md](./plugins/multi-agent-workflow/agents/design/api-designer.md) | Contratos RESTful |
| **UI Verifier** | [ui-verifier.md](./plugins/multi-agent-workflow/agents/design/ui-verifier.md) | Validación UI/UX |

---

## Workflows (Comandos)

| Comando | Descripción | Cuándo Usarlo |
|---------|-------------|---------------|
| `/workflows:plan` | Planificar feature | Inicio de feature |
| `/workflows:work` | Implementar código | Después de planificar |
| `/workflows:review` | Review de calidad | Antes de merge |
| `/workflows:compound` | Capturar learnings | Después de approval |
| `/workflows:role` | Trabajar como rol | Desarrollo específico |
| `/workflows:sync` | Sincronizar estado | Entre sesiones |
| `/workflows:status` | Ver estado actual | Cualquier momento |

---

## Skills (Utilidades)

| Skill | Ubicación | Función |
|-------|-----------|---------|
| **consultant** | `skills/core/consultant/` | Consulta y análisis |
| **checkpoint** | `skills/core/checkpoint/` | Guardar/restaurar estado |
| **git-sync** | `skills/core/git-sync/` | Sincronización git |
| **test-runner** | `skills/quality/test-runner/` | Ejecutar tests |
| **coverage-checker** | `skills/quality/coverage-checker/` | Validar cobertura |
| **lint-fixer** | `skills/quality/lint-fixer/` | Arreglar estilo |
| **worktree-manager** | `skills/workflow/worktree-manager/` | Gestión worktrees |
| **commit-formatter** | `skills/workflow/commit-formatter/` | Formato commits |
| **changelog-generator** | `skills/compound/changelog-generator/` | Generar changelogs |
| **layer-validator** | `skills/compound/layer-validator/` | Validar capas DDD |

---

## Metodologías Documentadas

| Metodología | Sección en README | Origen |
|-------------|-------------------|--------|
| Compound Engineering | §1 | Evolución Vibe Coding 2025 |
| Ralph Wiggum Pattern | §2 | Geoffrey Huntley |
| Agent Harnesses | §3 | Tendencia 2026 |
| Agent-Native Architecture | §4 | Diseño para agentes |
| Model Context Protocol | §5 | Anthropic |
| Multi-Agent Coordination | §6 | Orquestación |
| TDD + DDD | §7 | Best practices |
| Context Window Management | §8 | Optimización |
| Spec-Driven Development | §9 | Contratos primero |
| The 70% Problem | §10 | Addy Osmani |

---

## Flujo de Desarrollo Recomendado

```
1. QUICKSTART.md   → Instalación básica (5 min)
2. TUTORIAL.md     → Ejemplo práctico (30-45 min)
3. GLOSSARY.md     → Consultar términos confusos
4. README.md       → Profundizar en metodologías
5. global_rules.md → Entender reglas del proyecto
6. Agentes         → Personalizar según necesidad
```

---

## FAQ de Navegación

**¿Dónde encuentro las reglas de arquitectura DDD?**
→ [ddd_rules.md](./plugins/multi-agent-workflow/rules/ddd_rules.md)

**¿Cómo personalizo los agentes?**
→ Edita los archivos `.md` en `plugins/multi-agent-workflow/agents/`

**¿Dónde está el estado de mi feature?**
→ `.ai/project/features/{feature-name}/50_state.md`

**¿Cómo agrego una nueva regla global?**
→ Edita [global_rules.md](./plugins/multi-agent-workflow/rules/global_rules.md)

**¿Qué archivo lee Claude primero?**
→ [CLAUDE.md](./CLAUDE.md)

---

## Actualizaciones Recientes

| Fecha | Cambio | Archivo Afectado |
|-------|--------|------------------|
| 2026-01 | Evolution Governance | global_rules.md |
| 2026-01 | Beyond Vibe Coding | README.md |
| 2026-01 | Spec-Driven Development | README.md |
| 2026-01 | Mejoras de usabilidad | QUICKSTART, TUTORIAL, GLOSSARY, INDEX |
