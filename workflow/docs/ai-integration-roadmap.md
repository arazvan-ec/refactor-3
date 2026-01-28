# Roadmap de Integración IA - Workflow 2.0

## Visión General

Este roadmap detalla las tareas técnicas para evolucionar el Multi-Agent Workflow hacia una arquitectura compatible con los estándares de IA de 2026 (MCP, A2A, Agentic Memory).

---

## Fase 1: MCP Native Foundation

### Objetivo
Convertir los Skills existentes en MCP Servers para interoperabilidad con el ecosistema.

### Tareas

#### 1.1 Setup del SDK MCP
- [ ] Instalar `@modelcontextprotocol/sdk` (TypeScript) o `mcp` (Python)
- [ ] Crear estructura base para MCP servers
- [ ] Configurar build pipeline

#### 1.2 Migrar Skills a MCP
| Skill Actual | MCP Server | Prioridad |
|--------------|------------|-----------|
| `test-runner` | `mcp-server-test` | Alta |
| `lint-fixer` | `mcp-server-lint` | Alta |
| `git-sync` | `mcp-server-git` | Alta |
| `coverage-checker` | `mcp-server-coverage` | Media |
| `commit-formatter` | `mcp-server-commit` | Media |
| `consultant` | `mcp-server-consultant` | Media |
| `checkpoint` | `mcp-server-checkpoint` | Baja |
| `worktree-manager` | `mcp-server-worktree` | Baja |
| `changelog-generator` | `mcp-server-changelog` | Baja |
| `layer-validator` | `mcp-server-ddd` | Baja |

#### 1.3 Definir Schemas
```typescript
// Ejemplo: mcp-server-test
{
  "name": "workflow-test-runner",
  "version": "1.0.0",
  "tools": [
    {
      "name": "run_tests",
      "description": "Execute test suite for the project",
      "inputSchema": {
        "type": "object",
        "properties": {
          "path": { "type": "string", "description": "Path to test files" },
          "filter": { "type": "string", "description": "Test filter pattern" },
          "coverage": { "type": "boolean", "default": true }
        },
        "required": ["path"]
      }
    }
  ]
}
```

#### 1.4 Integrar Conectores Externos
- [ ] Configurar MCP server para GitHub
- [ ] Configurar MCP server para Slack (notificaciones)
- [ ] Configurar MCP server para PostgreSQL (si aplica)
- [ ] Documentar configuración en `.mcp/config.json`

#### 1.5 Registrar en MCP Registry
- [ ] Crear cuenta en MCP Registry
- [ ] Publicar servers verificados
- [ ] Añadir badges de compatibilidad al README

### Entregables
- 10 MCP Servers funcionales
- Documentación de configuración
- Tests de integración

---

## Fase 2: Agent Memory System

### Objetivo
Implementar memoria persistente para que los agentes aprendan entre sesiones.

### Tareas

#### 2.1 Evaluación de Vector DB
| Opción | Pros | Contras |
|--------|------|---------|
| ChromaDB | Local, open source, fácil | Menos escalable |
| Pinecone | Managed, escalable | Costo, dependencia |
| Weaviate | RAG-ready, GraphQL | Más complejo |
| Qdrant | Alto rendimiento | Menos maduro |

**Recomendación:** Empezar con **ChromaDB** local, migrar a cloud si necesario.

#### 2.2 Diseño de Esquema de Memoria
```python
# Tipos de memoria a persistir
MEMORY_TYPES = {
    "learning": {
        "description": "Patrones y decisiones aprendidas",
        "retention": "permanent",
        "example": "Usar DTO para comunicación entre capas"
    },
    "error": {
        "description": "Errores comunes y soluciones",
        "retention": "permanent",
        "example": "Error X se resuelve con Y"
    },
    "context": {
        "description": "Contexto de proyecto actual",
        "retention": "session",
        "example": "Arquitectura, dependencias, convenciones"
    },
    "decision": {
        "description": "Decisiones arquitecturales",
        "retention": "permanent",
        "example": "Por qué se eligió patrón X"
    }
}
```

#### 2.3 Implementar Memory Skill
```
plugins/multi-agent-workflow/skills/memory/
├── memory.md          # Documentación
├── memory.py          # Implementación
├── embeddings.py      # Generación de embeddings
├── retrieval.py       # Búsqueda semántica
└── config.yaml        # Configuración
```

