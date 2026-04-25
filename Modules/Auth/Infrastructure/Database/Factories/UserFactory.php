<?php

namespace Modules\Auth\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Infrastructure\Models\UserModel;

class UserFactory extends Factory
{
    protected $model = UserModel::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => bcrypt('password'),
            'is_active'         => true,
            'email_verified_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
