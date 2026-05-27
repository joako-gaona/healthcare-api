<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\DataTransferObjects;

use SensitiveParameter;

readonly class PatientDto
{
    public function __construct(
        public string $name,
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {
    }
}
