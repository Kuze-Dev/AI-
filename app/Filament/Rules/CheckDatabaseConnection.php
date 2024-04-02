<?php

declare(strict_types=1);

namespace App\Filament\Rules;

use Closure;
use Domain\Tenant\Models\Tenant;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Connectors\ConnectionFactory;
use PDOException;

class CheckDatabaseConnection implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function __construct(
        readonly protected string $connectionTemplate,
    ) {
    }

    #[\Override]
    public function setData($data): self
    {
        $this->data = $data['data'];

        return $this;
    }

    /**
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (config("database.connections.{$this->connectionTemplate}.driver") === 'sqlite') {
            return;
        }

        $connectionConfig = config("database.connections.{$this->connectionTemplate}");

        try {
            app(ConnectionFactory::class)->make(
                array_merge(
                    $connectionConfig,
                    [
                        'host' => $this->data[Tenant::internalPrefix().'db_host'],
                        'port' => $this->data[Tenant::internalPrefix().'db_port'],
                        'database' => $this->data[Tenant::internalPrefix().'db_name'],
                        'username' => $this->data[Tenant::internalPrefix().'db_username'],
                        'password' => $this->data[Tenant::internalPrefix().'db_password'],
                    ]
                )
            )->getPdo();
        } catch (PDOException $e) {
            report($e);
            $fail('Cannot connect with the database.');
        }
    }
}
