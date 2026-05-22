# SQLite Database Driver

SQLite is the main database engine for testing, providing fast in-memory or single-file test instances.

## Driver Registration

The integration registers `MB\BitrixTest\Database\SqliteConnection` as the database driver in Bitrix connection pool settings.

## regex and sha1 functions

Since SQLite lacks native `REGEXP` and `SHA1` functions (which Bitrix SQL helper uses), our adapter registers custom PDO functions on connections:
- `regexp($pattern, $value)`
- `sha1($value)`

## Pragma and Table Info

It handles pragma commands (e.g. `PRAGMA table_info` and `PRAGMA index_list`) to make ORM field discovery work out of the box in Bitrix D7.
