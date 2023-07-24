# Release Notes

## [Unreleased](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/master%0Dv0.2.4)

## [0.2.4](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/0.2.4%0Dv0.2.3)

### Added

- Evaluatable parameters for `Select::optionsFromModel()` macro.
- Added `wrap()` on table `TextColumns`.
- Added ellipsis for long titles and breadcrumbs.
- Added `Rule::email()` macro.
- Added export and import actions.

### Changed

- Updated `sortable()` and `searchable()` implementation on Filament Table columns.

## [v0.2.3](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.2.3%0Dv0.2.2)

## Added

- Add ability to update account of current logged-in admin.
- Improved `LogsFormActivity` extensibility.
- Add `Select::optionsFromModel()` macro.

## Changed

- `LogsFormActivity` will only log if the form was modified.
- `PermissionSeeder` will now update or create permissions then delete everything else that wasn't defined in the `permissionsByGuard()` method.

## [v0.2.2](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.2.2%0Dv0.2.1)

## Added

- Add general Properties `KeyValue` field for `ActivityResource` form.
- Add Event column for `ActivityResource` table.
- Add `Action::withActivityLog()` macro.
- Added `assertActivityLogged()` testing helper.

## Changed
- Upgrade `spatie/laravel-settings` to `v3.1.0`.
- Update `phpunit.xml` to match with `phpunit/phpunit` v10 specifications.

### Fixed

- Fix the description for activity logs on `CreateRecord` pages.
- Should fail gracefully when trying to resolve a subject's url in `ActivityResource` table.
- Remove bulk actions for `ActivityResource` table.
- `AdminResource` email field must be unique.
- Should not be able to impersonate a deleted `Admin`.

## [v0.2.1](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.2.1%0Dv0.2.0)

### Changed

- Sync `stechstudio/filament-impersonate` config.
- Revamp activity log.

## [v0.2.0](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.2.0%0Dv0.1.7)

### Added

- Success/fail prompt for resend verification action and request reset password.
- Standardized `DeleteAction`, `RestoreAction`, and `ForceDeleteAction` confirmation prompt.

### Changed

- Upgrade to laravel 10.
- Check if Admin is deleted for `resendVerification` and `sendPasswordReset` policy abilities.

## [v0.1.7](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.1.7%0Dv0.1.6)

### Added

- Added Settings API Endpoint.
- Added parallel testing support.
- Split Filament and Web asset entrypoints with thier own tailwind config

### Changed

- Moved ~~`resources/views/welcome.blade.php`~~ -> `resources/views/web/welcome.blade.php`

## [v0.1.6](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.1.6%0Dv0.1.5)

### Added

- Added `QueueCheck` on `HealthCheckServiceProvider`.
- Added impersonate for admin.

### Changed

- `two_factor_authentications.secret` is no longer nullable.

### Fixed

- Properly authorize existing settings permission.

## [v0.1.5](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.1.5%0Dv0.1.4)

### Changed

- Sync boilerplate with [laravel/laravel v9.4.0](https://github.com/laravel/laravel/releases/tag/v9.4.0)

### Fixed

- Manually load relationship of admin roles and permissions in select fields.
- `Settings` pages should fill data.

## [v0.1.4](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.1.4%0Dv0.1.3)

### Added

- Added `created` listener to setup 2FA when booting `TwoFactorAuthenticatable` trait.
- Added `ActivityPolicy` and necessary permissions.
- Added authorization features for `Settings` and necessary permissions.
- Added `PermissionSeeder::generatePermissionGroup()`.

### Changed

- `loginAsAdmin()` no longer logs in as a super admin, use `loginAsSuperAdmin()` instead.
- Updated `config/filament-laravel-log.php` to check for authorization.
- Moved `ViewLog::can` from ~~`AuthServiceProvider`~~ -> `FilamentServiceProvider`.
- Only Admins with Super Admin role can view `HealthCheckResults`.
- Implement `Model::shouldBeStrict()`.
- Update `spatie/laravel-permission` to use `^5.7`.
- Simplify authorization call `AdminResource`'s `resend-verification` action.

## [v0.1.3](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.1.3%0Dv0.1.2)

### Fixed

- Fixed translations in blade files.

### Changed

- Removed `spatie/data-transfer-object`.

## [v0.1.2](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.1.2%0Dv0.1.1)

### Fixed

- Properly assign default value for `*_abilities` in `RoleResource`

### Changed

- Increased pipelines PHP command's memory limt.

## [v0.1.1](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/v0.1.1%0Dv0.1.0)

### Changed

- Updated `SiteSettings` uploaded file name.
- Allow string in column `activity_log.subject_id` for uuid.

## v0.1.0

- First prototype 🎉
