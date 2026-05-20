<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\Actions;

use Lightit\Doctors\Domain\DataTransferObjects\DoctorDto;
use Lightit\Doctors\Domain\Models\Doctor;

class UpsertDoctorAction
{
    public function execute(DoctorDto $doctorDto, Doctor|null $doctor = null): Doctor
    {
        $doctor ??= new Doctor();

        $doctor->name = $doctorDto->name;

        $doctor->saveOrFail();

        $doctor->clinics()->sync($doctorDto->clinicIds);

        return $doctor->load('clinics');
    }
}
