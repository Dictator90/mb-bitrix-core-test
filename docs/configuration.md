# Configuration Options

`mb4it/bitrix-core-test` is configured using environment variables or composer.json parameters.

## Composer Configuration

You can configure installation settings in the consumer `composer.json` under `extra.bitrix-core-test`:

```json
{
  "extra": {
    "bitrix-core-test": {
      "source": "download",
      "edition": "business",
      "version_policy": "warn",
      "install_path": "bitrix",
      "force": false,
      "apply_filter": true
    }
  }
}
```

### Config Options

- `source`: Core source strategy (`download`, `bundled`, `local`, `skip`).
- `edition`: The Bitrix edition to download (e.g. `business`, `standard`).
- `version`: Specifically requested version.
- `version_policy`: How to handle version discrepancies (`strict`, `warn`, `ignore`).
- `install_path`: Relative directory to install the core files.
- `force`: Forces reinstall even if up to date.
- `apply_filter`: Filter useless directories (like backups, heavy files, assets) from installation.

## Environment Variables

Environment variables override composer options:

- `BITRIX_CORE_SOURCE`
- `BITRIX_CORE_EDITION`
- `BITRIX_CORE_VERSION`
- `BITRIX_CORE_VERSION_POLICY`
- `BITRIX_CORE_FORCE`
- `BITRIX_CORE_PATH`
- `BITRIX_CORE_DOWNLOAD_URL`
- `BITRIX_BOOTSTRAP_MODE` (`minimal` | `full`)
- `BITRIX_USE_SQLITE` (`1` | `0`)
- `BITRIX_SQLITE_PATH`
- `BITRIX_SQLITE_MODE` (`base` | `shop`)
- `BITRIX_SQLITE_EXTRA_SQL`
- `BITRIX_SQLITE_IMPORT_CORE_INSTALL_SQL` (`1` | `0`)
- `BITRIX_SQLITE_IMPORT_CORE_SHOP_DEMO_SQL` (`1` | `0`)
- `BITRIX_IMPORT_ESHOP_DEMO_XML` (`1` | `0`)
