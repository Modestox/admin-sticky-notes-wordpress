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
 * Standard structural contract for mapping raw database array rows into domain entity object instances.
 *
 * @template TEntity of object
 */
interface HydratorInterface
{
    /**
     * Transforms a flat database row array layout into a fully populated Domain DTO instance.
     *
     * @param array<string, mixed> $data Raw associated database column key-value data pairs.
     * @return TEntity Fully loaded and type-compliant functional Domain model descriptor instance.
     */
    public function hydrate(array $data): object;

    /**
     * Converts a clean Domain DTO instance back into a flat database array layout structure.
     *
     * @param TEntity $entity Targeted operational domain model descriptor instance.
     * @return array<string, mixed> Extracted database mapped column key-value data parameters.
     */
    public function extract(object $entity): array;
}