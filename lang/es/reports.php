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
    'nav_help' => 'Ayuda',
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

    // Comparación.
    'column_select' => 'Seleccionar',
    'select_report' => 'Seleccionar reporte #:id',
    'compare_action' => 'Comparar',
    'compare_selection_hint' => 'Seleccioná exactamente dos reportes para compararlos.',
    'compare_selection_ready' => 'Dos reportes seleccionados. Listo para comparar.',
    'compare_selection_error' => 'Seleccioná exactamente dos reportes para comparar.',
    'compare_title' => 'Comparar runs',
    'compare_subtitle' => 'Hallazgos clasificados como nuevos, resueltos o persistentes entre dos runs.',
    'compare_base_label' => 'Run base',
    'compare_head_label' => 'Run head',
    'compare_new_title' => 'Hallazgos nuevos',
    'compare_new_description' => 'Presentes en el run head pero no en el run base.',
    'compare_new_empty' => 'No hay hallazgos nuevos.',
    'compare_resolved_title' => 'Hallazgos resueltos',
    'compare_resolved_description' => 'Presentes en el run base pero ya no en el run head.',
    'compare_resolved_empty' => 'No hay hallazgos resueltos.',
    'compare_persistent_title' => 'Hallazgos persistentes',
    'compare_persistent_description' => 'Presentes en ambos runs.',
    'compare_persistent_empty' => 'No hay hallazgos persistentes.',
    'compare_line_drift_note' => 'Los hallazgos se comparan por una huella de regla, archivo y línea estimada. Si cambios no relacionados desplazan la línea de un hallazgo, la huella deja de coincidir aunque el problema sea el mismo (el conocido "line drift problem"). Es una limitación consciente de v1, no un bug.',

    // Ayuda de comparación (explicación plegable al inicio de la vista comparar).
    'compare_help_summary' => '¿Qué estoy viendo?',
    'compare_help_intro' => 'Esta vista compara dos runs del mismo proyecto: el run base (más antiguo) y el run head (más nuevo). Cada hallazgo se empareja por una huella construida con su regla, su archivo y su línea estimada, así que el base siempre se lee como el pasado y el head como el presente.',
    'compare_help_fingerprint' => 'Como el emparejamiento depende del número de línea, cambios no relacionados que agreguen o quiten líneas por encima de un hallazgo pueden desplazarlo y hacer que parezca nuevo o resuelto aunque el problema sea el mismo (el "line drift problem"). Es una limitación conocida de esta versión, no un bug.',

    // Paginación.
    'per_page_label' => 'Por página',
    'pagination_previous' => 'Anterior',
    'pagination_next' => 'Siguiente',
    'pagination_goto' => 'Ir a la página :page',
    'pagination_navigation' => 'Navegación de paginación',
    'pagination_summary' => 'Mostrando :first a :last de :total',

];
