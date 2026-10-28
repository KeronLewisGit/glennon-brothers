<?php

namespace Database\Factories;

use App\Enums\FrameClientType;
use App\Enums\FrameProjectType;
use App\Enums\FrameStatus;
use App\Enums\PlanningStatus;
use App\Models\FrameEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FrameEnquiry> */
class FrameEnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => FrameStatus::New,
            'client_type' => FrameClientType::SelfBuilder,
            'project_type' => FrameProjectType::House,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '087 '.fake()->numerify('### ####'),
            'site_address' => 'Site at Ballymahon Road',
            'county' => 'Longford',
            'planning_status' => PlanningStatus::Granted,
            'start_month' => now()->addMonths(4)->format('Y-m'),
            'floor_area_m2' => 210,
        ];
    }
}
