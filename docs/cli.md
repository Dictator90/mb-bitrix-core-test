# CLI Interface

The package provides a command-line tool `bitrix-core-test` to manage the testing environment.

## Execution

The binary is located in `vendor/bin`:

```bash
vendor/bin/bitrix-core-test <command> [options]
```

## Available Commands

### 1. `install`

Downloads, extracts, and configures the Bitrix Core for tests.

```bash
vendor/bin/bitrix-core-test install
```

### 2. `doctor`

Runs environment diagnostics (PHP versions, extensions, write permissions, configs).

```bash
vendor/bin/bitrix-core-test doctor
```

### 3. `show-config`

Outputs resolved installation configurations and environment variables.

```bash
vendor/bin/bitrix-core-test show-config
```

### 4. `generate-sqlite-dumps`

Generates SQLite DB dumps based on core SQL schema files.

```bash
vendor/bin/bitrix-core-test generate-sqlite-dumps
```
