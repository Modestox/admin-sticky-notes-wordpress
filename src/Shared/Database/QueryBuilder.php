<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Database;

/**
 * Lightweight fluent SQL query constructor designed for safe WordPress database interactions.
 */
final class QueryBuilder
{
    /** @var array<int, string> */
    private array $whereClauses = [];

    /** @var array<int, mixed> */
    private array $parameters = [];

    private string $orderBy = '';
    private string $direction = 'DESC';
    private ?int $limit = null;
    private ?int $offset = null;

    /**
     * Initializes a new query builder instance targeted at a specific catalog table.
     */
    public function __construct(
        private readonly string $tableName
    ) {}

    /**
     * Adds an exact equality condition constraint to the query structure.
     */
    public function equal(string $column, mixed $value, string $format = '%s'): self
    {
        if ($value === null || $value === '') {
            return $this;
        }

        $this->whereClauses[] = sprintf('`%s` = %s', $column, $format);
        $this->parameters[] = $value;

        return $this;
    }

    /**
     * Appends a partial text matching string constraint using SQL LIKE syntax.
     */
    public function like(string $column, ?string $value): self
    {
        global $wpdb;

        if ($value === null || $value === '') {
            return $this;
        }

        $this->whereClauses[] = sprintf('`%s` LIKE %%s', $column);
        $this->parameters[] = '%' . $wpdb->esc_like($value) . '%';

        return $this;
    }

    /**
     * Injects a highly precise serialized array substring pattern constraint targeting exact value matches.
     */
    public function likeSerializedId(string $column, ?int $value): self
    {
        global $wpdb;

        if ($value === null) {
            return $this;
        }

        // We explicitly look for serialized integer values "i:VALUE;" or string values 's:LENGTH:"VALUE";'
        $intPattern = '%i:' . $wpdb->esc_like((string)$value) . ';%';
        $strPattern = '%s:%:"' . $wpdb->esc_like((string)$value) . '";%';

        $this->whereClauses[] = sprintf('(`%s` LIKE %%s OR `%s` LIKE %%s)', $column, $column);
        $this->parameters[] = $intPattern;
        $this->parameters[] = $strPattern;

        return $this;
    }

    /**
     * Constructs a composite logical OR substring search boundary block across multiple columns.
     */
    public function generalSearch(array $columns, ?string $value): self
    {
        global $wpdb;

        if ($value === null || $value === '') {
            return $this;
        }

        $escapedValue = '%' . $wpdb->esc_like($value) . '%';
        $clauses = [];

        foreach ($columns as $column) {
            $clauses[] = sprintf('`%s` LIKE %%s', $column);
            $this->parameters[] = $escapedValue;
        }

        if (!empty($clauses)) {
            $this->whereClauses[] = '(' . implode(' OR ', $clauses) . ')';
        }

        return $this;
    }

    /**
     * Implements an inclusive set belonging array constraint check via SQL IN operator.
     *
     * @param array<int, mixed> $values
     */
    public function in(string $column, array $values, string $format = '%s'): self
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = implode(', ', array_fill(0, count($values), $format));
        $this->whereClauses[] = sprintf('`%s` IN (%s)', $column, $placeholders);

        foreach ($values as $value) {
            $this->parameters[] = $value;
        }

        return $this;
    }

    /**
     * Establishes targeted system sorting configuration attributes.
     */
    public function order(string $column, string $direction = 'DESC', array $allowedColumns = []): self
    {
        if (!empty($allowedColumns) && !in_array($column, $allowedColumns, true)) {
            return $this;
        }

        $this->orderBy = $column;
        $this->direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $this;
    }

    /**
     * Applies standard pagination numeric limit constraints boundaries.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Injects standard pagination skip offset numeric values indices.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Compiles complete prepared safe SQL query string for structural row extraction.
     */
    public function getSelectSql(): string
    {
        global $wpdb;

        $where = !empty($this->whereClauses) ? implode(' AND ', $this->whereClauses) : '1=1';
        $sql = "SELECT * FROM {$this->tableName} WHERE {$where}";

        if (!empty($this->orderBy)) {
            $sql .= sprintf(' ORDER BY `%s` %s', $this->orderBy, $this->direction);
        }

        if ($this->limit !== null) {
            $sql .= sprintf(' LIMIT %d', $this->limit);
        }

        if ($this->offset !== null) {
            $sql .= sprintf(' OFFSET %d', $this->offset);
        }

        if (empty($this->parameters)) {
            return $sql;
        }

        return $wpdb->prepare($sql, ...$this->parameters);
    }

    /**
     * Compiles highly lightweight safe total computational statement string.
     */
    public function getCountSql(): string
    {
        global $wpdb;

        $where = !empty($this->whereClauses) ? implode(' AND ', $this->whereClauses) : '1=1';
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$where}";

        if (empty($this->parameters)) {
            return $sql;
        }

        return $wpdb->prepare($sql, ...$this->parameters);
    }
}