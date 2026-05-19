<?php

declare(strict_types=1);

namespace Tests\Feature\Authentication;

use Database\Factories\UserFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Authentication\App\Controllers\LoginController;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('authentication', function (): void {
    /** @see LoginController */
    it('can log in and retrieve the authenticated user', function (): void {
        $user = UserFactory::new()->createOne();

        $loginResponse = postJson(url('/api/login'), [
            'email' => $user->email,
            'password' => '>e$pV4chNFcJoAB%X#{',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json
                    ->has(
                        'data',
                        fn (AssertableJson $json): AssertableJson => $json
                            ->whereType('access_token', 'string')
                            ->where('token_type', 'Bearer')
                            ->where('expires_in', 3600)
                    )
            );

        /** @var string $accessToken */
        $accessToken = $loginResponse->json('data.access_token');

        getJson(url('/api/me'), [
            'Authorization' => "Bearer {$accessToken}",
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    });

    it('rejects invalid credentials', function (): void {
        $user = UserFactory::new()->createOne();

        postJson(url('/api/login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    });
});
