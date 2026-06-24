<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Ui\Component;

/**
 * Metadata descriptor defining an isolated grid column behavior and rendering type.
 */
final readonly class Column
{
    private function __construct(
        public string $id,
        public string $label,
        public string $type,
        public bool $isSortable = false
    ) {}

    public static function text(string $id, string $label, bool $isSortable = false): self
    {
        return new self($id, $label, 'text', $isSortable);
    }

    public static function badge(string $id, string $label, bool $isSortable = false): self
    {
        return new self($id, $label, 'badge', $isSortable);
    }

    public static function datetime(string $id, string $label, bool $isSortable = false): self
    {
        return new self($id, $label, 'datetime', $isSortable);
    }
}