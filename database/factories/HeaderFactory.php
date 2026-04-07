<?php

namespace Database\Factories;

use App\Enums\EventRule;
use App\Enums\EventType;
use App\Models\Header;
use App\Models\Event;
use App\Models\Entry;
use App\Models\Asset;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Header>
 */
class HeaderFactory extends Factory
{
    protected $model = Header::class;

    public function definition(): array
    {
        $rule = $this->faker->randomElement(EventRule::cases());
        $type = $this->faker->randomElement(EventType::cases());

        $startDate = Carbon::now()->subMonths($this->faker->numberBetween(0, 12));

        $endDate = null;
        if ($this->faker->boolean(70)) {
            $endDate = $startDate->copy()->addMonths($this->faker->numberBetween(1, 12));
        }

        $defaultAmount = null;
        if ($type !== EventType::Transfer) {
            $defaultAmount = $this->faker->randomFloat(2, 10, 500);
        }

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->sentence(10),
            'type' => $type,
            'rule' => $rule,
            'default_amount' => $defaultAmount,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Generate everything: header, events, and entries
     */
    public function withEventEntries(?Asset $asset = null): static
    {
        return $this->afterCreating(function (Header $header) use ($asset) {
            $eventDates = $this->calculateEventDates($header);
            $targetAsset = $asset ?? Asset::factory()->create();

            foreach ($eventDates as $date) {
                $event = Event::factory()
                    ->forHeader($header)
                    ->withDate($date)
                    ->create();

                Entry::factory()
                    ->forEvent($event)
                    ->forAsset($targetAsset)
                    ->forHeader($header)
                    ->create();
            }
        });
    }

    /**
     * Calculate all occurrence dates based on header rules
     *
     * @return array<Date>
     */
    private function calculateEventDates(Header $header): array
    {
        $dates = [];
        $startDate = Carbon::parse($header->start_date)->firstOfMonth();
        $endDate = $header->end_date
            ? Carbon::parse($header->end_date)
            : Carbon::now();

        $endDate = $endDate->firstOfMonth();

        while ($startDate->lte($endDate)) {
            $dates[] = $startDate->copy();
            $startDate->addMonth();
        }

        return $dates;
    }

    /**
     * Set a specific recurrence rule.
     */
    public function withRule(EventRule $rule): static
    {
        return $this->state(fn(array $attributes) => [
            'rule' => $rule,
        ]);
    }

    /**
     * Set a specific event type.
     */
    public function withType(EventType $type): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => $type,
        ]);
    }
}
