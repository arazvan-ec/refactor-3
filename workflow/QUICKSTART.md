# Quick Start - AI Workflow en 5 Minutos

> Empieza a desarrollar con IA en minutos, no horas.

## Prerequisitos

- Claude Code CLI instalado
- Git configurado
- Un proyecto existente o nuevo

---

## Paso 1: Instalar el Plugin (30 segundos)

```bash
# Añadir el workflow como plugin
/plugin marketplace add https://github.com/arazvan-ec/workflow
/plugin install multi-agent-workflow
```

---

## Paso 2: Tu Primera Feature (2 minutos)

### Planificar

```bash
# Ejemplo: Crear sistema de autenticación
/workflows:plan user-authentication
```

Esto genera automáticamente:
- Análisis de requisitos
- Arquitectura
- Contratos de API
- Tareas para backend y frontend

### Implementar

```bash
# Backend primero
/workflows:work user-authentication --mode=roles --role=backend

# Frontend después (o en paralelo en otra terminal)
/workflows:work user-authentication --mode=roles --role=frontend
```

### Revisar

```bash
# QA automático
/workflows:review user-authentication
```

---

## Paso 3: Entender el Flujo (1 minuto)

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   PLAN      │───▶│   WORK      │───▶│   REVIEW    │───▶│  COMPOUND   │
│  (80%)      │    │  (15%)      │    │   (4%)      │    │   (1%)      │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
   Planner          Backend/Frontend      QA              Learnings
```

**Regla de oro**: 80% planificación, 20% ejecución.

---

## Comandos Esenciales

| Comando | Qué hace | Cuándo usarlo |
|---------|----------|---------------|
| `/workflows:plan` | Planifica una feature | Siempre primero |
| `/workflows:work` | Implementa código | Después de planificar |
| `/workflows:review` | Revisa calidad | Antes de merge |
| `/workflows:status` | Ver progreso | Cuando quieras |
| `/workflows:sync` | Sincronizar estado | Entre sesiones |

---

## Conceptos Clave (30 segundos)

| Concepto | Significado |
|----------|-------------|
| **Compound Engineering** | Cada tarea hace las siguientes más fáciles |
| **50_state.md** | Archivo con el estado actual (fuente de verdad) |
| **Ralph Wiggum Loop** | Auto-corrección automática (máx 10 intentos) |
| **TDD** | Tests primero, código después |

> Ver [GLOSSARY.md](./GLOSSARY.md) para más términos.

---

## Siguiente Paso

1. **Tutorial completo**: [TUTORIAL.md](./TUTORIAL.md) - Ejemplo práctico paso a paso
2. **Documentación completa**: [README.md](./README.md) - Todo el detalle
3. **Índice de navegación**: [INDEX.md](./INDEX.md) - Mapa del repositorio

---

## Troubleshooting Rápido

### "No sé por dónde empezar"
```bash
/workflows:plan mi-feature
```
El planner te guiará.

### "El contexto se volvió muy largo"
```bash
# Checkpoint y nueva sesión
/workflows:sync
# Empezar nueva sesión de Claude
```

### "No entiendo un concepto"
Consulta [GLOSSARY.md](./GLOSSARY.md)

---

**Tiempo total**: ~5 minutos para tu primera feature planificada.
