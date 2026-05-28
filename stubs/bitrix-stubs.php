<?php

// PHPStan stub definitions for the subset of the 1C-Bitrix core API used by this
// package. They let `composer analyse` run without the (proprietary, ~400MB)
// core downloaded into bitrix/. When the real core is present it is loaded via
// tests/phpstan-bootstrap.php; these stubs only describe signatures for analysis.

namespace Bitrix\Main {
    class Application
    {
        public static function getInstance(): self {}
        public static function getConnection(string $name = ''): \Bitrix\Main\DB\Connection {}
        public function getConnectionPool(): \Bitrix\Main\Data\ConnectionPool {}
    }

    class Loader
    {
        public static function includeModule(string $moduleName): bool {}
        public static function registerNamespace(string $namespace, string $path): void {}
    }

    class ModuleManager
    {
        /** @var array<string, mixed>|null */
        protected static $installedModules;

        public static function isModuleInstalled(string $moduleId): bool {}

        /** @return array<string, mixed> */
        public static function getInstalledModules(): array {}
    }

    class Result
    {
        public function isSuccess(): bool {}

        /** @return string[] */
        public function getErrorMessages(): array {}

        /** @return array<string, mixed> */
        public function getData(): array {}
    }

    class ArgumentException extends \Exception
    {
        public function __construct(string $message = '', string $parameter = '', ?\Throwable $previous = null) {}
    }

    class ModuleTable extends \Bitrix\Main\ORM\Data\DataManager
    {
    }
}

namespace Bitrix\Main\Config {
    class Option
    {
        /** @return mixed */
        public static function get(string $moduleId, string $name, mixed $default = '', mixed $siteId = false) {}
        public static function set(string $moduleId, string $name, mixed $value = '', string $siteId = ''): void {}
    }
}

namespace Bitrix\Main\Type {
    class DateTime
    {
        public function __construct(mixed $value = null, ?string $format = null) {}
        public function __toString(): string {}
    }

    class Date
    {
        public function __construct(mixed $value = null, ?string $format = null) {}
        public function __toString(): string {}
    }
}

namespace Bitrix\Main\Diag {
    class SqlTrackerQuery
    {
        public function startQuery(mixed $sql = '', mixed $binds = null): void {}
        public function finishQuery(): void {}
        public function setNode(mixed $node): void {}
    }

    class SqlTracker
    {
        public function getNewTrackerQuery(): SqlTrackerQuery {}
    }
}

namespace Bitrix\Main\Data {
    class ConnectionPool
    {
    }
}

namespace Bitrix\Main\DB {
    class Connection
    {
        public const INDEX_UNIQUE = 'UNIQUE';
        public const INDEX_FULLTEXT = 'FULLTEXT';
        public const INDEX_SPATIAL = 'SPATIAL';

        protected bool $isConnected = false;
        protected string $database = '';
        protected mixed $resource = null;
        protected bool $trackSql = false;
        protected \Bitrix\Main\Diag\SqlTracker $sqlTracker;
        protected ?string $version = null;
        protected ?string $versionExpress = null;
        protected bool $queryExecutingEnabled = true;
        /** @var array<int, string>|null */
        protected ?array $disabledQueryExecutingDump = null;
        /** @var array<string, array<string, \Bitrix\Main\ORM\Fields\ScalarField>> */
        protected array $tableColumnsCache = [];

        /** @param array<string, mixed> $configuration */
        public function __construct(array $configuration = []) {}

        public function isPersistent(): bool {}
        public function isConnected(): bool {}
        protected function afterConnected(): void {}
        public function getSqlHelper(): SqlHelper {}

        /**
         * @param array<int, mixed> $args
         * @return array<int, mixed>
         */
        protected static function parseQueryFunctionArgs(array $args): array {}

        protected static function findIndex(array $indexes, array $columns, mixed $strict = false): ?string {}

        /** @return mixed */
        public function getNodeId() {}

        /**
         * @param array<string, mixed>|null $binds
         * @return mixed
         */
        public function queryScalar(mixed $sql = '', ?array $binds = null) {}

        /** @return mixed */
        public function query(mixed $sql) {}

        public function getType(): string {}

        /** @return array<string, \Bitrix\Main\ORM\Fields\ScalarField> */
        public function getTableFields(mixed $tableName): array {}
    }

    class Result
    {
        public function __construct(mixed $result = null, ?Connection $dbConnection = null, ?\Bitrix\Main\Diag\SqlTrackerQuery $trackerQuery = null) {}

        /** @return mixed */
        public function fetch() {}

        /** @return array<int, array<string, mixed>> */
        public function fetchAll(): array {}

        public function getSelectedRowsCount(): int {}
    }

    class SqlHelper
    {
        protected Connection $connection;

        public function __construct(Connection $connection) {}

        public function getFieldByColumnType(mixed $name, mixed $type, ?array $parameters = null): \Bitrix\Main\ORM\Fields\ScalarField {}
        public function quote(mixed $identifier): string {}
        public function forSql(mixed $value, mixed $maxLength = 0): string {}
        public function getTopSql(mixed $sql, mixed $limit, mixed $offset = 0): string {}
        public function getColumnTypeByField(\Bitrix\Main\ORM\Fields\ScalarField $field): string {}
        public function convertToDb(mixed $value, mixed $field = null): string {}

        /**
         * @param array<string, mixed> $fields
         * @return mixed
         */
        public function prepareInsert(mixed $tableName, array $fields, mixed $returnAsArray = false) {}

        /**
         * @param array<string, mixed> $fields
         * @return mixed
         */
        public function prepareUpdate(mixed $tableName, array $fields) {}

