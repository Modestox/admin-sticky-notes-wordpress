<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Ui\Component;

/**
 * Baseline declarative configuration architecture for admin data tables.
 */
abstract readonly class AbstractGrid
{
    /**
     * Cached list of resolved column definitions.
     *
     * @var array<int, Column>
     */
    private array $columns;

    /**
     * Initializes the grid layout configuration schema.
     */
    public function __construct()
    {
        $this->columns = $this->defineColumns();
    }

    /**
     * Returns compiled structural column blueprint definitions schema.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns localized main tabular view screen title heading.
     */
    abstract public function getTitle(): string;

    /**
     * Declaratively configures the collection layout of structural column models.
     *
     * @return array<int, Column>
     */
    abstract protected function defineColumns(): array;
}