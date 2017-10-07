<?php

use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for($i = 0; $i < 6; $i++) {
            App\Models\Category::create([
                'category_name' => $faker->words($nb = 2, $asText = true) ,
                'view_count' => 0
            ]);
        }
    }
}
