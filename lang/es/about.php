<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ¿Qué es SARIF?
    |--------------------------------------------------------------------------
    |
    | Contenido visible de la página explicativa. Es la versión en lenguaje
    | simple de docs/scope. La comparación de runs vive en la página de ayuda.
    |
    */

    'title' => '¿Qué es SARIF?',
    'intro_1' => 'SARIF son las siglas de Static Analysis Results Interchange Format. Es un estándar abierto en JSON, publicado por OASIS, que muchas herramientas de análisis estático usan para describir los problemas de calidad y seguridad que encuentran.',
    'intro_2' => 'Cada herramienta solía inventar su propio formato de salida. SARIF les da un lenguaje común: Clarif lee ese único formato, así no hace falta escribir un adaptador distinto por cada herramienta.',

    'tools_title' => '¿Qué herramientas soporta Clarif?',
    'tools_intro' => 'Clarif acepta cualquier documento SARIF 2.1.0 válido. Estas son las herramientas con las que está diseñado y probado:',
    'tools_codeql' => 'El motor de análisis semántico de código de GitHub. Reporta problemas de seguridad y calidad con niveles de severidad explícitos.',
    'tools_eslint' => 'El linter de JavaScript y TypeScript. Suele expresar la severidad mediante la configuración por defecto de cada regla, que Clarif resuelve automáticamente.',
    'tools_semgrep' => 'Una herramienta de análisis estático liviana y multilenguaje, usada para chequeos de seguridad y reglas personalizadas.',
    'tools_zap' => 'OWASP ZAP, el escáner de seguridad de aplicaciones web. Exporta SARIF y reporta los cuatro niveles de severidad.',

    'severity_title' => '¿Cómo se interpretan los niveles de severidad?',
    'severity_intro' => 'SARIF solo define cuatro niveles abstractos, que por sí solos dicen poco. Clarif los traduce a una escala más clara, manteniendo el nivel original disponible al pasar el cursor:',
    'severity_column_sarif' => 'Nivel SARIF',
    'severity_column_label' => 'Etiqueta de Clarif',
    'severity_column_meaning' => 'Significado',
    'severity_error_meaning' => 'La herramienta marcó un problema definitivo.',
    'severity_warning_meaning' => 'Un problema potencial que conviene revisar.',
    'severity_note_meaning' => 'Una observación informativa.',
    'severity_none_meaning' => 'La herramienta no asignó un nivel.',
    'severity_caveat' => 'La etiqueta se deriva únicamente del nivel SARIF. Algunas herramientas, como CodeQL, además incluyen un puntaje numérico de riesgo en rule.properties.security-severity; Clarif no lo interpreta en esta versión.',

    'scope_title' => 'Cómo interpreta Clarif un reporte',
    'scope_intro' => 'Algunas reglas de alcance deliberadas mantienen la ingesta predecible y rápida. Son limitaciones conocidas, no errores:',
    'scope_runs' => 'Solo se procesa el primer run (runs[0]) de cada archivo. Un archivo SARIF se trata como un único run.',
    'scope_codeflows' => 'Los flujos de datos (codeFlows) se conservan tal cual dentro de cada hallazgo, pero no se descomponen en registros separados.',
    'scope_severity' => 'La severidad se toma del hallazgo cuando está presente, luego del nivel por defecto de la regla, y finalmente cae a warning.',

    'diffing_title' => '¿Y cómo comparo dos runs?',
    'diffing_intro' => 'La comparación de runs tiene su propia guía, con el detalle de la huella de cada hallazgo y la limitación conocida del desplazamiento de líneas.',

];
