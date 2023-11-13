<?php

namespace Database\Seeders;

use App\Helpers\CsvHelper;
use App\Models\City;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            // Recupero il disk che contiene i datasets di default del sistema
            $fs = Storage::disk('configs');

            City::truncate();

            // Ottengo path reale del file e lo apro con
            $content = CsvHelper::parseFile($fs->path('tables/cities.csv'));

            // Loop through each row and create a new model instance
            foreach ($content as $row) {
                $model = City::create($row);
            }
            // Do something with the result
        } catch (QueryException $e) {
            throw new \Exception("Error executing raw SQL: " . $e->getMessage());
        }


    }
}
