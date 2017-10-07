<?php

use Illuminate\Database\Seeder;

class CourseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for($i = 0; $i < 1000; $i++) {
            App\Models\Course::create([
                'course_name' => $faker->sentence($nbWords = 6, $variableNbWords = true),
                'course_type' => $faker->numberBetween($min = 1, $max = 4),
                'price' => $faker->numberBetween($min = 1, $max = 2),
                'level' => $faker->numberBetween($min = 1, $max = 3),
                'author' => $faker->name($gender = null|'male'|'female'),
                'description' => $faker->paragraph($nbSentences = 5, $variableNbSentences = true),
                'sub_cat' => $faker->numberBetween($min = 1, $max = 100),
            ]);
        }
    }
}
