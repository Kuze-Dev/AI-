<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

use App\Features\Customer\TierBase;
use App\HttpTenantApi\Requests\Auth\Customer\CustomerRegisterRequest;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Auth\Enums\EmailVerificationType;
use Domain\Blueprint\Models\Blueprint;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Enums\TierApprovalStatus;
use Domain\Tier\Models\Tier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

final readonly class CustomerData
{
    private function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $mobile = null,
        public ?Gender $gender = null,
        public ?Carbon $birth_date = null,
        public ?Status $status = null,
        public ?int $tier_id = null,
        public ?string $email = null,
        public ?string $password = null,
        public UploadedFile|string|null $image = null,
        public ?AddressData $shipping_address_data = null,
        public ?AddressData $billing_address_data = null,
        public EmailVerificationType $email_verification_type = EmailVerificationType::LINK,
        public RegisterStatus $register_status = RegisterStatus::REGISTERED,
        public ?TierApprovalStatus $tier_approval_status = null,
        public readonly ?array $data = [],
        public bool $through_api_registration = false,
    ) {
    }

    public static function fromRegistrationRequest(
        CustomerRegisterRequest $request,
        ?Tier $customerTier,
        Tier $defaultTier,
        ?Blueprint $customerBlueprint = null
    ): self {
        $tier = null;
        $validated = $request->validated();
        $sameAsShipping = $request->boolean('billing.same_as_shipping');
        unset($request);

        if (! tenancy()->tenant?->features()->active(TierBase::class) || $defaultTier->is($customerTier)) {
            $tier = $defaultTier;
        }

        if ($customerTier && $customerTier->has_approval && ! $customerTier->isDefault()) {
            $tier = $customerTier;
        }

        $customderBlueprintData = [];
        if ($customerBlueprint) {

            foreach ($customerBlueprint->schema->sections as $section) {
                $customderBlueprintData[$section->state_name] = $validated[$section->state_name];
            }
        }

        return new self(
            first_name: $validated['first_name'],
            last_name: $validated['last_name'],
            mobile: $validated['mobile'],
            gender: Gender::from($validated['gender']),
            birth_date: now()->parse($validated['birth_date']),
            status: Status::ACTIVE,
            tier_id: $tier?->getKey(),
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
            register_status: RegisterStatus::REGISTERED,
            tier_approval_status: null,
            through_api_registration: true,
            data: $customerBlueprint ? $customderBlueprintData : null,
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
            data: $data['data'],
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
            status: Status::INACTIVE,
            tier_id: isset($data['tier_id']) ? ((int) $data['tier_id']) : null,
            email: $data['email'],
            password: $data['password'] ?? null,
            image: $data['image'] ?? null,
            register_status: RegisterStatus::UNREGISTERED,
            tier_approval_status: TierApprovalStatus::APPROVED,
            data: $data['data'] ?? null,
        );
    }

    public static function fromArrayEditByAdmin(Customer $customer, array $data, ?Tier $tier = null): self
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
            register_status: $registerStatus,
            tier_approval_status: isset($data['tier_approval_status']) ? TierApprovalStatus::from($data['tier_approval_status']) : null,
            data: $data['data'] ?? null,
        );
    }

    public static function fromArrayImportByAdmin(
        ?string $customerPassword,
        ?int $tierKey,
        array $row
    ): self {
        return new self(
            first_name: $row['first_name'] ?? '',
            last_name: $row['last_name'] ?? '',
            mobile: $row['mobile'] ? (string) $row['mobile'] : null,
            gender: isset($row['gender']) ? Gender::from($row['gender']) : null,
            birth_date: isset($row['birth_date']) ? now()->parse($row['birth_date']) : null,
            tier_id: $tierKey,
            email: $row['email'],
            password: $customerPassword,
            register_status: RegisterStatus::UNREGISTERED,
        );
    }

    private static function getStatus(
        ?Tier $tier = null,
        ?TierApprovalStatus $tierApprovalStatus = null,
        ?Customer $customer = null
    ): RegisterStatus {

        if (! tenancy()->tenant?->features()->active(TierBase::class)) {
            return RegisterStatus::REGISTERED;
        }

        if ($tierApprovalStatus !== null) {
            if (
                $tier?->has_approval &&
                $tierApprovalStatus === TierApprovalStatus::APPROVED &&
                $customer?->register_status == RegisterStatus::UNREGISTERED

            ) {
                return RegisterStatus::REGISTERED;
            }

        }

        if ($tier?->isDefault()) {
            return RegisterStatus::REGISTERED;
        }

        if (
            $tier?->has_approval &&
            $customer?->tier_approval_status == TierApprovalStatus::APPROVED &&
            $customer?->register_status == RegisterStatus::INVITED
        ) {
            return RegisterStatus::REGISTERED;
        }

        if ($customer?->tier_approval_status === TierApprovalStatus::APPROVED) {
            return RegisterStatus::REGISTERED;
        }

        return RegisterStatus::UNREGISTERED;
    }

    public static function updateInvitedCustomer(
        array $data,
        ?Blueprint $customerBlueprint = null
    ): self {
        $customderBlueprintData = [];

        if ($customerBlueprint) {

            foreach ($customerBlueprint->schema->sections as $section) {
                $customderBlueprintData[$section->state_name] = $data[$section->state_name];
            }
        }

        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            mobile: $data['mobile'],
            gender: Gender::from($data['gender']),
            birth_date: now()->parse($data['birth_date']),
            status: Status::ACTIVE,
            email: $data['email'],
            password: $data['password'],
            image: $data['profile_image'] ?? null,
            tier_approval_status: TierApprovalStatus::APPROVED,
            register_status: RegisterStatus::REGISTERED,
            data: $customerBlueprint ? $customderBlueprintData : null,
        );
    }
}
