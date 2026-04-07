<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Asset;
use App\Models\Entry;
use App\Models\Event;
use App\Models\Header;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entry>
 */
class EntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    public function forEvent(Event $event): static
    {
        return $this->state(fn(array $attributes) => [
            'event_id' => $event->id,
        ]);
    }

    public function forAsset(Asset $asset): static
    {
        return $this->state(fn(array $attributes) => [
            'asset_id' => $asset->id
        ]);
    }

    public function forHeader(Header $header): static
    {
        return $this->state(fn(array $attributes) => [
            'amount' => $this->getAmountForEvent($header),
        ]);
    }

    private function getAmountForEvent(Header $header): float
    {
        // For transfers, amount is 0 (will be handled differently in real logic)
        if ($header->type === EventType::Transfer) {
            return 0;
        }

        if ($header->default_amount) {
            return $header->default_amount;
        }

        return $this->faker->randomFloat(2, 10, 500);
    }
}
