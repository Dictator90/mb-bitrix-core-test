# SQLite and System Limitations

This page lists known limitations of running Bitrix on SQLite.

## SQL Syntax Differences

Bitrix's query generators target MySQL or Oracle.
SQLite syntax differs in key areas:
- **Foreign Key Constraints**: Disabled by default in SQLite (can be enabled via pragma).
- **Date Functions**: functions like `NOW()`, `DATE_ADD()`, `DATE_SUB()` are not natively supported.
- **Joins**: Right outer joins and full outer joins are not supported by SQLite.
- **Locking**: SQLite uses database-wide lock for writing, meaning parallel writes from separate processes block each other.

## Global Constants

Once a PHP process defines a global constant in Bitrix (e.g. `SITE_ID` or `LANGUAGE_ID`), it cannot be redefined in the same process. This means bootstrap configuration changes require separate PHP processes.
