<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\DoctorFactory;
use Illuminate\Support\Str;
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
    'name is required' => ['name', '', 'name'],
    'name must be a string' => ['name', ['array'], 'name'],
    'name must not be too long' => ['name', getLongClinicValue(121), 'name'],

    'address is required' => ['address', '', 'address'],
    'address must be a string' => ['address', ['array'], 'address'],
    'address must not be too long' => ['address', getLongClinicValue(256), 'address'],

    'doctor_ids must be an array' => ['doctor_ids', 'not-array', 'doctor_ids'],
    'doctor_ids item must be an integer' => ['doctor_ids', [null], 'doctor_ids.0'],
    'doctor_ids item must exist' => ['doctor_ids', [99999], 'doctor_ids'],
]);

describe('clinics', function (): void {
    /** @see StoreClinicController */
    it(description: 'can create a clinic successfully', closure: function (): void {
        $data = StoreClinicRequestFactory::new()->create();

        $response = postJson(url('/api/clinics'), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $clinic->loadCount('doctors');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = ClinicResource::make($clinic)
            ->response()
            ->getData(true);

        $response
            ->assertCreated()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(Clinic::class, [
            'name' => $data['name'],
            'address' => $data['address'],
        ]);
    });

    it(description: 'can create a clinic with assigned doctors successfully', closure: function (): void {
        $doctors = DoctorFactory::new()
            ->count(2)
            ->create();

        /** @var list<int> $doctorIds */
        $doctorIds = $doctors->pluck('id')->all();

        $data = StoreClinicRequestFactory::new()->create([
            'doctor_ids' => $doctorIds,
        ]);

        $response = postJson(url('/api/clinics'), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $clinic->loadCount('doctors');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = ClinicResource::make($clinic)
            ->response()
            ->getData(true);

        $response
            ->assertCreated()
            ->assertJsonPath('data', $resourceResponse['data'])
            ->assertJsonPath('data.doctors_count', count($doctorIds));

        foreach ($doctorIds as $doctorId) {
            assertDatabaseHas('clinic_doctor', [
                'doctor_id' => $doctorId,
                'clinic_id' => $clinic->id,
            ]);
        }
    });

    it(
        'cannot create a clinic with invalid data',
        function (string $field, string|array $value, string $errorField): void {
            $data = StoreClinicRequestFactory::new()->create();
            $clinicsCount = Clinic::query()->count();

            $response = postJson(url('/api/clinics'), [...$data, $field => $value]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors([$errorField], 'error.fields');

            expect(Clinic::query()->count())->toBe($clinicsCount);
        }
    )->with('clinic-validation-rules');

    it('cannot create a clinic with duplicate doctor ids', function (): void {
        $doctor = DoctorFactory::new()->createOne();

        $data = StoreClinicRequestFactory::new()->create([
            'doctor_ids' => [$doctor->id, $doctor->id],
        ]);

        $clinicsCount = Clinic::query()->count();

        $response = postJson(url('/api/clinics'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['doctor_ids.0', 'doctor_ids.1'], 'error.fields');

        expect(Clinic::query()->count())->toBe($clinicsCount);
    });
});
