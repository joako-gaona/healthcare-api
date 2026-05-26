<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class StorePatientRequestFactory extends RequestFactory
{
    /**
     * @return array<string, string>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => '>e$pV4chNFcJoAB%X#{',
            'password_confirmation' => '>e$pV4chNFcJoAB%X#{',
        ];
    }
}