#### 2.4 Integrar en Workflow Stages
- [ ] Planning: Recuperar decisiones previas similares
- [ ] Implementation: Recuperar soluciones a errores
- [ ] Review: Verificar consistencia con learnings
- [ ] Compound: Almacenar nuevos learnings

#### 2.5 API de Memoria
```python
class WorkflowMemory:
    def store(self, content: str, type: str, metadata: dict) -> str
    def retrieve(self, query: str, type: str, limit: int) -> List[Memory]
    def update(self, id: str, content: str) -> bool
    def delete(self, id: str) -> bool
    def search_similar(self, query: str, threshold: float) -> List[Memory]
```

### Entregables
- Memory skill funcional
- ChromaDB configurado
- Integración con 3 workflows
- Documentación de uso

---

## Fase 3: A2A Protocol Support

### Objetivo
Permitir comunicación estandarizada entre agentes internos y externos.

### Tareas

#### 3.1 Definir Agent Cards
```json
// agents/roles/planner/agent-card.json
{
  "name": "workflow-planner",
  "version": "1.0.0",
  "description": "Senior architect agent for feature planning and task breakdown",
  "url": "local://workflow/agents/planner",
  "capabilities": {
    "planning": true,
    "task_breakdown": true,
    "spec_generation": true,
    "estimation": false
  },
  "input_modes": ["text", "markdown"],
  "output_modes": ["text", "markdown", "yaml"],
  "authentication": {
    "type": "none"
  },
  "metadata": {
    "category": "roles",
    "workflow_version": "2.0.0"
  }
}
```

#### 3.2 Agent Cards por Categoría
| Categoría | Agentes | Cards |
|-----------|---------|-------|
| Roles | Planner, Backend, Frontend, QA | 4 |
| Review | Security, Performance, DDD, Code | 4 |
| Research | Codebase-Analyzer, Git-Historian, Dependency | 3 |
| Workflow | Bug-Reproducer, Spec-Analyzer, Style | 3 |
| Design | API-Designer, UI-Verifier | 2 |
| **Total** | | **16** |

#### 3.3 Implementar Discovery Service
```
.ai/a2a/
├── discovery.py       # Service discovery local
├── registry.json      # Registro de agentes
├── router.py          # Enrutamiento de mensajes
└── client.py          # Cliente A2A
```

#### 3.4 Protocolo de Comunicación
```python
# Mensaje A2A entre agentes
{
    "id": "msg-123",
    "from": "workflow-planner",
    "to": "workflow-backend",
    "task": {
        "type": "implement",
        "spec": "...",
        "constraints": ["TDD", "DDD"]
    },
    "context": {
        "feature": "user-auth",
        "priority": "high"
    }
}
```

### Entregables
- 16 Agent Cards
- Discovery service
- Router de mensajes
- Documentación A2A

---

## Fase 4: Tool Search & Optimization

### Objetivo
Optimizar uso de tokens mediante Tool Search y Programmatic Tool Calling.

### Tareas

#### 4.1 Configurar Tool Search
- [ ] Crear catálogo de herramientas en formato indexable
- [ ] Configurar Tool Search en Claude API
- [ ] Definir queries de búsqueda optimizadas

#### 4.2 Implementar Programmatic Tool Calling
- [ ] Identificar operaciones batch candidatas
- [ ] Crear execution environment
- [ ] Migrar operaciones repetitivas

#### 4.3 Métricas de Optimización
| Métrica | Antes | Objetivo |
|---------|-------|----------|
| Tokens por workflow | ~50k | ~15k |
| Herramientas en contexto | 10 | Dinámico |
| Tiempo de respuesta | - | -30% |

### Entregables
- Tool Search configurado
- Operaciones batch migradas
- Dashboard de métricas de tokens

---

## Fase 5: Computer Use Integration

### Objetivo
Piloto de computer use para UI-Verifier agent.

### Tareas

#### 5.1 Configurar Ambiente
- [ ] Configurar sandbox para computer use
- [ ] Instalar dependencias de captura de pantalla
- [ ] Configurar permisos de automatización

