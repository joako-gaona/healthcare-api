<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\Rules\Password;
use Lightit\Patients\Domain\DataTransferObjects\PatientDto;
use Lightit\Users\Domain\Models\User;

class UpsertPatientRequest extends FormRequest
{
    public const string NAME = 'name';

    public const string EMAIL = 'email';

    public const string PASSWORD = 'password';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $uniqueEmailRule = Rule::unique(User::class, 'email');
        $patient = $this->route('patient');

        if ($patient instanceof User) {
            $uniqueEmailRule->ignore($patient->id);
        }

        return [
            self::NAME => ['required', 'string', 'min:4', 'max:80'],
            self::EMAIL => [
                'required',
                'max:100',
                Email::default(),
                $uniqueEmailRule,
            ],
            self::PASSWORD => [
                'required',
                Password::default(),
                'confirmed',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input(self::EMAIL);

        if (is_string($email)) {
            $this->merge([
                self::EMAIL => strtolower($email),
            ]);
        }
    }

    public function toDto(): PatientDto
    {
        return new PatientDto(
            name: $this->string(self::NAME)->toString(),
            email: $this->string(self::EMAIL)->toString(),
            password: $this->string(self::PASSWORD)->toString(),
        );
    }
}
