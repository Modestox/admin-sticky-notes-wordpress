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
 * Immutable data value object representing a single grid column definition.
 */
final readonly class Column
{
    /**
     * Property promotion constructor aligning strict types for PHP 8.3 named arguments.
     */
    public function __construct(
        public string $id,
        public string $label,
        public bool $isSortable = false,
        public string $type = 'text',
    ) {}
}