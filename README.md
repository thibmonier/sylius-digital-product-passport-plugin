# Digital Product Passport Plugin for Sylius

[![Build](https://github.com/thibmonier/sylius-digital-product-passport-plugin/actions/workflows/build.yaml/badge.svg)](https://github.com/thibmonier/sylius-digital-product-passport-plugin/actions/workflows/build.yaml)

[🇫🇷 Version française](README.fr.md)

Open and versioned sustainability data model for Sylius 2 products: origin, recyclability, repairability, with a public passport page.

**Important**: This plugin provides a **preparatory data model** aligned with ESPR (EU) 2024/1781. It makes **NO promise of regulatory compliance** until delegated acts by product category are published.

## Features

- **Versioned data model**: open schema with version tracking and revision history
- **Sustainability data**: origin of materials, composition, recyclability, repairability index and instructions, warranty, spare parts references
- **Public passport page**: accessible without login, shows all sustainability information
- **Admin interface**: Symfony forms to create and edit product passports
- **Export**: JSON export with versioned schema
- **GS1 Digital Link** (coming in v1.1): GTIN-14 support and QR code generation

## Demo

See the plugin in action: [sylius-b2b.thibaut-monier.fr](https://sylius-b2b.thibaut-monier.fr/en_US/plugins)

## Installation

```bash
composer require tmonier/sylius-digital-product-passport-plugin
```

Enable the plugin in `config/bundles.php`:

```php
return [
    // ...
    Tmonier\SyliusDigitalProductPassportPlugin\TmonierSyliusDigitalProductPassportPlugin::class => ['all' => true],
];
```

Run migrations:

```bash
bin/console doctrine:migrations:migrate
```

## Usage

### Admin

Go to **Products > Product Passports** to create and edit passports for your products.

### Public Page

Each product with a passport has a public page at `/product-passport/{id}` showing:
- Origin of materials
- Recyclability information
- Repairability index and instructions
- Warranty details
- Compatible spare parts (if available)
- Schema version and last revision date

## Screenshots

### Admin Form
*(Coming soon with US-231)*

### Public Passport Page
*(Coming soon with US-231)*

## Requirements

- PHP 8.2 or higher
- Sylius 2.2.9+ or 2.3+
- Symfony 6.4+, 7.4+, or 8.0+

## Development

```bash
make install        # Install dependencies
make test           # Run tests
make phpstan        # Run PHPStan
make ecs            # Check coding standards
make quality        # Run all quality checks
```

## Regulatory Context

This plugin anticipates the Digital Product Passport obligation from ESPR (EU) 2024/1781:
- **Batteries**: February 18, 2027 (Regulation (EU) 2023/1542, Art. 77)
- **Other categories**: indicative roadmap (textiles Q4 2027, furniture 2028, etc.)

**Disclaimer**: This plugin provides a preparatory data structure. It makes **NO guarantee of regulatory compliance** until delegated acts define the exact data requirements for each product category.

## Author

[Thibaut Monier](https://www.linkedin.com/in/thibautmonier/) – AI Delivery Manager / Tech Lead

## License

MIT License. See [LICENSE](LICENSE) for details.

## Links

- [Demo](https://sylius-b2b.thibaut-monier.fr/en_US/plugins)
- [ESPR Regulation](https://eur-lex.europa.eu/eli/reg/2024/1781/oj?locale=en)
- [Digital Product Passport Registry](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_en)
