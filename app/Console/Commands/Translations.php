<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Translations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if (!file_exists(resource_path() . '/lang')) {
            $res = symlink(base_path() . '/lang', resource_path() . '/lang');
        }

        $this->call('VueTranslation:generate');

        return Command::SUCCESS;
    }
}
