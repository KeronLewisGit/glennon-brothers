<?php

namespace Database\Factories;

use App\Enums\Country;
use App\Enums\CustomerType;
use App\Enums\QuoteStatus;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<QuoteRequest> */
class QuoteRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => QuoteStatus::New,
            'customer_type' => CustomerType::Merchant,
            'company' => fake()->company().' Building Supplies',
            'contact_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '090 '.fake()->numerify('### ####'),
            'country' => Country::Ireland,
            'delivery_address' => 'Unit 4, Business Park, Athlone',
            'postcode' => 'N37 X2Y4',
        ];
    }

    public function uk(): static
    {
        return $this->state(fn () => ['country' => Country::Scotland, 'delivery_address' => '12 Dock Road, Ayr', 'postcode' => 'KA8 8AA']);
    }
}
