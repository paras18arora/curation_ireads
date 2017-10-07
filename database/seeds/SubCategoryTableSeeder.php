<?php

use Illuminate\Database\Seeder;

class SubCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for($i = 0; $i < 100; $i++) {
            App\Models\SubCategory::create([
            	'sub_category_name' => $faker->words($nb = 2, $asText = true) ,
            	'categories_id' => $faker->numberBetween($min = 1, $max = 10),
            	'view_count' => 0
            ]);
        }
    }
}
