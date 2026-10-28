<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\JobCategory;
use App\Enums\JobStatus;
use App\Enums\Plant;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<JobPosting> */
class JobPostingFactory extends Factory
{
    public function definition(): array
    {
        $title = 'General operative '.fake()->unique()->numberBetween(1, 9999);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'category' => JobCategory::Operative,
            'employment_type' => EmploymentType::Shifts,
            'status' => JobStatus::Open,
            'plant' => Plant::Longford,
            'openings' => 1,
            'summary' => 'Work on the sawmill infeed and grading line.',
            'duties' => "Feed the line\nKeep the area clean and safe",
            'requirements' => 'Safe Pass or equivalent',
        ];
    }
}
