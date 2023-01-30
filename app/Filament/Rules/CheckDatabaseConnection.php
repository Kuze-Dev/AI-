<?php

declare(strict_types=1);

namespace App\Filament\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Support\Arr;

class CheckDatabaseConnection implements DataAwareRule, InvokableRule
{
    protected array $data = [];

    public function __construct(
        protected string $databaseArrayPath
    ) {
    }

    public function setData($data)
    {
        $this->data = Arr::get($data, $this->databaseArrayPath);

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        try {
            app(ConnectionFactory::class)->make([
                'driver' => 'mysql',
                'host' => $this->data['host'],
                'port' => $this->data['port'],
                'database' => $this->data['name'],
                'username' => $this->data['username'],
                'password' => $this->data['password'],
            ])->getPdo();
        } catch (\Exception $e) {
            $fail('Cannot connect with the database.');
        }
    }
}
