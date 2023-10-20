<?php

declare(strict_types=1);

namespace App\Filament\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Support\Arr;
use PDOException;

class CheckDatabaseConnection implements DataAwareRule, InvokableRule
{
    protected array $data = [];

    public function __construct(
        protected string $connectionTemplate,
        protected string $databaseArrayPath
    ) {
    }

    public function setData($data)
    {
        $this->data = Arr::get($data, $this->databaseArrayPath);

        return $this;
    }

    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
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
                        'host' => $this->data['host'],
                        'port' => $this->data['port'],
                        'database' => $this->data['name'],
                        'username' => $this->data['username'],
                        'password' => $this->data['password'],
                    ]
                )
            )->getPdo();
        } catch (PDOException $e) {
            report($e);
            $fail('Cannot connect with the database.');
        }
    }
}
