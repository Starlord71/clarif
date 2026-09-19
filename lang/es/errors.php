<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de fallo de reportes
    |--------------------------------------------------------------------------
    |
    | Mensajes visibles para el usuario final por cada caso de
    | App\Enums\ReportFailureReason. Son los únicos textos que se muestran
    | cuando falla la ingesta; el detalle técnico queda en el canal "sarif".
    |
    */

    'invalid_json' => 'El archivo no es un JSON válido. Verifica que sea un reporte SARIF exportado correctamente.',
    'missing_runs' => 'El archivo no contiene un run de SARIF procesable.',
    'not_sarif' => 'Este archivo no es un reporte SARIF. Sube un archivo .sarif o un .json en formato SARIF.',
    'unsupported_version' => 'La versión de SARIF de este archivo no está soportada todavía.',
    'unknown' => 'Ocurrió un error inesperado al procesar el reporte. Ya quedó registrado internamente.',

];
