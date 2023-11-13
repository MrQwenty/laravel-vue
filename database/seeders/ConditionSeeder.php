<?php

namespace Database\Seeders;

use App\Models\Condition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        if(Schema::hasTable('conditions')){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('conditions')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Condition::create([
                'id' => 1,
                'value' => ['it' => "Come nuovo",'en' => 'Like New']
            ]);
            Condition::create([
                'id' => 2,
                'value' => ['it' => "Buone condizioni",'en' => 'Good Condition']
            ]);
            Condition::create([
                'id' => 3,
                'value' => ['it' => "Danneggiato",'en' => 'Damaged']
            ]);
        }
    }
}
