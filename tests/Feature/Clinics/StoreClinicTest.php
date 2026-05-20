<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Clinics\App\Controllers\StoreClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Models\Clinic;
use Tests\RequestFactories\StoreClinicRequestFactory;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

function getLongClinicValue(int $length): string
{
    return Str::repeat(string: 'a', times: $length);
}

dataset(name: 'clinic-validation-rules', dataset: [
    'name is required' => ['name', ''],
    'name must be a string' => ['name', ['array']],
    'name must not be too long' => ['name', getLongClinicValue(121)],

    'address is required' => ['address', ''],
    'address must be a string' => ['address', ['array']],
    'address must not be too long' => ['address', getLongClinicValue(256)],
]);

describe('clinics', function (): void {
    /** @see StoreClinicController */
    it(description: 'can create a clinic successfully', closure: function (): void {
        $data = StoreClinicRequestFactory::new()->create();

        $response = postJson(url('/api/clinics'), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $response
            ->assertCreated()
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
            'name' => $data['name'],
            'address' => $data['address'],
        ]);
    });

    it('cannot create a clinic with invalid data', function (string $field, string|array $value): void {
        $data = StoreClinicRequestFactory::new()->create();
        $clinicsCount = Clinic::query()->count();

        $response = postJson(url('/api/clinics'), [...$data, $field => $value]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');

        expect(Clinic::query()->count())->toBe($clinicsCount);
    })->with('clinic-validation-rules');
});
