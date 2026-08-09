<?php

// Versión del esquema de los datos de dominio exportables. Se sube solo
// cuando un cambio de estructura (columnas, tablas) haría incompatible un
// JSON exportado con una versión anterior. El importador rechaza cualquier
// archivo cuya version_esquema no coincida exactamente, para no intentar
// adivinar una migración de datos ambigua.
return [
    'version_esquema' => 1,
    'version_app' => '1.0.0',
];
