<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Model\Notice;

/**
 * Domain entity model representing a centralized administration notice record.
 */
final readonly class Notice
{
    /**
     * @param array<int, string> $allowedRoles
     */
    public function __construct(
        public ?int $id,
        public string $title,
        public string $message,
        public string $status,
        public string $priority,
        public int $authorId,
        public array $allowedRoles,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {}
}