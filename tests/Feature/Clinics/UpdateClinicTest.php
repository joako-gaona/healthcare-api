<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Clinics\App\Controllers\UpdateClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Models\Clinic;
use Tests\RequestFactories\StoreClinicRequestFactory;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

describe('clinics', function (): void {
    /** @see UpdateClinicController */
    it('can update a clinic successfully', function (): void {
        $clinic = ClinicFactory::new()->createOne([
            'name' => 'Old clinic',
        ]);

        $data = StoreClinicRequestFactory::new()->create([
            'name' => 'Updated clinic',
        ]);

        $response = putJson(url("/api/clinics/$clinic->id"), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $response
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson =>
                $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        ClinicResource::make($clinic)->resolve()
                    )
                )
            );

        assertDatabaseHas('clinics', [
            'id' => $clinic->id,
            'name' => $data['name'],
            'address' => $data['address'],
        ]);
    });

    it('cannot update a clinic with invalid data', function (): void {
        $existingClinic = ClinicFactory::new()->createOne();

        $data = [
            'name' => '',
            'address' => '',
        ];

        $response = putJson(url("/api/clinics/$existingClinic->id"), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'address'], 'error.fields');
    });
});
