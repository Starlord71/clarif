# Comparación de runs y el problema del desplazamiento de líneas

**Read this in English: [diffing.md](diffing.md)**

Clarif compara dos runs (un `base` y un `head`) para responder la pregunta que de verdad le importa a
quien usa análisis estático: **¿qué cambió desde la última vez?** Los hallazgos se agrupan en tres
conjuntos:

- **Nuevos** — están en `head` pero no en `base`.
- **Resueltos** — estaban en `base` pero ya no en `head`.
- **Persistentes** — están en ambos.

## Cómo se identifica un hallazgo

Un hallazgo necesita una identidad estable entre runs. Clarif construye una huella así:

```
sha256(rule_id | file_path | line)
```

donde `line` es la línea estimada de la ubicación física del SARIF. Dos hallazgos son el mismo cuando
su huella coincide. Esa identidad es toda la base de la comparación; el `payload` crudo nunca se
compara. Como la comparación solo agrupa huellas en conjuntos, es lógica pura sin acceso a base de
datos ni al sistema de archivos, y está cubierta por tests unitarios con conjuntos de huellas fijos.

## El problema del desplazamiento de líneas (limitación conocida)

La huella depende del número de línea, y los números de línea se mueven. Pensá en un hallazgo en la
línea 42:

```diff
  function handle(input) {
+   const normalized = input.trim();
    return eval(normalized);   // finding: insecure eval
  }
```

Agregar una línea por encima del hallazgo lo desplaza a la línea 43. Su regla, archivo y mensaje no
cambiaron, pero la huella `rule_id | file_path | 42` ya no coincide con `rule_id | file_path | 43`. En
consecuencia:

- En una comparación donde el run anterior es `base`, el hallazgo aparece como **nuevo** y
  **resuelto** a la vez, aunque nada del problema haya cambiado.

Este es el **problema del desplazamiento de líneas**. Produce entradas falsas de "nuevo" y "resuelto"
cada vez que cambios no relacionados agregan o quitan líneas por encima de un hallazgo.

## Por qué no se resuelve en la v1

Resolverlo bien implica no depender solo del número de línea. Herramientas como SonarQube construyen
una huella a partir del **contexto de código que rodea** al hallazgo (una ventana de líneas alrededor)
en lugar de una sola línea, lo que mantiene la identidad estable ante pequeños desplazamientos. Clarif
**no** implementa huellas basadas en contexto en la v1, de forma consciente:

- Agrega complejidad real (leer y hashear el código alrededor de cada hallazgo, decidir cuánto
  contexto, manejar código movido o reformateado).
- La huella actual es simple, determinista y barata, suficiente para el flujo de un solo usuario
  previsto.
- La limitación se documenta en lugar de esconderse, así los resultados se leen con expectativas
  correctas.

**Guía práctica:** tratá los runs como comparables cuando el conjunto de cambios entre ambos no
reordena ni reindenta regiones grandes de un archivo. Cuando lo hace, esperá algo de desplazamiento
de líneas en las listas de nuevos/resueltos.

## Documentos relacionados

- [scope.es.md](scope.es.md) — formato de entrada, reglas de alcance y manejo de severidad.
- [architecture.es.md](architecture.es.md) — dónde vive el servicio de comparación en las capas.
