# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased]

### Added
- Optional battery profile on passports (`BatteryProfile`, `BatteryCategory`) for Regulation (EU) 2023/1542 art. 77, public level: mandatory category, chemistry, nominal capacity, nominal voltage, weight and manufacturing month; optional voltages, place, carbon footprint, recycled content, renewable share, cycle life, hazardous substances, critical raw materials, extinguishing agent, warranty (US-284)
- Symfony Validator constraints on the profile (field named when missing, percentages 0-100) and a non-blocking notice for portable and SLI batteries (`BatteryScopeAdvisor`)
- "Battery" section on the public passport page, in English and French, with units
- The profile is recorded in passport revisions
- JSON export `schemaVersion` 2 (version 1 plus a `battery` object) for a passport with a profile; a passport without a profile exports exactly the version 1 keys
- Two reversible migrations (`tmonier_battery_profile` table, `battery_profile_id` column); the passport page is now also published when only a battery profile is filled in

## [0.3.1] - 2026-11-04

### Fixed
- Admin product « Labels » tab rendered as a bullet outside the side navigation: it now uses the Sylius 2 side navigation markup (`list-group-item` button, Bootstrap `tab`), like the other tabs

### Changed
- `endroid/qr-code` constraint widened to ^5.0 || ^6.1; `QrGenerator` uses `QrCode` and the writers directly (the `Builder` API differs between 5 and 6, `Builder::create()` was removed in 6). Output parameters are unchanged (UTF-8, high error correction, size 300, margin 10) (US-268)

## [0.3.0] - 2026-10-30

### Added
- Passport block on the machine page of customer-asset-management (^0.2.1): link to the published passport of the equipment model and its compatible spare parts (US-242)

## [0.2.0] - 2026-10-30

### Added
- GTIN validation (8, 12, 13 and 14 digits, check digit) and GS1 Digital Link generation (US-232)
- QR code of the GS1 Digital Link (endroid/qr-code ^5) and "Labels" tab in the admin product edit page (US-232)
- Optional equipment model code on the passport (migration) and compatible spare parts block on the public page when customer-asset-management is installed (US-233)
- Screenshots of the public page in the README (US-241)

## [0.1.0] - 2026-10-30

### Added
- Versioned passport data model per product variant, with revision history (US-230)
- JSON export with schema version (US-230)
- Public passport page, English and French, 404 for unknown or unpublished passports (US-231)
- "Product Passport" link on the shop product page through a Twig hook (US-231)
