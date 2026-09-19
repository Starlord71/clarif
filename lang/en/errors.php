<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Report failure messages
    |--------------------------------------------------------------------------
    |
    | User-facing messages for every App\Enums\ReportFailureReason case. These
    | are the only strings ever shown to the end user when ingestion fails;
    | technical details stay in the "sarif" log channel.
    |
    */

    'invalid_json' => 'The file is not valid JSON. Make sure it is a correctly exported SARIF report.',
    'missing_runs' => 'The file does not contain a processable SARIF run.',
    'unsupported_version' => 'This file\'s SARIF version is not supported yet.',
    'unknown' => 'An unexpected error occurred while processing the report. It has been logged internally.',

];
