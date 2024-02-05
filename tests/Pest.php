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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Storage;
use Spatie\Once\Cache;
use Spatie\Permission\PermissionRegistrar;
use Tests\Fixtures\User;

use function Pest\Laravel\seed;

uses(
    Illuminate\Foundation\Testing\TestCase::class,
    Tests\CreatesApplication::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
)
    ->beforeEach(function () {
        Cache::getInstance()->disable();
        Http::preventStrayRequests();
        Mail::fake();

        foreach (array_keys(config('filesystems.disks')) as $disk) {
            Storage::fake($disk);
        }

        Event::listen(MigrationsEnded::class, function () {
            if (! tenancy()->initialized) {
                seed([
                    PermissionSeeder::class,
                    RoleSeeder::class,
                ]);
            }
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        config([
            'tenancy.database.template_tenant_connection' => 'sqlite',
            'tenancy.database.prefix' => ($token = ParallelTesting::token())
                ? "test_{$token}_"
                : 'test_',
        ]);
    })
    ->afterEach(function () {
        //        if (tenancy()->initialized) {
        tenancy()->end();
        Tenant::all()->each->delete();
        //        }
    })
    ->in('Feature');

uses(
    Illuminate\Foundation\Testing\TestCase::class,
    Tests\CreatesApplication::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
)
    ->beforeEach(function () {
        Cache::getInstance()->disable();
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

        config([
            'tenancy.database.template_tenant_connection' => 'sqlite',
            'tenancy.database.prefix' => ($token = ParallelTesting::token())
                ? "test_{$token}_"
                : 'test_',
        ]);
    })
    ->afterEach(function () {
        //        if (tenancy()->initialized) {
        tenancy()->end();
        Tenant::all()->each->delete();
        //        }
    })
    ->in('Unit');
