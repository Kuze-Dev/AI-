<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace Domain\Admin\Models{
/**
 * Domain\Admin\Models\Admin
 *
 * @property int $id
 * @property-read string $full_name
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $password_changed_at
 * @property bool $active
 * @property string $timezone
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property bool $to_be_logged_out
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Role\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Domain\Auth\Model\TwoFactorAuthentication|null $twoFactorAuthentication
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePasswordChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereToBeLoggedOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Role\Models\Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 */
	class Admin extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail, \Filament\Models\Contracts\HasName, \Domain\Auth\Contracts\TwoFactorAuthenticatable, \Filament\Models\Contracts\FilamentUser, \Domain\Auth\Contracts\HasActiveState {}
}

namespace Domain\Auth\Model{
/**
 * Domain\Auth\Model\RecoveryCode
 *
 * @property int $id
 * @property int $two_factor_authentication_id
 * @property mixed $code
 * @property int|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereTwoFactorAuthenticationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereUsedAt($value)
 * @mixin \Eloquent
 */
	class RecoveryCode extends \Eloquent {}
}

namespace Domain\Auth\Model{
/**
 * Domain\Auth\Model\SafeDevice
 *
 * @property int $id
 * @property int $two_factor_authentication_id
 * @property string $ip
 * @property string $user_agent
 * @property string $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereTwoFactorAuthenticationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereUserAgent($value)
 * @mixin \Eloquent
 */
	class SafeDevice extends \Eloquent {}
}

namespace Domain\Auth\Model{
/**
 * Domain\Auth\Model\TwoFactorAuthentication
 *
 * @property int $id
 * @property string $authenticatable_type
 * @property int $authenticatable_id
 * @property string|null $enabled_at
 * @property mixed $secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|Eloquent $authenticatable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Auth\Model\RecoveryCode> $recoveryCodes
 * @property-read int|null $recovery_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Auth\Model\SafeDevice> $safeDevices
 * @property-read int|null $safe_devices_count
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication query()
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereAuthenticatableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereAuthenticatableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereEnabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Auth\Model\RecoveryCode> $recoveryCodes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Auth\Model\SafeDevice> $safeDevices
 */
	class TwoFactorAuthentication extends \Eloquent {}
}

namespace Domain\Blueprint\Models{
/**
 * Domain\Blueprint\Models\Blueprint
 *
 * @property string $id
 * @property string $name
 * @property \Domain\Blueprint\DataTransferObjects\SchemaData $schema
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereSchema($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 */
	class Blueprint extends \Eloquent {}
}

namespace Domain\Content\Models{
/**
 * Domain\Content\Models\Content
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $slug
 * @property string $prefix
 * @property PublishBehavior|null $future_publish_date_behavior
 * @property PublishBehavior|null $past_publish_date_behavior
 * @property bool $is_sortable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Content\Models\ContentEntry> $contentEntries
 * @property-read int|null $content_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Taxonomy> $taxonomies
 * @property-read int|null $taxonomies_count
 * @method static \Illuminate\Database\Eloquent\Builder|Content newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content query()
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereFuturePublishDateBehavior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereIsSortable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content wherePastPublishDateBehavior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Content\Models\ContentEntry> $contentEntries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\Taxonomy> $taxonomies
 */
	class Content extends \Eloquent {}
}

