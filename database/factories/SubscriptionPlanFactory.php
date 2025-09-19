<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->word() . ' Plan',
            'price'       => $this->faker->randomFloat(2, 5, 200),
            'description' => $this->faker->optional()->sentence(),
            'duration'    => $this->faker->randomElement([30, 60, 90]),
            'interval'    => $this->faker->randomElement(['day', 'month', 'year']),
            'is_active'   => $this->faker->boolean(90),
            'created_by'  => User::inRandomOrder()->value('id') ?? null,
        ];
    }
}
