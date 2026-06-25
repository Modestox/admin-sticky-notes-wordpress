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
 * Metadata descriptor defining form input parameters and behavioral validation rules.
 */
final readonly class Field
{
    /**
     * @param array<int, FieldOption> $options
     */
    private function __construct(
        public string $id,
        public string $label,
        public string $type,
        public bool $isRequired = false,
        public array $options = [],
        public array $customMeta = [],
    ) {}

    public static function text(string $id, string $label, bool $isRequired = false): self
    {
        return new self($id, $label, 'text', $isRequired);
    }

    public static function textarea(string $id, string $label, bool $isRequired = false): self
    {
        return new self($id, $label, 'textarea', $isRequired);
    }

    public static function select(string $id, string $label, array $options, bool $isRequired = false): self
    {
        return new self($id, $label, 'select', $isRequired, $options);
    }

    public static function number(string $id, string $label, bool $required = false): self
    {
        return new self($id, $label, 'number', $required);
    }

    public static function datetime(string $id, string $label, bool $required = false): self
    {
        return new self($id, $label, 'datetime', $required);
    }

    public static function multiselect(string $id, string $label, array $options, bool $isRequired = false): self
    {
        return new self($id, $label, 'multiselect', $isRequired, $options);
    }

    public static function dynamicRows(string $id, string $label, array $columns, bool $isRequired = false): self
    {
        return new self(
            id: $id,
            label: $label,
            type: 'dynamic_rows',
            isRequired: $isRequired,
            options: [],
            customMeta: ['columns' => $columns],
        );
    }
}