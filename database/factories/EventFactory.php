<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Header;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'note' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function forHeader(Header $header): static
    {
        return $this->state(fn (array $attributes) => [
            'header_id' => $header->id,
        ]);
    }

    public function withDate(string $date):static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
            'consolidated' => Carbon::parse($date)
                ->lessThanOrEqualTo(Carbon::today()->firstOfMonth()),
        ]);
    }
}
