<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Service\Admin\Ui\Component;

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