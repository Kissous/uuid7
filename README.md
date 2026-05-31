# uuid7

[![CI](https://github.com/Kissous/uuid7/actions/workflows/ci.yml/badge.svg)](https://github.com/Kissous/uuid7/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/kissous/uuid7)](https://packagist.org/packages/kissous/uuid7)
[![PHP Version](https://img.shields.io/packagist/php-v/kissous/uuid7)](https://packagist.org/packages/kissous/uuid7)
[![License](https://img.shields.io/packagist/l/kissous/uuid7)](LICENSE)

Bibliothèque PHP légère, **sans dépendance**, pour générer et manipuler des identifiants
time-ordered : **UUIDv7 (RFC 9562)**, avec extension **ULID** à venir.

Conçue pour la performance d'indexation en base : les UUIDv7 sont triés par le temps,
ce qui évite la fragmentation des index B-tree (contrairement aux UUIDv4 aléatoires).

## Pourquoi uuid7 ?

- **Zéro dépendance** hors `php` stdlib
- **Crypto-secure** : aléa via `random_bytes()` uniquement
- **Time-ordered** : insertions séquentielles, index B-tree compacts
- **API minuscule**, focalisée sur UUIDv7 (puis ULID)
- **PHP 8.3+** : value objects `final` `readonly` immuables
- Conforme **RFC 9562**

## Installation

```bash
composer require kissous/uuid7
```

Requiert PHP **8.3** ou supérieur.

## Quick start

```php
use Kissous\Uuid7\Uuid7;

// Générer un nouvel UUIDv7
$uuid = Uuid7::generate();
echo $uuid->toString();        // ex. 0190f3a4-7b2c-7def-8123-456789abcdef
echo $uuid;                    // identique (Stringable)

// Parser une chaîne existante (insensible à la casse, normalisée en minuscules)
$parsed = Uuid7::fromString('0190F3A4-7B2C-7DEF-8123-456789ABCDEF');

// Validation : lève InvalidUuidException si la chaîne n'est pas un UUIDv7 valide
use Kissous\Uuid7\Exception\InvalidUuidException;

try {
    Uuid7::fromString('pas-un-uuid');
} catch (InvalidUuidException $e) {
    // ...
}
```

## Roadmap

| Version | Contenu |
|---------|---------|
| **v1.0** | UUIDv7 : `generate`, `fromString`, `toString`, validation |
| v1.1 | Monotonicité dans la même milliseconde |
| v1.2 | `timestamp()`, `equals()`, `compareTo()` |
| v1.3 | `toBytes/fromBytes`, `toHex/fromHex`, `toBase32/fromBase32` |
| v1.4 | Support ULID complet + conversion UUIDv7 ↔ ULID |
| v1.5 | `Clock` / `RandomSource` injectables, `Uuid7Factory` |
| v1.6 | Intégrations `kissous/uuid7-doctrine`, `kissous/uuid7-eloquent` |

Détail par version dans [`docs/roadmap/`](docs/roadmap/).

## Développement

```bash
composer install
composer test        # PHPUnit (Unit + Property)
composer stan        # PHPStan niveau max
composer cs:check    # PHP-CS-Fixer (dry-run)
composer cs          # PHP-CS-Fixer (corrige)
composer bench       # Benchmarks vs ramsey/uuid
```

## Licence

[MIT](LICENSE) © Omar Kissous
