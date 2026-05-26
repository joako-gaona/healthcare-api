<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\Actions;

use Illuminate\Support\Facades\DB;
use Lightit\Clinics\Domain\DataTransferObjects\ClinicDto;
use Lightit\Clinics\Domain\Models\Clinic;

class UpsertClinicAction
{
    public function execute(ClinicDto $clinicDto, Clinic|null $clinic = null): Clinic
    {
        return DB::transaction(function () use ($clinicDto, $clinic): Clinic {
            $clinic ??= new Clinic();

            $clinic->name = $clinicDto->name;
            $clinic->address = $clinicDto->address;

            $clinic->saveOrFail();

            if ($clinicDto->doctorIds !== null) {
                $clinic->doctors()->sync($clinicDto->doctorIds);
            }

            return $clinic;
        });
    }
}
