<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Database\Factories\ClinicFactory;
use Worksome\RequestFactories\RequestFactory;

class StoreDoctorRequestFactory extends RequestFactory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'clinic_ids' => ClinicFactory::new()
                ->count(2)
                ->create()
                ->pluck('id')
                ->all(),
        ];
    }
}
