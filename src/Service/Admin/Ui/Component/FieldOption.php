<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Service\Admin\Ui\Component;

/**
 * Data value object representing a select option element boundary.
 */
final readonly class FieldOption
{
    public function __construct(
        public string $value,
        public string $label
    ) {}
}