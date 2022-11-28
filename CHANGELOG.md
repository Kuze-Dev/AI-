# Release Notes

## [Unreleased](https://bitbucket.org/halcyonlaravel/saas-boilerplate/branches/compare/master%0Dv0.1.4)

## [v0.1.4](https://bitbucket.org/halcyonlaravel/saas-boilerplate/branches/compare/v0.1.4%0Dv0.1.3)

### Changed

- Use DB transaction when calling tenant actions.
- Sync with [`halcyonlaravel/tall-boilerplate` v0.1.4](https://bitbucket.org/halcyonlaravel/tall-boilerplate/src/v0.1.4/)

## [v0.1.3](https://bitbucket.org/halcyonlaravel/saas-boilerplate/branches/compare/v0.1.3%0Dv0.1.2)

### Fixed

- Should end tenancy before deleting all tenants.

## [v0.1.2](https://bitbucket.org/halcyonlaravel/saas-boilerplate/branches/compare/v0.1.2%0Dv0.1.1)

### Added

- Added Activity Logs for `Tenant` model and `FilamentTenant` context.

## [v0.1.1](https://bitbucket.org/halcyonlaravel/saas-boilerplate/branches/compare/v0.1.1%0Dv0.1.0)

### Added

- Add `tenants:drop-db` command

### Changed

- Now initializes tenancy only by domain (previously initialized by domain **or** subdomain)
    - Subdomain is still allowed as long as you provide the full domain name (e.g `subdomain.central-domain.com`).

## v0.1.0

- First prototype 🎉
