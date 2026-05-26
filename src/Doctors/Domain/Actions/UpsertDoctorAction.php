<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\Actions;

use Illuminate\Support\Facades\DB;
use Lightit\Doctors\Domain\DataTransferObjects\DoctorDto;
use Lightit\Doctors\Domain\Models\Doctor;

class UpsertDoctorAction
{
    public function execute(DoctorDto $doctorDto, Doctor|null $doctor = null): Doctor
    {
        return DB::transaction(function () use ($doctorDto, $doctor): Doctor {
            $doctor ??= new Doctor();

            $doctor->name = $doctorDto->name;

            $doctor->saveOrFail();

            $doctor->clinics()->sync($doctorDto->clinicIds);

            return $doctor->load('clinics');
        });
    }
}
