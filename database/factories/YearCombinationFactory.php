<?php

namespace Database\Factories;

use App\Models\YearCombination;
use Illuminate\Database\Eloquent\Factories\Factory;

class YearCombinationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = YearCombination::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'year' => $this->faker->year($max = 'now'),
            'country' => $this->faker->word(),
            'total' => $this->faker->numberBetween($min = 5, $max = 30),
            'streak' =>  $this->faker->numberBetween($min = 1, $max = 6)
        ];
    }
}
