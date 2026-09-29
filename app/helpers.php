<?php

if (! function_exists('yearSql')) {
    /**
     * Expresión SQL para extraer el año de una columna de fecha.
     * MySQL usa YEAR(), SQLite usa strftime('%Y', ...).
     */
    function yearSql(string $columna): string
    {
        if (\DB::getDriverName() === 'sqlite') {
            return "CAST(strftime('%Y', {$columna}) AS INTEGER)";
        }

        return "YEAR({$columna})";
    }
}
