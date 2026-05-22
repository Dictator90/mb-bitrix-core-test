# Public API

The package exposes a static facade interface for simplicity.

## `MB\BitrixTest\Bootstrap\PrologBootstrap`

The primary class to bootstrap the environment.

- `PrologBootstrap::boot(array $options = []): void`
  Starts the bootstrap sequence.
- `PrologBootstrap::reset(): void`
  Resets the internal booted flag state.

## `MB\BitrixTest\Bootstrap\EpilogBootstrap`

- `EpilogBootstrap::shutdown(): void`
  Closes database connections and cleans up temporary file descriptors.

## `MB\BitrixTest\Install\InstalledCore`

- `InstalledCore::path(): string`
  Locates the installed Bitrix core root path.
- `InstalledCore::readSmVersion(string $installDir): ?string`
  Extracts the `SM_VERSION` from core files.

## `@internal` Notice

All classes inside the `MB\BitrixTest\Internal` namespace are internal and subject to change without minor release notice. Avoid using them directly.
