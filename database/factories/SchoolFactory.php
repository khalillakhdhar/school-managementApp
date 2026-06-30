<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $name = 'École ' . fake()->unique()->city();

        return [
            'name'          => $name,
            'slug'          => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'primary_color' => fake()->randomElement(['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']),
            'email'         => fake()->companyEmail(),
            'phone'         => '+216 ' . fake()->numerify('## ### ###'),
            'city'          => fake()->city(),
            'country'       => 'Tunisie',
            'status'        => School::STATUS_ACTIVE,
            'plan'          => 'standard',
        ];
    }

    public function trial(): static
    {
        return $this->state(fn () => [
            'status'        => School::STATUS_TRIAL,
            'plan'          => 'trial',
            'trial_ends_at' => now()->addDays(30),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => School::STATUS_SUSPENDED]);
    }
}
