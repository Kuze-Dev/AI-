# Release Notes

## [Unreleased](https://bitbucket.org/halcyonlaravel/tall-boilerplate/branches/compare/master%0Dv0.1.7)

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
