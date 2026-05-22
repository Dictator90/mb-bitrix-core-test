# Bootstrap Lifecycle

Understanding the runtime lifecycle of the bootstrapper.

## Lifecycle Phases

When `PrologBootstrap::boot($options)` is called:

1. **Option Resolving**:
   Merged environment variables and programmatic options parameters are resolved.

2. **Mode Selection**:
   - `minimal`: Directly loads basic D7 class files.
   - `full`: Prepares runtime docroot, settings, stubs, and full Bitrix execution environment.

3. **Runtime Docroot Preparation**:
   A temporary docroot workspace is built under `.runtime/integration`. Symmetric links are generated linking to the core modules.

4. **Settings Generation**:
   Generates `local/.settings.php` and `local/php_interface/dbconn.php` dynamically matching database configs (SQLite/MySQL).

5. **Database Initialization**:
   Executes necessary SQL schema files and initializes the SQLite/MySQL engine with seed data.

6. **Global Variables & Prolog Startup**:
   Initializes global variables (`$APPLICATION`, `$USER`, `$_SERVER`), requires the core `start.php`, and runs `CMain::PrologActions()`.

7. **Shutdown / Epilog**:
   At the end of testing, `EpilogBootstrap::shutdown()` ensures cleanup of open files, database locks, and temporary directories.