#### 5.2 Implementar UI-Verifier Enhanced
```python
class UIVerifierAgent:
    async def verify_component(self, component: str):
        # 1. Navegar a la URL del componente
        # 2. Capturar screenshot
        # 3. Analizar con vision
        # 4. Interactuar (clicks, inputs)
        # 5. Verificar estados
        # 6. Reportar resultados
```

#### 5.3 Casos de Uso
- [ ] Verificación visual de componentes React
- [ ] Testing de flujos de usuario
- [ ] Captura de regresiones visuales
- [ ] Validación de responsive design

### Entregables
- UI-Verifier con computer use
- Suite de verificaciones visuales
- Documentación de seguridad

---

## Fase 6: Observabilidad

### Objetivo
Dashboard de métricas para optimizar el workflow.

### Tareas

#### 6.1 Instrumentación
- [ ] Añadir logging estructurado a todos los stages
- [ ] Capturar métricas de tiempo
- [ ] Registrar uso de tokens
- [ ] Trackear errores y retries

#### 6.2 Métricas Dashboard
```yaml
metrics:
  workflow:
    - execution_time_total
    - execution_time_by_stage
    - success_rate
    - retry_count

  agents:
    - tokens_used_by_agent
    - tool_calls_by_agent
    - error_rate_by_agent

  quality:
    - test_coverage
    - lint_score
    - learnings_captured

  memory:
    - retrieval_hit_rate
    - storage_size
    - query_latency
```

#### 6.3 Alertas
- [ ] Workflow fallido
- [ ] Token budget exceeded
- [ ] Memory retrieval miss rate alto
- [ ] Test coverage bajo threshold

### Entregables
- Sistema de logging
- Dashboard (Grafana/custom)
- Alertas configuradas

---

## Timeline Sugerido

```
Fase 1: MCP Native          ████████░░░░░░░░░░░░  Sprint 1-2
Fase 2: Memory System       ░░░░████████░░░░░░░░  Sprint 2-3
Fase 3: A2A Protocol        ░░░░░░░░████████░░░░  Sprint 4-5
Fase 4: Tool Optimization   ░░░░░░░░░░░░████░░░░  Sprint 5-6
Fase 5: Computer Use        ░░░░░░░░░░░░░░████░░  Sprint 6-7
Fase 6: Observability       ░░░░░░░░░░░░░░░░████  Sprint 7-8
```

---

## Dependencias Técnicas

### Nuevas Dependencias
```yaml
# Python
mcp-sdk: ">=0.5.0"
chromadb: ">=0.4.0"
openai: ">=1.0.0"  # Para embeddings
pydantic: ">=2.0.0"
prometheus-client: ">=0.17.0"

# TypeScript (si aplica)
"@modelcontextprotocol/sdk": ">=0.5.0"
"@anthropic-ai/sdk": ">=0.20.0"
```

### Infraestructura
- ChromaDB: Local o Docker
- Prometheus: Para métricas (opcional)
- Grafana: Para dashboard (opcional)

---

## Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| MCP spec changes | Media | Alto | Pin versiones, seguir changelog |
| Performance ChromaDB | Baja | Medio | Benchmark temprano, plan de migración |
| Complejidad A2A | Media | Medio | Implementación incremental |
| Costos tokens | Media | Medio | Tool Search, monitoring |
| Breaking changes Claude API | Baja | Alto | Abstraction layer |

---

## Criterios de Éxito

### Fase 1 (MCP)
- [ ] 100% skills migrados a MCP
- [ ] Al menos 2 conectores externos funcionando
- [ ] Documentación completa

### Fase 2 (Memory)
- [ ] Retrieval accuracy > 80%
- [ ] Latencia < 500ms
- [ ] Integración en 3 workflows

### Fase 3 (A2A)
- [ ] 16 Agent Cards definidas
- [ ] Comunicación inter-agente funcional
- [ ] Al menos 1 agente externo integrado

### Fase 4 (Optimization)
- [ ] Reducción 60% uso de tokens
- [ ] Mantener o mejorar calidad

### Fase 5 (Computer Use)
- [ ] UI-Verifier con 5 casos de uso
- [ ] < 5% false positives

### Fase 6 (Observability)
- [ ] Dashboard funcional
- [ ] Alertas configuradas
- [ ] Histórico de 30 días

---

*Documento: Roadmap Técnico de Integración IA*
*Versión: 1.0*
*Fecha: Enero 2026*
