<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación
    |--------------------------------------------------------------------------
    |
    | Solo se definen las reglas que usa la subida de SARIF, para que los
    | errores de validación sigan el idioma elegido en la UI en vez de los
    | textos por defecto del framework. Agregar entradas al sumar reglas.
    |
    */

    'required' => 'El campo :attribute es obligatorio.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'extensions' => 'El campo :attribute debe tener una de las siguientes extensiones: :values.',
    'uploaded' => 'No se pudo subir el archivo :attribute.',

    'max' => [
        'file' => 'El campo :attribute no debe pesar más de :max kilobytes.',
    ],

    'attributes' => [
        'report' => 'archivo SARIF',
    ],

];
