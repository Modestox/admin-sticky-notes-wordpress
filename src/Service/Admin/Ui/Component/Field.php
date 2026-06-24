<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Service\Admin\Ui\Component;

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
    ) {}

    public static function text(string $id, string $label, bool $isRequired = false): self
    {
        return new self($id, $label, 'text', $isRequired);
    }

    public static function textarea(string $id, string $label, bool $isRequired = false): self
    {
        return new self($id, $label, 'textarea', $isRequired);
    }

    /**
     * @param array<int, FieldOption> $options
     */
    public static function select(string $id, string $label, array $options, bool $isRequired = false): self
    {
        return new self($id, $label, 'select', $isRequired, $options);
    }

    /**
     * Factory method to instantiate a strict numerical input component.
     */
    public static function number(string $id, string $label, bool $required = false): self
    {
        return new self($id, $label, 'number', $required);
    }

    /**
     * Factory method to instantiate a strict datetime input component.
     */
    public static function datetime(string $id, string $label, bool $required = false): self
    {
        return new self($id, $label, 'datetime', $required);
    }

    /**
     * Factory method to instantiate a multiple selection component.
     */
    public static function multiselect(string $id, string $label, array $options, bool $isRequired = false): self
    {
        return new self($id, $label, 'multiselect', $isRequired, $options);
    }
}