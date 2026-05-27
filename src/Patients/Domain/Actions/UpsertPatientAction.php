<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\Actions;

use Lightit\Patients\Domain\DataTransferObjects\PatientDto;
use Lightit\Users\Domain\Models\User;

class UpsertPatientAction
{
    public function execute(PatientDto $patientDto, User|null $patient = null): User
    {
        $patient ??= new User();

        $patient->name = $patientDto->name;
        $patient->email = $patientDto->email;
        $patient->password = $patientDto->password;

        $patient->saveOrFail();

        return $patient;
    }
}
