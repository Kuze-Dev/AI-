<?php

declare(strict_types=1);

use App\Features\Customer\CustomerBase;
use Domain\Customer\Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertCount;

uses()->group('customer');

beforeEach(function () {
    $tenant = testInTenantContext();
    $tenant->features()->activate(CustomerBase::class);
});

it('can generate token', function () {
    $customer = CustomerFactory::new()
        ->active()
        ->createOne([
            'email' => 'me@me.me',
            'password' => 'secret-pass',
        ]);

    assertCount(0, $customer->tokens);

    postJson('api/login', [
        'email' => 'me@me.me',
        'password' => 'secret-pass',
    ])
        ->assertValid()
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('token')
                ->whereType('token', 'string');
        });

    assertCount(1, $customer->refresh()->tokens);
});

it('can not generate token when invalid credentials', function () {
    $customer = CustomerFactory::new()
        ->active()
        ->createOne([
            'email' => 'me@me.me',
            'password' => 'secret-pass',
        ]);

    assertCount(0, $customer->tokens);

    postJson('api/login', [
        'email' => 'me@me.me',
        'password' => 'secret-pass-invalid',
    ])
        ->assertValid()
        ->assertUnauthorized();

    assertCount(0, $customer->refresh()->tokens);
});

it('can access private route with valid token', function () {
    $customer = CustomerFactory::new()
        ->active()
        ->createOne();

    $token = $customer
        ->createToken(
            name: 'testing-auth',
        )
        ->plainTextToken;

    Route::get('api/test-private-route', fn () => 'access granted!')
        ->middleware([
            'api',
            'auth:sanctum',
        ]);

    getJson('api/test-private-route', [
        'Authorization' => 'Bearer '.$token,
    ])
        ->assertOk()
        ->assertSee('access granted!');
});

it('can not access private route with invalid token', function (?string $token) {
    Route::get('api/test-private-route', fn () => '')
        ->middleware([
            'api',
            'auth:sanctum',
        ]);

    getJson('api/test-private-route', [
        'Authorization' => $token,
    ])
        ->assertUnauthorized();
})
    ->with([
        null,
        'Bearer invalid',
        '',
    ]);

it('can not access private route without header authorization', function () {
    Route::get('api/test-private-route', fn () => '')
        ->middleware([
            'api',
            'auth:sanctum',
        ]);

    getJson('api/test-private-route')
        ->assertUnauthorized();
});
