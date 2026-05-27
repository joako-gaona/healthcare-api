<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Doctors\App\Controllers\DeleteDoctorController;
use Lightit\Doctors\Domain\Models\Doctor;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

describe('doctors', function (): void {
    /** @see DeleteDoctorController */
    it('deletes a doctor and returns a successful response', function (): void {
        $clinics = ClinicFactory::new()
            ->count(2)
            ->create();

        $existingDoctor = DoctorFactory::new()->createOne();

        /** @var list<int> $clinicIds */
        $clinicIds = $clinics->pluck('id')->all();

        $existingDoctor->clinics()->sync($clinicIds);

        $response = deleteJson("api/doctors/$existingDoctor->id");

        $response->assertNoContent();

        assertDatabaseMissing(Doctor::class, ['id' => $existingDoctor->id]);

        foreach ($clinicIds as $clinicId) {
            assertDatabaseMissing('clinic_doctor', [
                'doctor_id' => $existingDoctor->id,
                'clinic_id' => $clinicId,
            ]);
        }
    });

    it('returns a 404 response when doctor is not found', function (): void {
        $nonExistentDoctorId = 99999;

        deleteJson("api/doctors/{$nonExistentDoctorId}")->assertNotFound();
    });
});
