<?php

// Versión del esquema de los datos de dominio exportables. Se sube solo
// cuando un cambio de estructura (columnas, tablas) haría incompatible un
// JSON exportado con una versión anterior. El importador rechaza cualquier
// archivo cuya version_esquema no coincida exactamente, para no intentar
// adivinar una migración de datos ambigua.
return [
    // v2: se añadió la tabla 'riegos_manta' al respaldo. Un JSON v1 no la
    // contiene, así que el importador lo rechaza (evita restaurar una copia
    // que borraría los riegos a manta en cascada sin volver a crearlos).
    'version_esquema' => 2,
    'version_app' => '1.0.0',
];
