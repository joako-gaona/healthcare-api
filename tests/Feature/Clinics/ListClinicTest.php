<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Lightit\Clinics\App\Controllers\ListClinicController;
use function Pest\Laravel\getJson;

describe('clinics', function (): void {
    /** @see ListClinicController */
    it('can list clinics successfully', function (): void {
        ClinicFactory::new()
            ->createMany(5);

        getJson(url('/api/clinics'))
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');
    });
});
