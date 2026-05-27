# Changelog

## [Unreleased]

- Add legacy `CDBResult` SQLite driver stub so the Bitrix prolog loads under the `sqlite` connection type; `BitrixStubInstaller` now installs both `database.php` and `dbresult.php`.
- Fix `b_option` schema (PRIMARY KEY `MODULE_ID, NAME` + nullable `SITE_ID`) and add `b_option_site` / `b_user_option` tables to the SQLite dumps.
- Remove obsolete `sqlite-minimal.sql` and the `dump-sql-chunks` dev script.
- Document custom SQL dumps for SQLite (extra SQL) and MySQL (external DB) — see `recipes/08-custom-sql-dump.md`.
- Initial package: CoreInstaller (download / bundled / local), bootstrap prolog/epilog, SQLite minimal schema, PHPUnit smoke tests.
- SQLite D7 driver copied into package (`MB\BitrixTest\Database\Sqlite*`), no dependency on `mb4it/bitrix-support`.
- Official editions: `start`, `standard`, `small_business`, `business` (1c-bitrix.ru encode tar.gz).
