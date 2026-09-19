<?php

return [

    /*
    |--------------------------------------------------------------------------
    | What is SARIF?
    |--------------------------------------------------------------------------
    |
    | User-facing content of the standalone explainer page. It is the plain
    | language counterpart of docs/scope and docs/diffing.
    |
    */

    'title' => 'What is SARIF?',
    'intro_1' => 'SARIF stands for Static Analysis Results Interchange Format. It is an open JSON standard, published by OASIS, that many static analysis tools use to describe the issues and code quality problems they find.',
    'intro_2' => 'Every tool used to invent its own output format. SARIF gives them a common language: Clarif reads that single format, so you do not need to write a different adapter for each tool.',

    'tools_title' => 'Which tools does Clarif support?',
    'tools_intro' => 'Clarif accepts any valid SARIF 2.1.0 document. These three tools are the ones it is designed and tested around:',
    'tools_codeql' => 'GitHub\'s semantic code analysis engine. It reports security and quality issues with explicit severity levels.',
    'tools_eslint' => 'The JavaScript and TypeScript linter. It usually expresses severity through each rule\'s default configuration, which Clarif resolves automatically.',
    'tools_semgrep' => 'A lightweight, multi-language static analysis tool used for security checks and custom rules.',

    'scope_title' => 'How Clarif interprets a report',
    'scope_intro' => 'A few deliberate scope rules keep ingestion predictable and fast. They are known limitations, not bugs:',
    'scope_runs' => 'Only the first run (runs[0]) of each file is processed. A single SARIF file is treated as a single run.',
    'scope_codeflows' => 'Data flows (codeFlows) are preserved as-is inside each finding, but they are not broken down into separate records.',
    'scope_severity' => 'Severity is taken from the finding when present, then from the rule\'s default level, and finally falls back to warning.',

    'diffing_title' => 'Comparing two runs and the line drift problem',
    'diffing_1' => 'Clarif identifies the same finding across two runs using a fingerprint built from its rule, file, and estimated line. That is what lets a comparison tell new findings apart from fixed and persistent ones.',
    'diffing_2' => 'The limitation is that if unrelated changes add or remove lines above a finding, its line number shifts and the fingerprint no longer matches, even though the issue is the same. Tools such as SonarQube address this with fingerprints based on the surrounding code context. Clarif does not solve it in its current version; it is a known and conscious trade-off.',

];
