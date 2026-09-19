<?php

return [

    /*
    |--------------------------------------------------------------------------
    | What is SARIF?
    |--------------------------------------------------------------------------
    |
    | User-facing content of the standalone explainer page. It is the plain
    | language counterpart of docs/scope. Run comparison lives on the help page.
    |
    */

    'title' => 'What is SARIF?',
    'intro_1' => 'SARIF stands for Static Analysis Results Interchange Format. It is an open JSON standard, published by OASIS, that many static analysis tools use to describe the issues and code quality problems they find.',
    'intro_2' => 'Every tool used to invent its own output format. SARIF gives them a common language: Clarif reads that single format, so you do not need to write a different adapter for each tool.',

    'tools_title' => 'Which tools does Clarif support?',
    'tools_intro' => 'Clarif accepts any valid SARIF 2.1.0 document. These tools are the ones it is designed and tested around:',
    'tools_codeql' => 'GitHub\'s semantic code analysis engine. It reports security and quality issues with explicit severity levels.',
    'tools_eslint' => 'The JavaScript and TypeScript linter. It usually expresses severity through each rule\'s default configuration, which Clarif resolves automatically.',
    'tools_semgrep' => 'A lightweight, multi-language static analysis tool used for security checks and custom rules.',
    'tools_zap' => 'OWASP ZAP, the web application security scanner. It exports SARIF and reports all four severity levels.',

    'severity_title' => 'How are severity levels interpreted?',
    'severity_intro' => 'SARIF only defines four abstract levels, which say little on their own. Clarif translates them into a clearer scale, while keeping the original level available on hover:',
    'severity_column_sarif' => 'SARIF level',
    'severity_column_label' => 'Clarif label',
    'severity_column_meaning' => 'Meaning',
    'severity_error_meaning' => 'The tool flagged a definite problem.',
    'severity_warning_meaning' => 'A potential problem worth reviewing.',
    'severity_note_meaning' => 'An informational observation.',
    'severity_none_meaning' => 'The tool did not assign a level.',
    'severity_caveat' => 'The label is derived from the SARIF level only. Some tools, such as CodeQL, also carry a finer numeric risk score in rule.properties.security-severity; Clarif does not interpret it in this version.',

    'scope_title' => 'How Clarif interprets a report',
    'scope_intro' => 'A few deliberate scope rules keep ingestion predictable and fast. They are known limitations, not bugs:',
    'scope_runs' => 'Only the first run (runs[0]) of each file is processed. A single SARIF file is treated as a single run.',
    'scope_codeflows' => 'Data flows (codeFlows) are preserved as-is inside each finding, but they are not broken down into separate records.',
    'scope_severity' => 'Severity is taken from the finding when present, then from the rule\'s default level, and finally falls back to warning.',

    'diffing_title' => 'And how do I compare two runs?',
    'diffing_intro' => 'Run comparison has its own guide, with the details of each finding\'s fingerprint and the known line drift limitation.',

];
