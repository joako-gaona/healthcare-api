<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Lightit\Clinics\Domain\DataTransferObjects\ClinicDto;
use Lightit\Doctors\Domain\Models\Doctor;
use Illuminate\Validation\Rule;


class UpsertClinicRequest extends FormRequest
{
    public const string NAME = 'name';

    public const string ADDRESS = 'address';

    public const string DOCTOR_IDS = 'doctor_ids';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            self::NAME => ['required', 'string', 'max:120'],
            self::ADDRESS => ['required', 'string', 'max:255'],
            self::DOCTOR_IDS => ['sometimes', 'array', Rule::exists(Doctor::class, 'id')],
            self::DOCTOR_IDS . '.*' => [Rule::numeric()->integer(), 'distinct'],
        ];
    }

    public function toDto(): ClinicDto
    {
        /** @var list<int>|null $doctorIds */
        $doctorIds = $this->has(self::DOCTOR_IDS)
            ? $this->array(self::DOCTOR_IDS)
            : null;

        return new ClinicDto(
            name: $this->string(self::NAME)->toString(),
            address: $this->string(self::ADDRESS)->toString(),
            doctorIds: $doctorIds,
        );
    }
}
