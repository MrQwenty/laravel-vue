<?php

namespace Database\Seeders;

use App\Models\Side;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if(Schema::hasTable('sides')){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('sides')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Side::create([
                'value' => "Sinistra"
            ]);
            Side::create([
                'value' => "Destra"
            ]);
            Side::create([
                'value' => "Neutro"
            ]);
        }
    }
}
