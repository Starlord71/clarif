# Run comparison and the line drift problem

**Leer en español: [diffing.es.md](diffing.es.md)**

Clarif compares two runs (a `base` and a `head`) to answer the question static analysis users actually
care about: **what changed since last time?** Findings are grouped into three sets:

- **New** — present in `head` but not in `base`.
- **Resolved** — present in `base` but not in `head`.
- **Persistent** — present in both.

## How a finding is identified

A finding needs a stable identity across runs. Clarif builds a fingerprint as:

```
sha256(rule_id | file_path | line)
```

where `line` is the estimated line from the SARIF physical location. Two findings are considered the
same when their fingerprint matches. That identity is the whole basis of the comparison; the raw
`payload` is never compared. Because the comparison only groups fingerprints into sets, it is pure
logic with no database or filesystem access, and it is unit tested with fixed fingerprint sets.

## The line drift problem (known limitation)

The fingerprint depends on the line number, and line numbers move. Consider a finding on line 42:

```diff
  function handle(input) {
+   const normalized = input.trim();
    return eval(normalized);   // finding: insecure eval
  }
```

Adding a line above the finding shifts it to line 43. Its rule, file and message are unchanged, but the
fingerprint `rule_id | file_path | 42` no longer matches `rule_id | file_path | 43`. As a result:

- In a comparison where the earlier run is `base`, the finding is reported as **new** and **resolved**
  at the same time, even though nothing about the issue changed.

This is the **line drift problem**. It produces false "new" and "resolved" entries whenever unrelated
changes add or remove lines above a finding.

## Why it is not fixed in v1

Solving it properly means not relying on the line number alone. Tools such as SonarQube build a
fingerprint from the **surrounding code context** (a window of lines around the finding) rather than a
single line, which keeps the identity stable across small shifts. Clarif consciously does **not**
implement context-based fingerprints in v1:

- It adds real complexity (reading and hashing source around each finding, deciding how much context,
  handling moved or reformatted code).
- The current fingerprint is simple, deterministic and cheap, which is enough for the intended
  single-user workflow.
- The limitation is documented rather than hidden, so results are read with the right expectations.

**Practical guidance:** treat runs as comparable when the change set between them does not reorder or
re-indent large regions of a file. When it does, expect some line drift in the new/resolved lists.

## Related documents

- [scope.md](scope.md) — input format, scope rules and severity handling.
- [architecture.md](architecture.md) — where the comparison service lives in the layering.
