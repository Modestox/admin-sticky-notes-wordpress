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
 * Baseline declarative architecture for administrative form schema blueprints.
 */
abstract readonly class AbstractForm
{
    /**
     * Compiles and returns ready-to-render configuration fields dataset map.
     *
     * @param array<int, string> ...$context Dynamic lookup arrays passed from the action controller.
     * @return array<int, Field> Collection of strictly typed form field components.
     */
    abstract public function getFields(array ...$context): array;
}