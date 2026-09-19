<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ayuda
    |--------------------------------------------------------------------------
    |
    | Contenido de la página de ayuda: cómo funciona el flujo de trabajo de
    | Clarif y, en detalle, cómo comparar dos runs.
    |
    */

    'title' => 'Ayuda',
    'subtitle' => 'Cómo funciona Clarif y cómo se comparan dos runs.',

    'workflow_title' => 'Cómo funciona el flujo',
    'workflow_intro' => 'Clarif trabaja en tres pasos simples:',
    'workflow_step_one' => 'Subes un archivo SARIF 2.1.0 exportado por una herramienta como CodeQL, ESLint, Semgrep u OWASP ZAP.',
    'workflow_step_two' => 'Clarif procesa el archivo en segundo plano y guarda cada hallazgo de forma normalizada.',
    'workflow_step_three' => 'En el listado seleccionás dos reportes y los comparás.',

    'compare_title' => 'Cómo comparar dos runs',
    'compare_intro' => 'La comparación funciona como un diff entre dos momentos del mismo proyecto. El run base es el más antiguo (el pasado) y el run head el más nuevo (el presente). Clarif te muestra qué cambió entre uno y otro.',

    'compare_how_title' => 'Cómo decide Clarif si un hallazgo es el mismo',
    'compare_how_body' => 'Cada hallazgo tiene una huella construida con su regla, su archivo y su línea estimada. Dos hallazgos son el mismo cuando su huella coincide entre runs. Sobre esa identidad se clasifican en tres grupos:',

    'compare_new_title' => 'Hallazgos nuevos',
    'compare_new_body' => 'Están en el run head pero no en el run base: el problema apareció después.',
    'compare_resolved_title' => 'Hallazgos resueltos',
    'compare_resolved_body' => 'Estaban en el run base pero ya no en el head: el problema se corrigió.',
    'compare_persistent_title' => 'Hallazgos persistentes',
    'compare_persistent_body' => 'Están en ambos runs: el problema sigue ahí.',

    'compare_drift_title' => 'Limitación conocida: el desplazamiento de líneas',
    'compare_drift_body' => 'Como la huella depende del número de línea, cambios no relacionados que agreguen o quiten líneas por encima de un hallazgo pueden desplazarlo y hacer que parezca nuevo o resuelto aunque el problema sea el mismo. Herramientas como SonarQube resuelven esto con huellas basadas en el contexto de código que rodea al hallazgo. Clarif todavía no lo resuelve: es una limitación conocida y consciente, no un error.',

];
