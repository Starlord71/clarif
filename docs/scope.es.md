# Alcance y limitaciones de Clarif

**Read this in English: [scope.md](scope.md)**

Este documento lista qué hace Clarif y, con la misma importancia, qué decide no hacer. Son decisiones
de producto conscientes para la v1, no errores. Ver [architecture.es.md](architecture.es.md) para
cómo está construido el pipeline.

## Formato de entrada

Clarif acepta un único formato de entrada: **SARIF 2.1.0** (`runs[0]`). Los archivos cuyo `version`
no sea ese se rechazan durante la ingesta (`ReportFailureReason::UnsupportedVersion`). Las salidas
JSON nativas que no son documentos SARIF (por ejemplo, un JSON de Semgrep o ZAP sin arreglo `runs`)
también se rechazan (`ReportFailureReason::NotSarif`).

## Herramientas soportadas

Como SARIF es un estándar, Clarif no está atado a un productor específico: acepta **cualquier
documento SARIF 2.1.0 válido**. Las herramientas con las que está diseñado y probado son:

- CodeQL
- ESLint
- Semgrep
- OWASP ZAP

El nombre del driver se lee de `runs[0].tool.driver.name` y se guarda en el reporte. Un driver que no
está en `config('clarif.known_drivers')` solo emite una advertencia en el canal de log `sarif`; nunca
bloquea ni cambia el parsing.

## Reglas de alcance

### Solo se procesa el primer run

Un archivo SARIF puede contener varios runs. Clarif procesa **solo `runs[0]`** y trata el archivo
completo como un único run. Los runs adicionales se ignoran. Normalizar múltiples runs queda fuera de
alcance para la v1.

### Los flujos de código se conservan, no se normalizan

Los `codeFlows` (los rastros de flujo de datos paso a paso que algunas herramientas adjuntan a un
resultado) se guardan tal cual dentro de la columna JSONB `payload` de cada hallazgo. **No** se
descomponen en tablas relacionales. Esto mantiene el esquema chico y preserva el dato crudo para quien
lo necesite.

### La severidad sale del nivel de SARIF

Clarif resuelve la severidad de un hallazgo con esta precedencia:

1. El `level` del propio resultado, cuando está presente y es válido.
2. El `defaultConfiguration.level` de la regla referenciada.
3. El fallback de la especificación: `warning`.

Solo se leen `runs[0].results[].level` y
`runs[0].tool.driver.rules[].defaultConfiguration.level`. Nada más del documento influye en la
severidad.

## Etiquetas de severidad

SARIF define cuatro niveles abstractos que por sí solos dicen poco. Clarif guarda el valor SARIF
original (para que siga siendo rastreable) pero muestra una etiqueta clara, orientada a negocio,
disponible al pasar el cursor como nivel crudo:

| Nivel SARIF | Etiqueta de Clarif | Significado |
| --- | --- | --- |
| `error` | Alto | La herramienta marcó un problema definitivo. |
| `warning` | Medio | Un problema potencial que conviene revisar. |
| `note` | Bajo | Una observación informativa. |
| `none` | Info | La herramienta no asignó un nivel. |

### Limitación conocida: los puntajes numéricos de riesgo no se interpretan

Algunas herramientas traen una señal de riesgo más fina que el `level` de SARIF no puede expresar.
CodeQL, por ejemplo, agrega un puntaje numérico (0.0–10.0) en la regla bajo
`properties.security-severity`, y Semgrep agrega `properties.tags` como `"LOW CONFIDENCE"` o
`"security"`. Clarif **no interpreta esto en la v1**: la etiqueta se deriva únicamente de `level`, así
que no puede distinguir, por ejemplo, un puntaje de 9.5 de uno de 7.5. Interpretar puntajes numéricos
(con umbrales) es una posible mejora futura, no un comportamiento actual.

## Documentos relacionados

- [architecture.es.md](architecture.es.md) — cómo encajan la ingesta, el almacenamiento y las capas.
- [diffing.es.md](diffing.es.md) — cómo se comparan dos runs y la limitación del desplazamiento de
  líneas.
