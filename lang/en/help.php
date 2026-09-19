<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Help
    |--------------------------------------------------------------------------
    |
    | Content of the help page: how Clarif's workflow works and, in detail,
    | how comparing two runs works.
    |
    */

    'title' => 'Help',
    'subtitle' => 'How Clarif works and how two runs are compared.',

    'workflow_title' => 'How the workflow works',
    'workflow_intro' => 'Clarif works in three simple steps:',
    'workflow_step_one' => 'You upload a SARIF 2.1.0 file exported by CodeQL, ESLint, or Semgrep.',
    'workflow_step_two' => 'Clarif parses the file in the background and stores every finding in a normalized form.',
    'workflow_step_three' => 'In the listing you select two reports and compare them.',

    'compare_title' => 'How to compare two runs',
    'compare_intro' => 'A comparison works like a diff between two moments of the same project. The base run is the older one (the past) and the head run is the newer one (the present). Clarif shows you what changed between them.',

    'compare_how_title' => 'How Clarif decides whether a finding is the same',
    'compare_how_body' => 'Every finding has a fingerprint built from its rule, file, and estimated line. Two findings are the same when their fingerprint matches across runs. On top of that identity they are classified into three groups:',

    'compare_new_title' => 'New findings',
    'compare_new_body' => 'Present in the head run but not in the base run: the issue appeared afterwards.',
    'compare_resolved_title' => 'Resolved findings',
    'compare_resolved_body' => 'Present in the base run but no longer in the head run: the issue was fixed.',
    'compare_persistent_title' => 'Persistent findings',
    'compare_persistent_body' => 'Present in both runs: the issue is still there.',

    'compare_drift_title' => 'Known limitation: line drift',
    'compare_drift_body' => 'Because the fingerprint depends on the line number, unrelated changes that add or remove lines above a finding can shift it and make it look new or resolved even though the issue is the same. Tools such as SonarQube address this with fingerprints based on the surrounding code context. Clarif does not solve it yet: it is a known and conscious limitation, not a bug.',

];
