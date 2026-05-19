<?php

declare(strict_types=1);

namespace Lightit\Authentication\Domain\Actions;

use Carbon\Constants\UnitValue;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Lightit\Authentication\Domain\DataTransferObjects\CredentialsDto;
use Lightit\Authentication\Domain\DataTransferObjects\LoginDto;
use Lightit\Shared\App\Exceptions\Http\UnauthenticatedException;
use PHPOpenSourceSaver\JWTAuth\Factory as JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class LoginAction
{
    public function __construct(
        private AuthFactory $factory,
        private JWTAuth $jwtAuth,
    ) {
    }

    /**
     * @throws UnauthenticatedException
     */
    public function execute(CredentialsDto $credentials): LoginDto
    {
        /** @var JWTGuard $guard */
        $guard = $this->factory->guard('api');

        if (! $token = $guard->attempt($credentials->toArray())) {
            throw new UnauthenticatedException();
        }

        /** @var string $token */
        return new LoginDto(
            accessToken: $token,
            tokenType: 'Bearer',
            expiresIn: $this->jwtAuth->getTTL() * UnitValue::SECONDS_PER_MINUTE,
        );
    }
}
