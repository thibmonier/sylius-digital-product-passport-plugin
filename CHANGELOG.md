# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased]

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
