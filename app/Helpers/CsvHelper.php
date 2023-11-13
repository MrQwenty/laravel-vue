<?php

namespace App\Helpers;

use App\Http\Controllers\Controller;

class CsvHelper extends Controller
{
    /**
     * Funzione che prende in ingresso un path ad un file e ritorna un array del contenuto del file .csv
     *
     * @param string $csv Percorso del file .csv
     * @param string $delimeter delimitatore dei record (default ',')
     * @return array | null Contenuto del file .csv in un array
     *
     */
    public static function parseFile($csv, $delimeter = ',')
    {
        $header = null;
        $data = array();
        if (($handle = fopen($csv, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimeter)) !== false) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}
