<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Lightit\Patients\App\Requests\UpsertPatientRequest;
use Worksome\RequestFactories\RequestFactory;

class StorePatientRequestFactory extends RequestFactory
{
    public const string VALID_PASSWORD = '>e$pV4chNFcJoAB%X#{';

    /**
     * @return array<string, string>
     */
    public function definition(): array
    {
        return [
            UpsertPatientRequest::NAME => fake()->name(),
            UpsertPatientRequest::EMAIL => fake()->email(),
            UpsertPatientRequest::PASSWORD => self::VALID_PASSWORD,
            UpsertPatientRequest::PASSWORD . '_confirmation' => self::VALID_PASSWORD,
        ];
    }
}
