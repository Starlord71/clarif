<?php

return [

    /*
    |--------------------------------------------------------------------------
    | UI de reportes
    |--------------------------------------------------------------------------
    |
    | Todos los textos visibles del listado, formulario de subida, detalle y
    | comparación de reportes. Las vistas los referencian vía __(); no hay
    | texto hardcodeado en las plantillas Blade.
    |
    */

    'brand' => 'Clarif',
    'nav_reports' => 'Reportes',
    'nav_upload' => 'Subir',
    'nav_about' => '¿Qué es SARIF?',
    'language_label' => 'Idioma',

    // Listado.
    'reports_title' => 'Reportes',
    'reports_subtitle' => 'Todos los reportes SARIF ingeridos por Clarif.',
    'reports_empty' => 'Todavía no hay reportes.',
    'reports_empty_hint' => 'Sube tu primer archivo SARIF para empezar.',
    'upload_report' => 'Subir reporte',
    'column_number' => 'N°',
    'column_file' => 'Archivo',
    'column_tool' => 'Herramienta',
    'column_status' => 'Estado',
    'column_findings' => 'Hallazgos',
    'column_uploaded' => 'Subido',
    'column_actions' => 'Acciones',
    'action_view' => 'Ver',
    'action_delete' => 'Eliminar',
    'delete_modal_title' => 'Eliminar reporte',
    'delete_modal_body' => '¿Seguro que quieres eliminar este reporte? Se van a borrar todos sus hallazgos. Esta acción no se puede deshacer.',
    'delete_confirm_yes' => 'Sí, eliminar',
    'delete_cancel' => 'Cancelar',
    'report_deleted' => 'El reporte se eliminó correctamente.',
    'delete_failed' => 'No se pudo eliminar el reporte. Intenta de nuevo.',

    // Etiquetas de estado y severidad (usadas por los métodos label() de los enums).
    'statuses' => [
        'pending' => 'Pendiente',
        'processing' => 'Procesando',
        'completed' => 'Completado',
        'failed' => 'Fallido',
    ],

    'severities' => [
        'error' => 'Error',
        'warning' => 'Advertencia',
        'note' => 'Nota',
        'none' => 'Ninguna',
    ],

    // Formulario de subida.
    'upload_title' => 'Subir reporte SARIF',
    'upload_heading' => 'Subir un reporte SARIF',
    'upload_description' => 'Selecciona un archivo .sarif o .json exportado por CodeQL, ESLint o Semgrep. Clarif lo procesa en segundo plano y normaliza sus hallazgos.',
    'upload_field_label' => 'Archivo SARIF',
    'upload_choose_file' => 'Elegir archivo',
    'upload_no_file' => 'Ningún archivo seleccionado',
    'upload_submit' => 'Subir y procesar',
    'upload_formats_hint' => 'Formatos aceptados: .sarif o .json en formato SARIF 2.1.0. Tamaño máximo: :size MB.',
    'upload_tips_title' => 'Antes de subir',
    'upload_tip_one' => 'El archivo debe ser un documento SARIF 2.1.0. Las salidas JSON nativas de herramientas como Semgrep o ZAP no se aceptan.',
    'upload_tip_two' => 'Solo se procesa el primer run (runs[0]) del archivo.',
    'upload_tip_three' => 'El procesamiento corre en segundo plano; el estado se actualiza en la página del reporte.',
    'back_to_reports' => 'Volver a reportes',

    // Detalle.
    'report_title' => 'Reporte #:id',
    'detail_filename' => 'Archivo',
    'detail_tool' => 'Herramienta',
    'detail_version' => 'Versión de la herramienta',
    'detail_status' => 'Estado',
    'detail_total_findings' => 'Hallazgos totales',
    'detail_uploaded' => 'Subido',
    'detail_unknown' => 'Desconocida',

    'failure_title' => 'Este reporte no se pudo procesar',
    'failure_reference' => 'Referencia: #:id',

    // Filtros del detalle.
    'filters_title' => 'Filtrar hallazgos',
    'filter_severity' => 'Severidad',
    'filter_rule' => 'ID de regla',
    'filter_file' => 'Ruta de archivo',
    'filter_all' => 'Todas las severidades',
    'filter_apply' => 'Aplicar',
    'filter_reset' => 'Limpiar',
    'filter_rule_placeholder' => 'ej. no-unused-vars',
    'filter_file_placeholder' => 'ej. src/app.js',

    // Tabla de hallazgos.
    'findings_title' => 'Hallazgos',
    'findings_empty' => 'Este reporte no tiene hallazgos.',
    'findings_no_match' => 'Ningún hallazgo coincide con los filtros actuales.',
    'column_severity' => 'Severidad',
    'column_rule' => 'Regla',
    'column_line' => 'Línea',
    'column_message' => 'Mensaje',
    'value_unknown' => '-',
    'findings_view_detail' => 'Ver detalle',
    'finding_modal_title' => 'Detalle del hallazgo',
    'finding_modal_close' => 'Cerrar',

    // Paginación.
    'per_page_label' => 'Por página',
    'pagination_previous' => 'Anterior',
    'pagination_next' => 'Siguiente',
    'pagination_goto' => 'Ir a la página :page',
    'pagination_navigation' => 'Navegación de paginación',
    'pagination_summary' => 'Mostrando :first a :last de :total',

];
