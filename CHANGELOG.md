# Changelog

Toutes les évolutions notables de ce projet sont documentées dans ce fichier.

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/)
et le projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [Unreleased]

## [1.0.0] - 2026-05-31

### Added

- Value object immuable `Kissous\Uuid7\Uuid7` (UUIDv7, RFC 9562).
- `Uuid7::generate()` : génération time-ordered avec aléa crypto-secure (`random_bytes`).
- `Uuid7::fromString()` : parsing depuis la forme canonique, insensible à la casse,
  avec validation stricte de la version (7) et du variant (RFC 9562).
- `Uuid7::toString()` et support de `Stringable`.
- `Kissous\Uuid7\Exception\InvalidUuidException` pour les entrées invalides.

[Unreleased]: https://github.com/Kissous/uuid7/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/Kissous/uuid7/releases/tag/v1.0.0
