<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Domain;

/**
 * Data Transfer Object representing a single sticky note entity aligned with the schema layout.
 */
final readonly class Notice
{
    /**
     * Dependency Injection via Constructor Property Promotion (PHP 8.3 standard).
     *
     * @param int|null $id
     * @param string $groupId
     * @param int $userId
     * @param int $targetUserId
     * @param string $title
     * @param string $message
     * @param string $status
     * @param string $priority
     * @param \DateTimeImmutable|null $startDate
     * @param \DateTimeImmutable|null $endDate
     * @param \DateTimeImmutable $createdAt
     * @param \DateTimeImmutable $updatedAt
     */
    public function __construct(
        public ?int $id,
        public string $groupId,
        public int $userId,
        public int $targetUserId,
        public string $title,
        public string $message,
        public string $status,
        public string $priority,
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $endDate,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {}
}