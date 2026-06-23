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
        public array $options = []
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
}