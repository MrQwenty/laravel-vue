<?php

namespace App\Console\Commands\Setup;

use App\Helpers\CsvHelper;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Country as CountryModel;

class Country extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:countries {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup "countries" table using the default dataset provided inside the application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Recupero il disk che contiene i datasets di default del sistema
        $fs = Storage::disk('configs');

        $isForce = !!$this->option('force');

        if ($isForce) {
            CountryModel::truncate();
        }

        // Ottengo path reale del file e lo apro con
        $content = CsvHelper::parseFile($fs->path('tables/countries.csv'));

        foreach ($content as $c) {

            if ($c['vat_perc'] == "NULL") {
                $c['vat_perc'] = null;
            }

            CountryModel::firstOrCreate($c);
        }

        $this->output->info('Tabella "' . CountryModel::getTableName() . '" importata correttamente');
        return 0;
    }
}
