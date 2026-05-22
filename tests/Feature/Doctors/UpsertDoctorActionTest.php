<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Illuminate\Database\QueryException;
use Lightit\Doctors\Domain\Actions\UpsertDoctorAction;
use Lightit\Doctors\Domain\DataTransferObjects\DoctorDto;
use Lightit\Doctors\Domain\Models\Doctor;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

const NON_EXISTENT_CLINIC_ID = 99999;

describe('upsert doctor action', function (): void {
    it('rolls back the doctor creation when syncing clinics fails', function (): void {
        $action = new UpsertDoctorAction();

        expect(fn () => $action->execute(new DoctorDto(
            name: 'Doctor without valid clinics',
            clinicIds: [NON_EXISTENT_CLINIC_ID],
        )))->toThrow(QueryException::class);

        assertDatabaseMissing(Doctor::class, [
            'name' => 'Doctor without valid clinics',
        ]);
    });

    it('rolls back the doctor update when syncing clinics fails', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne([
            'name' => 'Original doctor',
        ]);

        $doctor->clinics()->sync([$clinic->id]);

        $action = new UpsertDoctorAction();

        expect(fn () => $action->execute(new DoctorDto(
            name: 'Updated doctor',
            clinicIds: [NON_EXISTENT_CLINIC_ID],
        ), $doctor))->toThrow(QueryException::class);

        assertDatabaseHas(Doctor::class, [
            'id' => $doctor->id,
            'name' => 'Original doctor',
        ]);

        assertDatabaseMissing(Doctor::class, [
            'id' => $doctor->id,
            'name' => 'Updated doctor',
        ]);

        assertDatabaseHas('clinic_doctor', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
        ]);
    });
});