namespace Domain\Content\Models{
/**
 * Domain\Content\Models\ContentEntry
 *
 * @property int $id
 * @property int|null $author_id
 * @property int $content_id
 * @property string $title
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property array $data
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Admin|null $author
 * @property-read \Domain\Content\Models\Content $content
 * @property-read \Domain\Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read int|null $route_urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @method static ContentEntryBuilder|ContentEntry newModelQuery()
 * @method static ContentEntryBuilder|ContentEntry newQuery()
 * @method static ContentEntryBuilder|ContentEntry query()
 * @method static ContentEntryBuilder|ContentEntry whereAuthorId($value)
 * @method static ContentEntryBuilder|ContentEntry whereContentId($value)
 * @method static ContentEntryBuilder|ContentEntry whereCreatedAt($value)
 * @method static ContentEntryBuilder|ContentEntry whereData($value)
 * @method static ContentEntryBuilder|ContentEntry whereId($value)
 * @method static ContentEntryBuilder|ContentEntry whereOrder($value)
 * @method static ContentEntryBuilder|ContentEntry wherePublishStatus(?\Domain\Content\Enums\PublishBehavior $publishBehavior = null, ?string $timezone = null)
 * @method static ContentEntryBuilder|ContentEntry wherePublishedAt($value)
 * @method static ContentEntryBuilder|ContentEntry wherePublishedAtRange(?\Carbon\Carbon $publishedAtStart = null, ?\Carbon\Carbon $publishedAtEnd = null)
 * @method static ContentEntryBuilder|ContentEntry wherePublishedAtYearMonth(int $year, ?int $month = null)
 * @method static ContentEntryBuilder|ContentEntry whereSlug($value)
 * @method static ContentEntryBuilder|ContentEntry whereTaxonomyTerms(string $taxonomy, array $terms)
 * @method static ContentEntryBuilder|ContentEntry whereTitle($value)
 * @method static ContentEntryBuilder|ContentEntry whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\TaxonomyTerm> $taxonomyTerms
 */
	class ContentEntry extends \Eloquent implements \Domain\Support\MetaData\Contracts\HasMetaData, \Domain\Support\RouteUrl\Contracts\HasRouteUrl {}
}

namespace Domain\Discount\Models{
/**
 * Domain\Discount\Models\Discount
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property DiscountConditionType $type
 * @property mixed $status
 * @property int $max_uses
 * @property int $max_uses_per_user
 * @property \Illuminate\Support\Carbon $valid_start_at
 * @property \Illuminate\Support\Carbon|null $valid_end_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Discount\Models\DiscountCondition> $DiscountConditions
 * @property-read int|null $discount_conditions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Discount\Models\DiscountCode> $discountCodes
 * @property-read int|null $discount_codes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereMaxUsesPerUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValidEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValidStartAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Discount\Models\DiscountCondition> $DiscountConditions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Discount\Models\DiscountCode> $discountCodes
 */
	class Discount extends \Eloquent {}
}

namespace Domain\Discount\Models{
/**
 * Domain\Discount\Models\DiscountCode
 *
 * @property int $id
 * @property int $discount_id
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Discount\Models\Discount|null $discount
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DiscountCode extends \Eloquent {}
}

namespace Domain\Discount\Models{
/**
 * Domain\Discount\Models\DiscountCondition
 *
 * @property int $id
 * @property int $discount_id
 * @property DiscountConditionType $type
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Discount\Models\Discount|null $discount
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DiscountCondition extends \Eloquent {}
}

namespace Domain\Form\Models{
/**
 * Domain\Form\Models\Form
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $slug
 * @property bool $store_submission
 * @property int $uses_captcha
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormEmailNotification> $formEmailNotifications
 * @property-read int|null $form_email_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormSubmission> $formSubmissions
 * @property-read int|null $form_submissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereStoreSubmission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUsesCaptcha($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormEmailNotification> $formEmailNotifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormSubmission> $formSubmissions
 */
	class Form extends \Eloquent {}
}

namespace Domain\Form\Models{
/**
 * Domain\Form\Models\FormEmailNotification
 *
 * @property int $id
 * @property int $form_id
 * @property array|null $to
 * @property array|null|null $cc
 * @property array|null|null $bcc
 * @property string $sender
 * @property array|null|null $reply_to
 * @property string $subject
 * @property string $template
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Form\Models\Form $form
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereBcc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereCc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereReplyTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 */
	class FormEmailNotification extends \Eloquent {}
}

