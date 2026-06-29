<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Dashboard\Admin\Data;

/**
 * Read-only data transfer object representing a tailored group boundary inside the admin dashboard.
 */
final readonly class DashboardGroup
{
    /**
     * Replicates the baseline domain model structure for unified view template rendering.
     */
    public function __construct(
        public int $id,
        public string $title,
    ) {}
}