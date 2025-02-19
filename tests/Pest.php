<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\User;

uses(
    Tests\TestCase::class,
)
    ->in('Feature');

uses(
    Tests\TestCase::class,
)
    ->beforeEach(function () {
        DB::connection()->getSchemaBuilder()->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
        });

        Relation::morphMap(['test_user' => User::class]);

    })
    ->in('Unit');
