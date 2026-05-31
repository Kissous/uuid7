# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.1.0] - 2026-05-31

### Added

- Same-millisecond monotonicity: UUIDs generated in the same process are now
  strictly increasing, even within a single millisecond (RFC 9562 "monotonic
  random" method). No public API change.

## [1.0.1] - 2026-05-31

### Changed

- Translated user-facing documentation (README, roadmap), code comments and the
  `composer.json` description to English.
- `InvalidUuidException` message is now in English.

## [1.0.0] - 2026-05-31

### Added

- Immutable value object `Kissous\Uuid7\Uuid7` (UUIDv7, RFC 9562).
- `Uuid7::generate()`: time-ordered generation with crypto-secure randomness (`random_bytes`).
- `Uuid7::fromString()`: parsing from the canonical form, case-insensitive, with
  strict validation of the version (7) and variant (RFC 9562).
- `Uuid7::toString()` and `Stringable` support.
- `Kissous\Uuid7\Exception\InvalidUuidException` for invalid input.

[Unreleased]: https://github.com/Kissous/uuid7/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/Kissous/uuid7/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/Kissous/uuid7/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/Kissous/uuid7/releases/tag/v1.0.0
