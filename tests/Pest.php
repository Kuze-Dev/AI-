<?php

declare(strict_types=1);

use Database\Seeders\Auth\PermissionSeeder;
use Database\Seeders\Auth\RoleSeeder;
use Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;
use Tests\Fixtures\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\seed;

uses(
    Illuminate\Foundation\Testing\TestCase::class,
    Tests\CreatesApplication::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class
)
    ->beforeEach(function () {
        Http::preventStrayRequests();
        Mail::fake();

        foreach (array_keys(config('filesystems.disks')) as $disk) {
            Storage::fake($disk);
        }

        Event::listen(MigrationsEnded::class, function () {
            if ( ! tenancy()->initialized) {
                seed([
                    PermissionSeeder::class,
                    RoleSeeder::class,
                ]);
            }
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        config()->set('tenancy.database.prefix', 'test_');
    })
    ->afterEach(function () {
        tenancy()->end();
        Tenant::all()->each->delete();
    })
    ->in('Feature');

uses(
    Illuminate\Foundation\Testing\TestCase::class,
    Tests\CreatesApplication::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
)
    ->beforeEach(function () {
        Http::preventStrayRequests();
        Mail::fake();

        foreach (array_keys(config('filesystems.disks')) as $disk) {
            Storage::fake($disk);
        }

        DB::connection()->getSchemaBuilder()->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
        });

        Relation::morphMap(['test_user' => User::class]);

        config()->set('tenancy.database.prefix', 'test_');
    })
    ->afterEach(function () {
        tenancy()->end();
        Tenant::all()->each->delete();
    })
    ->in('Unit');
