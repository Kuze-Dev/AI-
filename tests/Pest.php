<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\User;

uses(
    Illuminate\Foundation\Testing\TestCase::class,
    Tests\CreatesApplication::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class
)->in('Feature', 'Unit');

uses()->beforeEach(function () {
    DB::connection()->getSchemaBuilder()->create('test_users', function (Blueprint $table) {
        $table->increments('id');
        $table->string('email');
        $table->timestamp('email_verified_at')->nullable();
    });

    Relation::morphMap(['test_user' => User::class]);
})->in('Unit/Domain/Auth');
