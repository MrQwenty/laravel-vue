<?php

namespace Database\Seeders;

use App\Models\AttributeType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttributeTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Schema::hasTable('attribute_types')){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('attribute_types')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            AttributeType::create([
                'id' => 1,
                'name' => ['it' => "Campo di testo",'en' => 'Textfield']
            ]);
            AttributeType::create([
                'id' => 2,
                'name' => ['it' => "Menù a tendina",'en' => 'Select']
            ]);
        }
    }
}
