<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

use App\HttpTenantApi\Requests\Auth\Customer\CustomerRegisterRequest;
use Carbon\Carbon;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Auth\Enums\EmailVerificationType;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\Status;
use Domain\Tier\Models\Tier;
use Illuminate\Http\UploadedFile;

final class CustomerData
{
    private function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $mobile,
        public readonly Gender $gender,
        public readonly Carbon $birth_date,
        public readonly ?Status $status = null,
        public readonly ?int $tier_id = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly UploadedFile|string|null $image = null,
        public readonly ?AddressData $shipping_address_data = null,
        public readonly ?AddressData $billing_address_data = null,
        public readonly EmailVerificationType $email_verification_type = EmailVerificationType::LINK,
    ) {
    }

    public static function fromRegistrationRequest(
        Tier $tier,
        CustomerRegisterRequest $request
    ): self {
        $validated = $request->validated();
        $sameAsShipping = $request->boolean('billing.same_as_shipping');
        unset($request);

        return new self(
            first_name: $validated['first_name'],
            last_name: $validated['last_name'],
            mobile: $validated['mobile'],
            gender: Gender::from($validated['gender']),
            birth_date: now()->parse($validated['birth_date']),
            status: Status::ACTIVE,
            tier_id: $tier->getKey(),
            email: $validated['email'],
            password: $validated['password'],
            shipping_address_data: new AddressData(
                state_id: (int) $validated['shipping']['state_id'],
                label_as: $validated['shipping']['label_as'],
                address_line_1: $validated['shipping']['address_line_1'],
                zip_code: $validated['shipping']['zip_code'],
                city: $validated['shipping']['city'],
                is_default_shipping: true,
                is_default_billing: $sameAsShipping,
            ),
            billing_address_data: $sameAsShipping
                ? null
                : new AddressData(
                    state_id: (int) $validated['billing']['state_id'],
                    label_as: $validated['billing']['label_as'],
                    address_line_1: $validated['billing']['address_line_1'],
                    zip_code: $validated['billing']['zip_code'],
                    city: $validated['billing']['city'],
                    is_default_shipping: false,
                    is_default_billing: true,
                ),
            email_verification_type: isset($validated['email_verification_type'])
                ? EmailVerificationType::from($validated['email_verification_type'])
                : EmailVerificationType::LINK,
        );
    }

    public static function formArrayCustomerEditAPI(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            mobile: $data['mobile'],
            gender: Gender::from($data['gender']),
            birth_date: now()->parse($data['birth_date']),
            email: $data['email'],
        );
    }

    public static function fromArrayCreateByAdmin(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            mobile: $data['mobile'],
            gender: Gender::from($data['gender']),
            birth_date: now()->parse($data['birth_date']),
            status: Status::from($data['status']),
            tier_id: (int) $data['tier_id'],
            email: $data['email'],
            password: $data['password'] ?? null,
            image: $data['image'],
            shipping_address_data: new AddressData(
                state_id: (int) $data['shipping_state_id'],
                label_as: $data['shipping_label_as'],
                address_line_1: $data['shipping_address_line_1'],
                zip_code: $data['shipping_zip_code'],
                city: $data['shipping_city'],
                is_default_shipping: true,
                is_default_billing: $data['same_as_shipping'],
            ),
            billing_address_data: $data['same_as_shipping']
                ? null
                : new AddressData(
                    state_id: (int) $data['billing_state_id'],
                    label_as: $data['billing_label_as'],
                    address_line_1: $data['billing_address_line_1'],
                    zip_code: $data['billing_zip_code'],
                    city: $data['billing_city'],
                    is_default_shipping: false,
                    is_default_billing: true,
                ),
        );
    }

    public static function fromArrayEditByAdmin(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            mobile: $data['mobile'],
            gender: Gender::from($data['gender']),
            birth_date: now()->parse($data['birth_date']),
            status: Status::from($data['status']),
            tier_id: (int) $data['tier_id'],
            email: $data['email'],
            image: $data['image'],
        );
    }

    public static function fromArrayImportByAdmin(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            mobile: $data['mobile'],
            gender: Gender::from($data['gender']),
            birth_date: now()->parse($data['birth_date']),
            status: Status::from($data['status']),
            tier_id: $data['tier_id'] ?? null,
            email: $data['email'],
        );
    }
}
