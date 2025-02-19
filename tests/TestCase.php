<?php

declare(strict_types=1);

namespace Tests;

use Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use RefreshDatabaseWithTenant;

    private string $seeder = TestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Mail::fake();

        foreach (array_keys(config('filesystems.disks')) as $disk) {
            Storage::fake($disk);
        }
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        Tenant::all()->each->delete();
        parent::tearDown();
    }

}
