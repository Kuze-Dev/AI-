<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

use App\Features\Customer\TierBase;
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
        CustomerRegisterRequest $request,
        Tier $customerTier = null,
        Tier $defaultTier
    ): self {

        $validated = $request->validated();
        $sameAsShipping = $request->boolean('billing.same_as_shipping');

        if( ! $customerTier || ! tenancy()->tenant?->features()->active(TierBase::class)) {
            $registerStatus = self::getStatus($defaultTier, null, null);
            $tierId = $defaultTier->getKey();
        } else {
            $registerStatus = self::getStatus($customerTier, null, null);
            $tierId = $customerTier->getKey();
        }

        unset($request);

        return new self(
            first_name: $validated['first_name'],
            last_name: $validated['last_name'],
            mobile: $validated['mobile'],
            gender: Gender::from($validated['gender']),
            birth_date: now()->parse($validated['birth_date']),
            status: Status::ACTIVE,
            tier_id:  $tierId,
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
            register_status: $registerStatus,
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

    public static function fromArrayEditByAdmin(Customer $customer, array $data, Tier $tier = null): self
    {

        $tierApprovalStatus = ! isset($data['tier_approval_status']) ? null : TierApprovalStatus::from($data['tier_approval_status']);

        $registerStatus = self::getStatus($tier, $tierApprovalStatus, $customer);

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

    private static function getStatus(
        Tier $tier = null,
        TierApprovalStatus $tierApprovalStatus = null,
        Customer $customer = null
    ): RegisterStatus {

        if( ! tenancy()->tenant?->features()->active(TierBase::class)) {
            return  RegisterStatus::REGISTERED;
        }

        if ($tierApprovalStatus !== null) {
            if(
                $tier?->has_approval &&
                $tierApprovalStatus === TierApprovalStatus::APPROVED &&
                $customer?->register_status == RegisterStatus::UNREGISTERED

            ) {
                return  RegisterStatus::REGISTERED;
            }

        }

        if($tier?->isDefault() === null && $customer === null) {
            return  RegisterStatus::REGISTERED;
        }

        if (
            $tier?->has_approval &&
            $customer?->tier_approval_status == TierApprovalStatus::APPROVED &&
            $customer?->register_status == RegisterStatus::INVITED
        ) {
            return  RegisterStatus::REGISTERED;
        }

        return RegisterStatus::UNREGISTERED;
    }
}
