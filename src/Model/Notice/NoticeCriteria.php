<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Model\Notice;

/**
 * Value object encapsulating strict search criteria and filtering bounds for Notices.
 */
final class NoticeCriteria
{
    /**
     * @param array<int, string>|null $statuses
     * @param array<int, string>|null $priorities
     */
    public function __construct(
        public ?int $groupId = null,
        public ?int $targetUserId = null,
        public ?array $statuses = null,
        public ?array $priorities = null,
        public ?\DateTimeInterface $activeAt = null,
        public string $orderBy = 'id',
        public string $direction = 'DESC',
        public int $limit = 20,
        public int $offset = 0
    ) {}
}