namespace Domain\Form\Models{
/**
 * Domain\Form\Models\FormSubmission
 *
 * @property int $id
 * @property int $form_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Form\Models\Form $form
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 */
	class FormSubmission extends \Eloquent {}
}

namespace Domain\Globals\Models{
/**
 * Domain\Globals\Models\Globals
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $blueprint_id
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @method static \Illuminate\Database\Eloquent\Builder|Globals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals query()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 */
	class Globals extends \Eloquent {}
}

namespace Domain\Menu\Models{
/**
 * Domain\Menu\Models\Menu
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $nodes
 * @property-read int|null $nodes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $parentNodes
 * @property-read int|null $parent_nodes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $nodes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $parentNodes
 */
	class Menu extends \Eloquent {}
}

namespace Domain\Menu\Models{
/**
 * Domain\Menu\Models\Node
 *
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $label
 * @property Target $target
 * @property NodeType $type
 * @property string|null $url
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Node> $children
 * @property-read int|null $children_count
 * @property-read \Domain\Menu\Models\Menu|null $menu
 * @property-read Model|Eloquent $model
 * @method static Builder|Node newModelQuery()
 * @method static Builder|Node newQuery()
 * @method static Builder|Node ordered(string $direction = 'asc')
 * @method static Builder|Node query()
 * @method static Builder|Node whereCreatedAt($value)
 * @method static Builder|Node whereId($value)
 * @method static Builder|Node whereLabel($value)
 * @method static Builder|Node whereMenuId($value)
 * @method static Builder|Node whereModelId($value)
 * @method static Builder|Node whereModelType($value)
 * @method static Builder|Node whereOrder($value)
 * @method static Builder|Node whereParentId($value)
 * @method static Builder|Node whereTarget($value)
 * @method static Builder|Node whereType($value)
 * @method static Builder|Node whereUpdatedAt($value)
 * @method static Builder|Node whereUrl($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Node> $children
 */
	class Node extends \Eloquent implements \Spatie\EloquentSortable\Sortable {}
}

