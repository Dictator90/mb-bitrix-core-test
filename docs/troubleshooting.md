# Troubleshooting

Common issues and how to fix them.

## Process lock on SQLite file (Windows)

If you see `The process cannot access the file because it is being used by another process` on Windows:
- Ensure all PDO connection references are cleared (`$connection->disconnect()`).
- Avoid running multiple test processes simultaneously touching the same SQLite file.

## Missing classes or modules

If Bitrix modules are not loading:
- Verify that the core edition contains the module (e.g. `business` edition is required for `catalog`/`sale`).
- Run `vendor/bin/bitrix-core-test doctor` to check if modules are present in the installed core path.
