<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Model\Notice;

/**
 * Value object encapsulating strict search criteria and filtering bounds for Notices.
 */
final readonly class NoticeCriteria
{
    /**
     * Dependency Injection via Constructor Property Promotion (PHP 8.3 standard).
     *
     * @param int|null $groupId
     * @param int|null $targetUserId
     * @param array<int, string>|null $statuses
     * @param array<int, string>|null $priorities
     * @param \DateTimeImmutable|null $activeAt
     * @param string $orderBy
     * @param string $direction
     * @param int $limit
     * @param int $offset
     */
    public function __construct(
        public ?int $groupId = null,
        public ?int $targetUserId = null,
        public ?array $statuses = null,
        public ?array $priorities = null,
        public ?\DateTimeImmutable $activeAt = null,
        public string $orderBy = 'id',
        public string $direction = 'DESC',
        public int $limit = 20,
        public int $offset = 0
    ) {}
}