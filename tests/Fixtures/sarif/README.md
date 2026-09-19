# SARIF test fixtures

These files are the real, hand-crafted inputs used by the test suite. They are
kept out of `storage/app/sarif-uploads/`, which only holds local QA artifacts
and is gitignored.

| File | Purpose |
| --- | --- |
| `eslint-style.sarif` | Happy path. Eight-scenario coverage of the severity precedence rules, driver metadata and `codeFlows`. |
| `malformed.sarif` | Invalid JSON (`InvalidJson`). |
| `missing-runs.sarif` | Valid SARIF header but an empty `runs` array (`MissingRuns`). |
| `native-tool-report.json` | Native tool export (Semgrep/ZAP style) without a top-level `runs` array (`NotSarif`). |
| `unsupported-version.sarif` | Valid SARIF with a version other than 2.1.0 (`UnsupportedVersion`). |

## Why a single severity fixture is enough

The original plan called for two separate fixtures (a CodeQL-style file with an
explicit `result.level` and an ESLint-style file relying on the rule default).
The implemented `eslint-style.sarif` already exercises all three precedence
cases in one document, so splitting it would only duplicate the same assertions
without adding coverage:

1. `no-unused-vars`: no `result.level`, so it resolves from the rule's
   `defaultConfiguration.level` (`error`).
2. `no-console`: carries an explicit `result.level` (`note`) that wins over the
   rule default (`warning`).
3. `unknown-rule`/`eqeqeq`: no level anywhere, so they fall back to `warning`.

A separate CodeQL fixture would still be parsed by the exact same code path
(the normalizer is driver-agnostic), so the decision is to keep one fixture
that covers the three cases and document it here rather than maintain two
near-identical files.

Anything that needs a different shape (large result sets, unknown drivers,
missing `driver.version`) is generated inside the test that needs it instead of
being committed as a fixture.
