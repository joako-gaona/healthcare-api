<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\DataTransferObjects;

readonly class ClinicDto
{
    /**
     * @param list<int>|null $doctorIds
     */
    public function __construct(
        public string $name,
        public string $address,
        public array|null $doctorIds = null,
    ) {
    }
}
