<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Repository;

use Modestox\AdminStickyNotes\Shared\Database\AbstractRepository;
use Modestox\AdminStickyNotes\Shared\Database\QueryBuilder;
use Modestox\AdminStickyNotes\Notice\Domain\Notice;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

/**
 * Handles persistent database operations for Administrative Notices.
 *
 * @extends AbstractRepository<Notice>
 */
final readonly class NoticeRepository extends AbstractRepository
{
    /**
     * Dependency injection handled via constructor property promotion extending baseline configuration.
     */
    public function __construct(
        private DateFactory $dateFactory,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getTableNameKeyword(): string
    {
        return 'modestox_sticky_notes';
    }

    /**
     * Fetches notices with strict sorting, pagination, and structural filtering boundaries.
     *
     * @param string $orderBy Validated database column name.
     * @param string $direction Sort order direction (ASC|DESC).
     * @param int $limit Maximum number of records to return.
     * @param int $offset Number of records to skip.
     * @param array{status?: string, priority?: string, group?: string, search?: string} $filters SQL constraints filters.
     * @return array<int, Notice>
     */
    public function findAll(string $orderBy = 'id', string $direction = 'DESC', int $limit = 20, int $offset = 0, array $filters = []): array
    {
        global $wpdb;

        $allowedColumns = ['id', 'title', 'status', 'priority', 'start_date', 'end_date'];
        $groupFilter = isset($filters['group']) ? (int)$filters['group'] : null;

        $query = (new QueryBuilder($this->tableName))
            ->equal('status', $filters['status'] ?? null)
            ->equal('priority', $filters['priority'] ?? null);

        if ($groupFilter === 0) {
            $query->equal('group_id', '0');
        } elseif ($groupFilter > 0) {
            $query->likeSerializedId('group_id', $groupFilter);
        }

        $query->generalSearch(['title', 'content'], $filters['search'] ?? null)
            ->order($orderBy, $direction, $allowedColumns)
            ->limit($limit)
            ->offset($offset);

        $rows = $wpdb->get_results($query->getSelectSql(), ARRAY_A);

        if (!is_array($rows)) {
            return [];
        }

        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * Returns the total count of filtered rows inside the database table.
     *
     * @param array{status?: string, priority?: string, group?: string, search?: string} $filters SQL constraints filters.
     * @return int
     */
    public function countAll(array $filters = []): int
    {
        global $wpdb;

        $groupFilter = isset($filters['group']) ? (int)$filters['group'] : null;

        $query = (new QueryBuilder($this->tableName))
            ->equal('status', $filters['status'] ?? null)
            ->equal('priority', $filters['priority'] ?? null);

        if ($groupFilter === 0) {
            $query->equal('group_id', '0');
        } elseif ($groupFilter > 0) {
            $query->likeSerializedId('group_id', $groupFilter);
        }

        $query->generalSearch(['title', 'content'], $filters['search'] ?? null);

        return (int)$wpdb->get_var($query->getCountSql());
    }

    /**
     * @inheritDoc
     * @return Notice
     */
    public function hydrate(array $data): Notice
    {
        return new Notice(
            id: isset($data['id']) ? (int)$data['id'] : null,
            groupId: (string)($data['group_id'] ?? '0'),
            userId: (int)($data['user_id'] ?? 0),
            targetUserId: (int)($data['target_user_id'] ?? 0),
            title: (string)($data['title'] ?? ''),
            message: (string)($data['content'] ?? ''),
            status: (string)($data['status'] ?? 'draft'),
            priority: (string)($data['priority'] ?? 'normal'),
            startDate: !empty($data['start_date']) ? $this->dateFactory->create($data['start_date']) : null,
            endDate: !empty($data['end_date']) ? $this->dateFactory->create($data['end_date']) : null,
            createdAt: $this->dateFactory->create($data['created_at'] ?? 'now'),
            updatedAt: $this->dateFactory->create($data['updated_at'] ?? 'now'),
        );
    }

    /**
     * @inheritDoc
     * @param Notice $entity
     */
    public function extract(object $entity): array
    {
        return [
            'group_id'       => $entity->groupId,
            'user_id'        => $entity->userId,
            'target_user_id' => $entity->targetUserId,
            'title'          => $entity->title,
            'content'        => $entity->message,
            'status'         => $entity->status,
            'priority'       => $entity->priority,
            'start_date'     => $entity->startDate?->format('Y-m-d H:i:s'),
            'end_date'       => $entity->endDate?->format('Y-m-d H:i:s'),
            'updated_at'     => $entity->updatedAt->format('Y-m-d H:i:s'),
            'created_at'     => $entity->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}