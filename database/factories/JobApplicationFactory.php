<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Enums\Experience;
use App\Enums\JobCategory;
use App\Enums\Plant;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobApplication> */
class JobApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category' => JobCategory::Operative,
            'plant' => Plant::Longford,
            'status' => ApplicationStatus::New,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => '086 '.fake()->numerify('### ####'),
            'right_to_work' => true,
            'email' => fake()->safeEmail(),
            'experience' => Experience::TwoToFive,
            'consented_at' => now(),
        ];
    }
}
