<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Domain;

use DateTimeImmutable;

/**
 * Data Transfer Object representing a single sticky note group entity mapped to the database structure.
 */
final readonly class Group
{
    /**
     * Dependency Injection via Constructor Property Promotion (PHP 8.3 standard).
     */
    public function __construct(
        public ?int $id,
        public string $slug,
        public string $title,
        public string $allowedRoles,
        public int $sortOrder,
        public DateTimeImmutable $createdAt,
    ) {}
}