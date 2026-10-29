# CLAUDE.md

Sylius 2 plugin "Digital Product Passport": open and versioned sustainability data model (origin, recyclability, repairability), public passport page, no regulatory compliance promise.

## Commands

```bash
make install              # Install dependencies
make test                 # Run tests
make phpstan              # Run PHPStan (level max)
make ecs                  # Check coding standards
make rector               # Check Rector rules
make quality              # Run all quality checks
```

The test application reads `tests/TestApplication/config/*.yaml` (database `tmonier_sylius_digital_product_passport_test`).

## Architecture

- `Entity/DigitalProductPassport`: main entity with versioned schema and revisions
- `Repository/`: Doctrine repositories
- `Controller/`: admin and shop controllers
- `Form/`: Symfony forms
- Migrations use the portable Doctrine Schema API (no raw SQL)

## Important Notes

- **No regulatory compliance promise**: this plugin provides a preparatory data model aligned with ESPR (EU) 2024/1781, but makes NO promise of regulatory compliance
- **Tone**: neutral, no comparison with Sylius Plus
- **Security**: no sensitive data in plain text, no hardcoded secrets
