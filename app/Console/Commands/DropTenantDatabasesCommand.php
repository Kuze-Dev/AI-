<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Tenant\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class DropTenantDatabasesCommand extends Command
{
    use ConfirmableTrait;

    protected $name = 'tenants:drop-db';

    protected $description = 'Drop unsued tenant databases.';

    public function handle(): int
    {
        if ( ! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $tenants = Tenant::all();

        $databases = collect(DB::getDoctrineSchemaManager()->listDatabases())
            ->filter(fn (string $database) => Str::startsWith($database, config('tenancy.database.prefix')))
            ->reject(fn (string $database) => in_array($database, $tenants->pluck('tenancy_db_name')->toArray()));

        if ($databases->isEmpty()) {
            (new Info($this->output))->render('Nothing to drop.');

            return self::SUCCESS;
        }

        (new Info($this->output))->render('Dropping unused tenant databases.');

        $databases->each(
            fn (string $database) => (new Task($this->output))
                ->render($database, function () use ($database) {
                    try {
                        DB::getDoctrineSchemaManager()->dropDatabase($database);

                        return true;
                    } catch (Exception $e) {
                        report($e);

                        return false;
                    }
                })
        );

        return self::SUCCESS;
    }
}