namespace Domain\Page\Models{
/**
 * Domain\Page\Models\Block
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $component
 * @property bool $is_fixed_content
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read int|null $block_contents_count
 * @property-read Blueprint $blueprint
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|Block newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block query()
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereComponent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereIsFixedContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 */
	class Block extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace Domain\Page\Models{
/**
 * Domain\Page\Models\BlockContent
 *
 * @property int $id
 * @property int $block_id
 * @property int $page_id
 * @property mixed|null $data
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Page\Models\Block $block
 * @method static Builder|BlockContent newModelQuery()
 * @method static Builder|BlockContent newQuery()
 * @method static Builder|BlockContent ordered(string $direction = 'asc')
 * @method static Builder|BlockContent query()
 * @method static Builder|BlockContent whereBlockId($value)
 * @method static Builder|BlockContent whereCreatedAt($value)
 * @method static Builder|BlockContent whereData($value)
 * @method static Builder|BlockContent whereId($value)
 * @method static Builder|BlockContent whereOrder($value)
 * @method static Builder|BlockContent wherePageId($value)
 * @method static Builder|BlockContent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class BlockContent extends \Eloquent implements \Spatie\EloquentSortable\Sortable {}
}

namespace Domain\Page\Models{
/**
 * Domain\Page\Models\Page
 *
 * @property int $id
 * @property int|null $author_id
 * @property string $name
 * @property string $slug
 * @property Visibility $visibility
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Admin|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read int|null $block_contents_count
 * @property-read \Domain\Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read int|null $route_urls_count
 * @method static PageBuilder|Page newModelQuery()
 * @method static PageBuilder|Page newQuery()
 * @method static PageBuilder|Page query()
 * @method static PageBuilder|Page whereAuthorId($value)
 * @method static PageBuilder|Page whereCreatedAt($value)
 * @method static PageBuilder|Page whereId($value)
 * @method static PageBuilder|Page whereName($value)
 * @method static PageBuilder|Page wherePublishedAt($value)
 * @method static PageBuilder|Page wherePublishedAtRange(?\Carbon\Carbon $publishedAtStart = null, ?\Carbon\Carbon $publishedAtEnd = null)
 * @method static PageBuilder|Page wherePublishedAtYearMonth(int $year, ?int $month = null)
 * @method static PageBuilder|Page whereSlug($value)
 * @method static PageBuilder|Page whereUpdatedAt($value)
 * @method static PageBuilder|Page whereVisibility($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Support\RouteUrl\Models\RouteUrl> $routeUrls
 */
	class Page extends \Eloquent implements \Domain\Support\MetaData\Contracts\HasMetaData, \Domain\Support\RouteUrl\Contracts\HasRouteUrl {}
}

namespace Domain\Role\Models{
/**
 * Domain\Role\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Admin\Models\Admin> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Admin\Models\Admin> $users
 */
	class Role extends \Eloquent {}
}

namespace Domain\Support\MetaData\Models{
/**
 * Domain\Support\MetaData\Models\MetaData
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $title
 * @property string|null $author
 * @property string|null $description
 * @property string|null $keywords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Model|Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData query()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 */
	class MetaData extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace Domain\Support\RouteUrl\Models{
/**
 * Domain\Support\RouteUrl\Models\RouteUrl
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $url
 * @property bool $is_override
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl query()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereIsOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereUrl($value)
 * @mixin Eloquent
 */
	class RouteUrl extends \Eloquent {}
}

namespace Domain\Taxonomy\Models{
/**
 * Domain\Taxonomy\Models\Taxonomy
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Content> $contents
 * @property-read int|null $contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\TaxonomyTerm> $parentTerms
 * @property-read int|null $parent_terms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy query()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Content\Models\Content> $contents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\TaxonomyTerm> $parentTerms
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\TaxonomyTerm> $taxonomyTerms
 */
	class Taxonomy extends \Eloquent {}
}

namespace Domain\Taxonomy\Models{
/**
 * Domain\Taxonomy\Models\TaxonomyTerm
 *
 * @property int $id
 * @property int $taxonomy_id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property array $data
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ContentEntry> $contentEntries
 * @property-read int|null $content_entries_count
 * @property-read \Domain\Taxonomy\Models\Taxonomy $taxonomy
 * @method static Builder|TaxonomyTerm newModelQuery()
 * @method static Builder|TaxonomyTerm newQuery()
 * @method static Builder|TaxonomyTerm ordered(string $direction = 'asc')
 * @method static Builder|TaxonomyTerm query()
 * @method static Builder|TaxonomyTerm whereCreatedAt($value)
 * @method static Builder|TaxonomyTerm whereData($value)
 * @method static Builder|TaxonomyTerm whereId($value)
 * @method static Builder|TaxonomyTerm whereName($value)
 * @method static Builder|TaxonomyTerm whereOrder($value)
 * @method static Builder|TaxonomyTerm whereParentId($value)
 * @method static Builder|TaxonomyTerm whereSlug($value)
 * @method static Builder|TaxonomyTerm whereTaxonomyId($value)
 * @method static Builder|TaxonomyTerm whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Content\Models\ContentEntry> $contentEntries
 */
	class TaxonomyTerm extends \Eloquent implements \Spatie\EloquentSortable\Sortable {}
}

namespace Domain\Tenant\Models{
/**
 * Domain\Tenant\Models\Tenant
 *
 * @property string $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array|null $data
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Domain> $domains
 * @property-read int|null $domains_count
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> all($columns = ['*'])
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Stancl\Tenancy\Database\Models\Domain> $domains
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> all($columns = ['*'])
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> get($columns = ['*'])
 */
	class Tenant extends \Eloquent implements \Stancl\Tenancy\Contracts\TenantWithDatabase {}
}

