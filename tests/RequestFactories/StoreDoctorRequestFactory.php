<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Database\Factories\ClinicFactory;
use Worksome\RequestFactories\RequestFactory;

class StoreDoctorRequestFactory extends RequestFactory
{
    /**
     * @return array{name: string, clinic_ids: list<int>}
     */
    public function definition(): array
    {
        /** @var list<int> $clinicIds */
        $clinicIds = ClinicFactory::new()
            ->count(2)
            ->create()
            ->pluck('id')
            ->all();

        return [
            'name' => fake()->name(),
            'clinic_ids' => $clinicIds,
        ];
    }
}
