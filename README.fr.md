# Plugin Passeport Numérique Produit pour Sylius

[![Build](https://github.com/thibmonier/sylius-digital-product-passport-plugin/actions/workflows/build.yaml/badge.svg)](https://github.com/thibmonier/sylius-digital-product-passport-plugin/actions/workflows/build.yaml)

[🇬🇧 English version](README.md)

Modèle de données de durabilité ouvert et versionné pour les produits Sylius 2 : origine, recyclabilité, réparabilité, avec page publique du passeport.

**Important** : ce plugin fournit un **modèle de données préparatoire** aligné sur le règlement ESPR (UE) 2024/1781. Il ne fait **AUCUNE promesse de conformité réglementaire** tant que les actes délégués par catégorie de produits ne sont pas publiés.

## Fonctionnalités

- **Modèle de données versionné** : schéma ouvert avec suivi de version et historique des révisions
- **Données de durabilité** : origine des matériaux, composition, recyclabilité, indice de réparabilité et instructions, garantie, références de pièces détachées
- **Page publique du passeport** : accessible sans connexion, affiche toutes les informations de durabilité
- **Interface d'administration** : formulaires Symfony pour créer et modifier les passeports produit
- **Export** : export JSON avec schéma versionné
- **GS1 Digital Link** (à venir en v1.1) : support GTIN-14 et génération de QR code

## Démo

Voir le plugin en action : [sylius-b2b.thibaut-monier.fr](https://sylius-b2b.thibaut-monier.fr/fr_FR/plugins)

## Installation

```bash
composer require tmonier/sylius-digital-product-passport-plugin
```

Activer le plugin dans `config/bundles.php` :

```php
return [
    // ...
    Tmonier\SyliusDigitalProductPassportPlugin\TmonierSyliusDigitalProductPassportPlugin::class => ['all' => true],
];
```

Exécuter les migrations :

```bash
bin/console doctrine:migrations:migrate
```

## Utilisation

### Administration

Aller dans **Produits > Passeports Produit** pour créer et modifier les passeports de vos produits.

### Page Publique

Chaque produit avec un passeport a une page publique à `/product-passport/{id}` affichant :
- Origine des matériaux
- Informations de recyclabilité
- Indice de réparabilité et instructions
- Détails de garantie
- Pièces détachées compatibles (si disponibles)
- Version du schéma et date de dernière révision

## Captures d'écran

### Formulaire Admin
*(À venir avec US-231)*

### Page Publique du Passeport
*(À venir avec US-231)*

## Prérequis

- PHP 8.2 ou supérieur
- Sylius 2.2.9+ ou 2.3+
- Symfony 6.4+, 7.4+, ou 8.0+

## Développement

```bash
make install        # Installer les dépendances
make test           # Lancer les tests
make phpstan        # Lancer PHPStan
make ecs            # Vérifier les standards de code
make quality        # Lancer tous les contrôles qualité
```

## Contexte Réglementaire

Ce plugin anticipe l'obligation du Passeport Numérique Produit du règlement ESPR (UE) 2024/1781 :
- **Batteries** : 18 février 2027 (Règlement (UE) 2023/1542, Art. 77)
- **Autres catégories** : calendrier indicatif (textiles T4 2027, meubles 2028, etc.)

**Avertissement** : ce plugin fournit une structure de données préparatoire. Il ne garantit **AUCUNE conformité réglementaire** tant que les actes délégués ne définissent pas les exigences exactes pour chaque catégorie de produits.

## Auteur

[Thibaut Monier](https://www.linkedin.com/in/thibautmonier/) – Delivery Manager IA / Tech Lead

## Licence

Licence MIT. Voir [LICENSE](LICENSE) pour les détails.

## Liens

- [Démo](https://sylius-b2b.thibaut-monier.fr/fr_FR/plugins)
- [Règlement ESPR](https://eur-lex.europa.eu/eli/reg/2024/1781/oj?locale=fr)
- [Registre DPP](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_fr)
