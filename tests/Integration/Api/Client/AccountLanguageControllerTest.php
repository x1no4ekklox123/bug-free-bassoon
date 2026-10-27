<?php

namespace Pterodactyl\Tests\Integration\Api\Client;

use Pterodactyl\Models\User;
use Illuminate\Http\Response;

class AccountLanguageControllerTest extends ClientApiIntegrationTestCase
{
    public function testLanguageCanBeUpdated(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['language' => 'en']);

        $this->actingAs($user)->putJson('/api/client/account/language', ['language' => 'ru'])
            ->assertNoContent();

        $this->assertSame('ru', $user->refresh()->language);
        $this->assertActivityFor('user:account.language-changed', $user, $user);
    }

    public function testUnknownLanguageIsRejected(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['language' => 'en']);

        $this->actingAs($user)->putJson('/api/client/account/language', ['language' => 'xx'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.meta.source_field', 'language');

        $this->assertSame('en', $user->refresh()->language);
    }

    public function testApiResponsesUseTheUsersLanguage(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['language' => 'ru']);

        $this->actingAs($user)->putJson('/api/client/account/language', ['language' => ''])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.detail', trans('validation.required', ['attribute' => 'language'], 'ru'));
    }
}
