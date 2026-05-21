<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Illuminate\Support\Str;
use Lightit\Doctors\App\Controllers\StoreDoctorController;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Models\Doctor;
use Tests\RequestFactories\StoreDoctorRequestFactory;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

function getLongDoctorValue(int $length): string
{
    return Str::repeat(string: 'a', times: $length);
}

dataset(name: 'doctor-validation-rules', dataset: [
    'name is required' => ['name', '', 'name'],
    'name must be a string' => ['name', ['array'], 'name'],
    'name must not be too long' => ['name', getLongDoctorValue(121), 'name'],

    'clinic_ids is required' => ['clinic_ids', [], 'clinic_ids'],
    'clinic_ids must be an array' => ['clinic_ids', 'not-array', 'clinic_ids'],
    'clinic_ids item must be an integer' => ['clinic_ids', ['not-id'], 'clinic_ids.0'],
    'clinic_ids item must exist' => ['clinic_ids', [99999], 'clinic_ids.0'],
]);

describe('doctors', function (): void {
    /** @see StoreDoctorController */
    it(description: 'can create a doctor successfully', closure: function (): void {
        $data = StoreDoctorRequestFactory::new()->create();

        $response = postJson(url('/api/doctors'), $data);

        $doctor = Doctor::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $doctor->load('clinics');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = DoctorResource::make($doctor)
            ->response()
            ->getData(true);

        $response
            ->assertCreated()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(Doctor::class, [
            'name' => $data['name'],
        ]);

        foreach ($data['clinic_ids'] as $clinicId) {
            assertDatabaseHas('clinic_doctor', [
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinicId,
            ]);
        }
    });

    it('cannot create a doctor with invalid data', function (string $field, string|array $value, string $errorField): void {
        $data = StoreDoctorRequestFactory::new()->create();
        $doctorsCount = Doctor::query()->count();

        $response = postJson(url('/api/doctors'), [...$data, $field => $value]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([$errorField], 'error.fields');

        expect(Doctor::query()->count())->toBe($doctorsCount);
    })->with('doctor-validation-rules');
});