        /** @return mixed */
        public function getConverter(\Bitrix\Main\ORM\Fields\ScalarField $field) {}
    }

    class ConnectionException extends \Exception
    {
        public function __construct(string $message = '', string $databaseMessage = '', ?\Throwable $previous = null) {}
    }

    class SqlQueryException extends \Exception
    {
        public function __construct(string $message = '', string $databaseMessage = '', mixed $query = '') {}
    }

    class DuplicateEntryException extends SqlQueryException
    {
    }

    class TransactionException extends \Exception
    {
        public function __construct(string $message = '', string $databaseMessage = '') {}
    }

    class MysqliConnection extends Connection
    {
    }

    class PgsqlConnection extends Connection
    {
    }

    class MysqliSqlHelper extends SqlHelper
    {
    }

    class PgsqlSqlHelper extends SqlHelper
    {
    }
}

namespace Bitrix\Main\ORM\Fields {
    class ScalarField
    {
        /** @param array<string, mixed> $parameters */
        public function __construct(mixed $name = '', array $parameters = []) {}

        public function getName(): string {}
        public function getColumnName(): string {}
        public function isNullable(): bool {}
        public function setConnection(mixed $connection): self {}

        /** @return array<int|string, string> */
        public function getValues(): array {}

        /** @return array<int, mixed> */
        public function getValidators(): array {}
    }

    class IntegerField extends ScalarField
    {
        public function getSize(): int {}

        /** @return $this */
        public function configureSize(mixed $size): static {}
    }

    class FloatField extends ScalarField
    {
    }

    class StringField extends ScalarField
    {
    }

    class TextField extends ScalarField
    {
    }

    class BooleanField extends ScalarField
    {
    }

    class EnumField extends ScalarField
    {
    }

    class DatetimeField extends ScalarField
    {
    }

    class DateField extends ScalarField
    {
    }

    class DecimalField extends ScalarField
    {
        public function getPrecision(): int {}

        public function getScale(): int {}
    }
}

namespace Bitrix\Main\ORM\Fields\Validators {
    class LengthValidator
    {
        public function getMax(): int {}
    }
}

namespace Bitrix\Main\ORM\Data {
    class AddResult extends \Bitrix\Main\Result
    {
        /** @return int|string */
        public function getId() {}
    }

    class UpdateResult extends \Bitrix\Main\Result
    {
        /** @return int|string */
        public function getId() {}
    }

    class DeleteResult extends \Bitrix\Main\Result
    {
    }

    class DataManager
    {
        public static function query(): \Bitrix\Main\ORM\Query\Query {}

        /** @param array<string, mixed> $parameters */
        public static function getList(array $parameters = []): \Bitrix\Main\ORM\Query\Result {}

        /** @param array<string, mixed> $data */
        public static function add(array $data): AddResult {}

        /** @param array<string, mixed> $data */
        public static function update(mixed $primary, array $data): UpdateResult {}

        public static function delete(mixed $primary): DeleteResult {}

        /** @param array<string, mixed> $parameters */
        public static function getByPrimary(mixed $primary, array $parameters = []): \Bitrix\Main\ORM\Query\Result {}

        /** @return mixed */
        public static function getEntity() {}

        public static function getTableName(): string {}
    }
}

namespace Bitrix\Main\ORM\Query {
    class Query
    {
        /**
         * @param string[] $select
         * @return $this
         */
        public function setSelect(array $select): static {}

        /**
         * @param array<string, mixed> $filter
         * @return $this
         */
        public function setFilter(array $filter): static {}

        /**
         * @param array<string, mixed> $order
         * @return $this
         */
        public function setOrder(array $order): static {}

        /** @return $this */
        public function setLimit(mixed $limit): static {}

        /** @return $this */
        public function where(mixed ...$args): static {}

        public function exec(): Result {}

        /** @return mixed */
        public function fetch() {}

        /** @return array<int, array<string, mixed>> */
        public function fetchAll(): array {}

        public function fetchObject(): ?\Bitrix\Main\ORM\Objectify\EntityObject {}
        public function fetchCollection(): \Bitrix\Main\ORM\Objectify\Collection {}
    }

    class Result extends \Bitrix\Main\DB\Result
    {
        public function fetchObject(): ?\Bitrix\Main\ORM\Objectify\EntityObject {}
        public function fetchCollection(): \Bitrix\Main\ORM\Objectify\Collection {}
    }
}

namespace Bitrix\Main\ORM\Objectify {
    class EntityObject
    {
        /**
         * Bitrix EntityObject exposes per-field accessors (getId(), getCode(), …)
         * via __call; PHPStan does not infer those from a reflection-loaded stub,
         * so the accessors used by the test-suite are declared explicitly.
         *
         * @param array<int, mixed> $arguments
         * @return mixed
         */
        public function __call(string $name, array $arguments) {}

        /** @return mixed */
        public function __get(string $name) {}

        /** @return mixed */
        public function getId() {}

        /** @return mixed */
        public function getCode() {}

        /** @return mixed */
        public function getName() {}
    }

    /**
     * @implements \IteratorAggregate<int, EntityObject>
     */
    class Collection implements \IteratorAggregate, \Countable
    {
        public function getIterator(): \Iterator {}
        public function count(): int {}

        /**
         * @param array<int, mixed> $arguments
         * @return mixed
         */
        public function __call(string $name, array $arguments) {}
    }
}

namespace Bitrix\Iblock {
    class IblockTable extends \Bitrix\Main\ORM\Data\DataManager
    {
    }

    class ElementTable extends \Bitrix\Main\ORM\Data\DataManager
    {
    }

    class SectionTable extends \Bitrix\Main\ORM\Data\DataManager
    {
    }
}
