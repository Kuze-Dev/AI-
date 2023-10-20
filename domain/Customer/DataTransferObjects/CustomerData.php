<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

use App\HttpTenantApi\Requests\Auth\Customer\CustomerRegisterRequest;
use Carbon\Carbon;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Auth\Enums\EmailVerificationType;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Enums\TierApprovalStatus;
use Domain\Tier\Models\Tier;
use Illuminate\Http\UploadedFile;

final class CustomerData
{
    private function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly ?string $mobile,
        public readonly ?Gender $gender,
        public readonly ?Carbon $birth_date,
        public readonly ?Status $status = null,
        public readonly ?int $tier_id = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly UploadedFile|string|null $image = null,
        public readonly ?AddressData $shipping_address_data = null,
        public readonly ?AddressData $billing_address_data = null,
        public readonly EmailVerificationType $email_verification_type = EmailVerificationType::LINK,
        public readonly RegisterStatus $register_status = RegisterStatus::REGISTERED,
        public readonly ?TierApprovalStatus $tier_approval_status = null,
        public readonly bool $through_api_registration = false,
    ) {
    }

    public static function fromRegistrationRequest(
        CustomerRegisterRequest $request
    ): self {

        $validated = $request->validated();
        $sameAsShipping = $request->boolean('billing.same_as_shipping');

        $tier = null;

        if (isset($validated['tier_id'])) {
            /** @var \Domain\Tier\Models\Tier $tier */
            $tier = Tier::whereId($validated['tier_id'])->first();
        }

        $register_status = self::getStatus( ! isset($validated['tier_id']) ? null : $tier, $validated, null);

        /** @var \Domain\Tier\Models\Tier $defaultTier */
        $defaultTier = Tier::whereName(config('domain.tier.default'))->first();

        unset($request);

        return new self(
            first_name: $validated['first_name'],
            last_name: $validated['last_name'],
            mobile: $validated['mobile'],
            gender: Gender::from($validated['gender']),
            birth_date: now()->parse($validated['birth_date']),
            status: Status::ACTIVE,
            tier_id:  is_null($validated['tier_id']) ? $defaultTier->getKey() : (int) $validated['tier_id'],
            email: $validated['email'],
            password: $validated['password'],
            image: $validated['profile_image'] ?? null,
            shipping_address_data: isset($validated['shipping'])
            ? new AddressData(
                state_id: (int) $validated['shipping']['state_id'],
                label_as: $validated['shipping']['label_as'],
                address_line_1: $validated['shipping']['address_line_1'],
                zip_code: $validated['shipping']['zip_code'],
                city: $validated['shipping']['city'],
                is_default_shipping: true,
                is_default_billing: $sameAsShipping,
            ) : null,
            billing_address_data: ! $sameAsShipping && isset($validated['billing'])
            ? new AddressData(
                state_id: (int) $validated['billing']['state_id'],
                label_as: $validated['billing']['label_as'],
                address_line_1: $validated['billing']['address_line_1'],
                zip_code: $validated['billing']['zip_code'],
                city: $validated['billing']['city'],
                is_default_shipping: false,
                is_default_billing: true,
            ) : null,
            email_verification_type: isset($validated['email_verification_type'])
                ? EmailVerificationType::from($validated['email_verification_type'])
                : EmailVerificationType::LINK,
            register_status: $register_status,
            tier_approval_status: null,
            through_api_registration: true,
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
            mobile: $data['mobile'] ?? null,
            gender: isset($data['gender']) ? Gender::from($data['gender']) : null,
            birth_date: isset($data['birth_date']) ? now()->parse($data['birth_date']) : null,
            status: isset($data['status']) ? Status::from($data['status']) : null,
            tier_id: isset($data['tier_id']) ? ((int) $data['tier_id']) : null,
            email: $data['email'],
            password: $data['password'] ?? null,
            image: $data['image'] ?? null,
            tier_approval_status: TierApprovalStatus::APPROVED,
            register_status: RegisterStatus::UNREGISTERED,
        );
    }

    public static function fromArrayEditByAdmin(Customer $customer, array $data): self
    {

        $tier = Tier::whereId($data['tier_id'])->first();
        $registerStatus = self::getStatus($tier, $data, $customer);

        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            mobile: $data['mobile'] ?? null,
            gender: isset($data['gender']) ? Gender::from($data['gender']) : null,
            birth_date: isset($data['birth_date']) ? now()->parse($data['birth_date']) : null,
            status: isset($data['status']) ? Status::from($data['status']) : null,
            tier_id: isset($data['tier_id']) ? ((int) $data['tier_id']) : null,
            email: $data['email'],
            image: $data['image'],
            tier_approval_status: isset($data['tier_approval_status']) ? TierApprovalStatus::from($data['tier_approval_status']) : null,
            register_status: $registerStatus,
        );
    }

    public static function fromArrayImportByAdmin(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            mobile: $data['mobile'] ?? null,
            gender: isset($data['gender']) ? Gender::from($data['gender']) : null,
            birth_date: isset($data['birth_date']) ? now()->parse($data['birth_date']) : null,
            status: isset($data['status']) ? Status::from($data['status']) : null,
            tier_id: isset($data['tier_id']) ? ((int) $data['tier_id']) : null,
            email: $data['email'],
            register_status: RegisterStatus::UNREGISTERED,
        );
    }

    private static function getStatus(Tier $tier = null, array $data = null, Customer $customer = null): RegisterStatus
    {

        $isTierWholesaler = in_array(
            $tier?->name,
            [
                config('domain.tier.wholesaler-domestic'),
                config('domain.tier.wholesaler-international'),
            ]
        );

        $registerStatus = RegisterStatus::UNREGISTERED;

        $unregistered_customer = Customer::whereEmail($customer?->email)->first();

        if ( ! is_null($data) && array_key_exists('tier_approval_status', $data)) {
            if ($data['tier_approval_status'] == TierApprovalStatus::REJECTED->value) {
                return $registerStatus = RegisterStatus::REJECTED;
            }
            //if the customer was created in admin but was not yet sent an invitation or the customer registered and picked wholesaler as tier and waiting to be approved,
            // initial register status of a customer
            if( ! isset($data['tier_approval_status']) && $isTierWholesaler) {
                return $registerStatus;
            }

            if($data['tier_approval_status'] == TierApprovalStatus::REJECTED->value) {
                return $registerStatus = RegisterStatus::REJECTED;
            }

            //if the customer registered through api and picked wholesaler as tier and was approved by admin, the initial register status was unregistered,
            //but since approved, now registered.
            if($isTierWholesaler && $data['tier_approval_status'] == TierApprovalStatus::APPROVED->value && $customer?->register_status == RegisterStatus::UNREGISTERED) {
                return $registerStatus = RegisterStatus::REGISTERED;
            }

            if($isTierWholesaler && $data['tier_approval_status'] == TierApprovalStatus::APPROVED->value && $customer?->register_status == RegisterStatus::REJECTED) {
                return $registerStatus = RegisterStatus::REGISTERED;
            }

        }
        //if customer registered through api but no tier indicated or if default was picked
        if((is_null($tier) || $tier->name == config('domain.tier.default')) && is_null($unregistered_customer)) {
            return $registerStatus = RegisterStatus::REGISTERED;
        }

        //if customer was created in admin and was sent an invitation and the tier selected by the admin is wholesaler
        if($isTierWholesaler && $customer?->tier_approval_status == TierApprovalStatus::APPROVED->value && ($customer?->register_status == RegisterStatus::INVITED)) {
            return $registerStatus = RegisterStatus::REGISTERED;
        }

        return $registerStatus;
    }
}
