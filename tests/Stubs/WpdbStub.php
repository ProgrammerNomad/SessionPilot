<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Tests\Stubs;

/**
 * Minimal wpdb stub for unit testing without a real database.
 * All methods are overridable via closures assigned to public properties.
 */
class WpdbStub
{
    public string $prefix = 'wp_';

    /** @var callable|null Override get_var behaviour */
    public $getVarCallback = null;

    /** @var callable|null Override insert behaviour */
    public $insertCallback = null;

    /** @var callable|null Override update behaviour */
    public $updateCallback = null;

    /** @var callable|null Override get_row behaviour */
    public $getRowCallback = null;

    /** @var callable|null Override prepare behaviour */
    public $prepareCallback = null;

    public function get_var(string $sql, int $column = 0, int $row = 0): ?string
    {
        if ($this->getVarCallback !== null) {
            return ($this->getVarCallback)($sql, $column, $row);
        }
        return null;
    }

    public function insert(string $table, array $data, ?array $format = null): int|false
    {
        if ($this->insertCallback !== null) {
            return ($this->insertCallback)($table, $data, $format);
        }
        return 1;
    }

    public function update(string $table, array $data, array $where, ?array $format = null, ?array $where_format = null): int|false
    {
        if ($this->updateCallback !== null) {
            return ($this->updateCallback)($table, $data, $where, $format, $where_format);
        }
        return 1;
    }

    public function get_row(string $sql, string $output = 'OBJECT', int $row = 0): object|array|null
    {
        if ($this->getRowCallback !== null) {
            return ($this->getRowCallback)($sql, $output, $row);
        }
        return null;
    }

    public function prepare(string $sql, mixed ...$args): string
    {
        if ($this->prepareCallback !== null) {
            return ($this->prepareCallback)($sql, ...$args);
        }
        return $sql;
    }

    public function get_results(string $sql, string $output = 'OBJECT'): array
    {
        return [];
    }
}
