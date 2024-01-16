<?php

declare(strict_types=1);

namespace Domain\Customer\Jobs;

use Domain\Customer\Actions\SendRegisterInvitationAction;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @template TModel as Customer
 */
class CustomerSendInvitationJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const LIMIT = 1_00;

    private readonly SendRegisterInvitationAction $sendRegisterInvitation;

    private readonly string $keyName;

    /**
     * @param  Collection<int, TModel>|null  $records
     * @param  array<RegisterStatus>  $registerStatuses
     */
    public function __construct(
        private ?int $initialCustomerKey = null,
        private readonly ?Collection $records = null,
        private readonly array $registerStatuses = []
    ) {
        $this->records?->ensure(Customer::class);

        $this->keyName = (new Customer())->getKeyName();

        $this->initialCustomerKey
            ??= $this->records?->sortByDesc($this->keyName)->value($this->keyName)
            ?? $this->query()->value($this->keyName);

        $this->sendRegisterInvitation = app(SendRegisterInvitationAction::class);
    }

    public function handle(): void
    {
        if ($this->initialCustomerKey === null) {
            $this->fail('No invitation will send');
        }

        $query = $this->query()
            ->where($this->keyName, '<=', $this->initialCustomerKey);

        $query
            ->limit(self::LIMIT)
            ->cursor()
            ->each(
                fn (Customer $customer) => $this->sendRegisterInvitation
                    ->execute($customer)
            );

        $ids = $query
            ->limit(self::LIMIT)
            ->pluck($this->keyName);

        if ($query->wherekeyNot($ids)->exists()) {
            dispatch(new self(
                initialCustomerKey: $query->value($this->keyName),
                records: $this->records,
                registerStatuses: $this->registerStatuses
            ));
        }
    }

    /**
     * @return Builder<Customer>
     */
    private function query(): Builder
    {
        /** @var Builder<Customer> $query */
        $query = Customer::query();

        return $query->latest($this->keyName)
            ->whereNot('register_status', RegisterStatus::REGISTERED)
            ->when(
                $this->records,
                function (Builder $query, Collection $records) {
                    /** @var Builder<Customer> $query */
                    return $query->whereIn($this->keyName, $records->pluck($this->keyName));
                }
            )
            ->when(
                $this->registerStatuses,
                function (Builder $query, array $registerStatuses) {
                    /** @var Builder<Customer> $query */
                    return $query->whereIn('register_status', $registerStatuses);
                }
            );
    }
}
