<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

use Carbon\Carbon;
use Domain\Customer\Enums\Status;
use Illuminate\Http\UploadedFile;

class CustomerData
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $mobile,
        public readonly Status $status,
        public readonly Carbon $birth_date,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?int $tier_id = null,
        public readonly UploadedFile|string|null $image = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $data['status'] = isset($data['status'])
            ? (
                is_bool($data['status'])
                ? ($data['status'] ? Status::ACTIVE : Status::INACTIVE)
                : Status::tryFrom($data['status'])
            )
            : Status::INACTIVE;

        $data['birth_date'] = now()->parse($data['birth_date']);

        if (isset($data['tier_id'])) {
            $data['tier_id'] = (int) $data['tier_id'];
        }

        return new self(...$data);
    }
}
