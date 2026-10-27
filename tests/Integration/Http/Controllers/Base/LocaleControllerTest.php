<?php

namespace Pterodactyl\Tests\Integration\Http\Controllers\Base;

use Pterodactyl\Tests\Integration\Http\HttpTestCase;

class LocaleControllerTest extends HttpTestCase
{
    public function testMultipleLocalesAndNamespacesAreReturned(): void
    {
        $this->getJson('/locales/locale.json?locale=en+ru&namespace=common+account')
            ->assertOk()
            ->assertJsonPath('en.common.cancel', 'Cancel')
            ->assertJsonPath('ru.common.cancel', 'Отмена')
            ->assertJsonPath('ru.account.language.title', 'Язык');
    }

    public function testPlaceholdersAreConvertedForTheFrontend(): void
    {
        $this->getJson('/locales/locale.json?locale=en&namespace=activity')
            ->assertOk()
            ->assertJsonPath('en.activity.user.account.email-changed', 'Changed email from {{old}} to {{new}}');
    }

    public function testInvalidNamespacesAreRejected(): void
    {
        $this->getJson('/locales/locale.json?locale=en&namespace=../../config')->assertUnprocessable();
        $this->getJson('/locales/locale.json?locale=english&namespace=common')->assertUnprocessable();
    }
}
