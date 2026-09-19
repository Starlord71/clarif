# Clarif scope and limitations

**Leer en español: [scope.es.md](scope.es.md)**

This document lists what Clarif does and, just as importantly, what it deliberately does not do.
These are conscious product decisions for v1, not bugs. See
[architecture.md](architecture.md) for how the pipeline is built.

## Input format

Clarif accepts a single input format: **SARIF 2.1.0** (`runs[0]`). Files whose `version` is anything
else are rejected during ingestion (`ReportFailureReason::UnsupportedVersion`). Native JSON exports
that are not SARIF documents (for example, a Semgrep or ZAP JSON export without a `runs` array) are
rejected as well (`ReportFailureReason::NotSarif`).

## Supported tools

Because SARIF is a standard, Clarif is not tied to any specific producer: it accepts **any valid SARIF
2.1.0 document**. The tools it is designed and tested around are:

- CodeQL
- ESLint
- Semgrep
- OWASP ZAP

The driver name is read from `runs[0].tool.driver.name` and stored on the report. A driver that is not
in `config('clarif.known_drivers')` only emits a warning in the `sarif` log channel; it never blocks
or changes parsing.

## Scope rules

### Only the first run is processed

A SARIF file can contain several runs. Clarif processes **only `runs[0]`** and treats the whole file
as a single run. Additional runs are ignored. Normalizing multiple runs is out of scope for v1.

### Code flows are preserved, not normalized

`codeFlows` (the step-by-step data-flow traces some tools attach to a result) are kept verbatim inside
each finding's `payload` JSONB column. They are **not** broken down into relational tables. This keeps
the schema small while preserving the raw data for anyone who needs it.

### Severity comes from the SARIF level

Clarif resolves a finding's severity with this precedence:

1. The result's own `level`, when present and valid.
2. The referenced rule's `defaultConfiguration.level`.
3. The specification fallback: `warning`.

Only `runs[0].results[].level` and `runs[0].tool.driver.rules[].defaultConfiguration.level` are read.
Nothing else in the document influences severity.

## Severity labels

SARIF defines four abstract levels that say little on their own. Clarif stores the original SARIF
value (so it stays traceable) but shows a clear, business-friendly label, available on hover as the
raw level:

| SARIF level | Clarif label | Meaning |
| --- | --- | --- |
| `error` | High | The tool flagged a definite problem. |
| `warning` | Medium | A potential problem worth reviewing. |
| `note` | Low | An informational observation. |
| `none` | Info | The tool did not assign a level. |

### Known limitation: numeric risk scores are not interpreted

Some tools carry a finer risk signal that SARIF `level` cannot express. CodeQL, for example, adds a
numeric score (0.0–10.0) on the rule under `properties.security-severity`, and Semgrep adds
`properties.tags` such as `"LOW CONFIDENCE"` or `"security"`. Clarif **does not interpret these in
v1**: the label is derived from `level` alone, so it cannot distinguish, say, a score of 9.5 from one
of 7.5. Interpreting numeric scores (with thresholds) is a possible future enhancement, not a current
behavior.

## Related documents

- [architecture.md](architecture.md) — how ingestion, storage and the layers fit together.
- [diffing.md](diffing.md) — how two runs are compared and the line drift limitation.
