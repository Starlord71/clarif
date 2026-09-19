<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported SARIF version
    |--------------------------------------------------------------------------
    |
    | Clarif only accepts SARIF 2.1.0 documents. Any other version fails the
    | ingestion with ReportFailureReason::UnsupportedVersion.
    |
    */

    'supported_sarif_version' => '2.1.0',

    /*
    |--------------------------------------------------------------------------
    | Supported locales
    |--------------------------------------------------------------------------
    |
    | Locales offered by the language switcher. Spanish is an explicit user
    | choice; English remains the configured application default. Used to
    | validate both the switcher route and the value stored in the session.
    |
    */

    'supported_locales' => ['en', 'es'],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | Where uploaded SARIF files are stored and how large they may be. The
    | size limit is expressed in kilobytes, matching Laravel's "max" rule.
    |
    */

    'uploads' => [
        'disk' => env('CLARIF_UPLOAD_DISK', 'sarif'),
        'max_kilobytes' => (int) env('CLARIF_MAX_UPLOAD_KB', 51200),

        // Files up to this size (in kilobytes) are fully decoded during the
        // upload preflight, which guarantees the whole document is valid JSON
        // (no trailing garbage). Larger files fall back to a streaming header
        // check to avoid loading a huge report in memory.
        'preflight_max_kilobytes' => (int) env('CLARIF_PREFLIGHT_MAX_KB', 5120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Parsing
    |--------------------------------------------------------------------------
    |
    | Findings are streamed from the file and inserted in batches of this size.
    |
    */

    'parsing' => [
        'chunk_size' => (int) env('CLARIF_FINDINGS_CHUNK_SIZE', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Known SARIF drivers
    |--------------------------------------------------------------------------
    |
    | Used only to emit a warning when a file comes from an unrecognized
    | driver. Parsing never depends on this list.
    |
    */

    'known_drivers' => ['codeql', 'eslint', 'semgrep'],

    /*
    |--------------------------------------------------------------------------
    | Tool name placeholder
    |--------------------------------------------------------------------------
    |
    | The real driver name is only known once the job reads the file, so a
    | placeholder is stored when the Report is created.
    |
    */

    'unknown_tool_name' => 'unknown',

];
