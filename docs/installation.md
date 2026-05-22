# Installation

This guide explains how to install and configure `mb4it/bitrix-core-test` in your project.

## Requirements

- **PHP**: 8.2 or higher
- **Extensions**:
  - `ext-json`
  - `ext-pdo`
  - `ext-pdo_sqlite`
  - `ext-zip`
  - `ext-mbstring`
- **1C-Bitrix**: 22.0 or higher

## Step-by-step Installation

1. Add the package to your development dependencies:

```bash
composer require --dev mb4it/bitrix-core-test
```

2. Configure environment variables for download source (optional, defaults to `download` with `business` edition):

```bash
# Unix/macOS
export BITRIX_CORE_SOURCE=download
export BITRIX_CORE_EDITION=business

# Windows (PowerShell)
$env:BITRIX_CORE_SOURCE="download"
$env:BITRIX_CORE_EDITION="business"
```

3. Run composer install to trigger the core installation command:

```bash
composer install
```
