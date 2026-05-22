# Testing Strategy

Guidelines on how to write tests with this package.

## Test Separation

1. **Unit tests**:
   Fast tests that mock dependencies and run *without* booting the Bitrix environment.
2. **Integration tests**:
   Bootstrapped tests that require access to database query executing, D7 ORM, or Loader module includes.

## Running Tests in Isolation

Since Bitrix uses global variables and constants (`$_SERVER`, constants like `SITE_ID`), we recommend configuring PHPUnit to run tests in isolated processes when testing bootstrap lifecycle phases:

```xml
<phpunit runTestsInSeparateProcesses="true">
```

Or mark test methods with `@runInSeparateProcess`.
