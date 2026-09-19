<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation messages
    |--------------------------------------------------------------------------
    |
    | Only the rules used by the SARIF upload are defined here, so validation
    | errors follow the language chosen in the UI instead of the framework
    | defaults. Add new entries as new rules are introduced.
    |
    */

    'required' => 'The :attribute field is required.',
    'file' => 'The :attribute must be a file.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'uploaded' => 'The :attribute failed to upload.',

    'max' => [
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
    ],

    'attributes' => [
        'report' => 'SARIF file',
    ],

];
