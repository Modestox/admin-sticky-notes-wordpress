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

use Modestox\AdminStickyNotes\Notice\Domain\Notice;

/**
 * Handles persistent database operations for Administrative Notices.
 */
final readonly class NoticeRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'modestox_sticky_notes';
    }

    /**
     * Fetches notices with strict sorting, pagination, and structural filtering boundaries.
     *
     * @param string $orderBy Validated database column name.
     * @param string $direction Sort order direction (ASC|DESC).
     * @param int $limit Maximum number of records to return.
     * @param int $offset Number of records to skip.
     * @param array{status?: string, priority?: string} $filters SQL constraints filters.
     * @return array<int, Notice>
     */
    public function findAll(string $orderBy = 'id', string $direction = 'DESC', int $limit = 20, int $offset = 0, array $filters = []): array
    {
        global $wpdb;

        $allowedColumns = ['id', 'title', 'status', 'priority', 'start_date', 'end_date'];
        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'id';
        }

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $whereClauses = ['1=1'];

        if (!empty($filters['status'])) {
            $whereClauses[] = $wpdb->prepare("status = %s", $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $whereClauses[] = $wpdb->prepare("priority = %s", $filters['priority']);
        }

        if (isset($filters['group']) && $filters['group'] !== '') {
            $groupVal = (int)$filters['group'];
            $likePattern = '%' . $wpdb->esc_like(sprintf(':%d;', $groupVal)) . '%';
            $whereClauses[] = $wpdb->prepare("group_id LIKE %s", $likePattern);
        }

        if (!empty($filters['search'])) {
            $searchPattern = '%' . $wpdb->esc_like($filters['search']) . '%';
            $whereClauses[] = $wpdb->prepare("(title LIKE %s OR content LIKE %s)", $searchPattern, $searchPattern);
        }

        $whereSql = implode(' AND ', $whereClauses);

        $sql = sprintf(
            "SELECT * FROM %s WHERE %s ORDER BY %s %s LIMIT %d OFFSET %d",
            $this->tableName,
            $whereSql,
            $orderBy,
            $direction,
            $limit,
            $offset
        );

        $rows = $wpdb->get_results($sql, ARRAY_A);

        if (!is_array($rows)) {
            return [];
        }

        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * Returns the total count of filtered rows inside the database table.
     *
     * @param array{status?: string, priority?: string} $filters SQL constraints filters.
     * @return int
     */
    public function countAll(array $filters = []): int
    {
        global $wpdb;

        $whereClauses = ['1=1'];

        if (!empty($filters['status'])) {
            $whereClauses[] = $wpdb->prepare("status = %s", $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $whereClauses[] = $wpdb->prepare("priority = %s", $filters['priority']);
        }

        if (isset($filters['group']) && $filters['group'] !== '') {
            $groupVal = (int)$filters['group'];
            // Ищем подстроку вида i:0;i:1; или s:1:"1"; внутри сериализованного массива
            $likePattern = '%' . $wpdb->esc_like(sprintf(':%d;', $groupVal)) . '%';
            $whereClauses[] = $wpdb->prepare("group_id LIKE %s", $likePattern);
        }

        if (!empty($filters['search'])) {
            $searchPattern = '%' . $wpdb->esc_like($filters['search']) . '%';
            $whereClauses[] = $wpdb->prepare("(title LIKE %s OR content LIKE %s)", $searchPattern, $searchPattern);
        }

        $whereSql = implode(' AND ', $whereClauses);

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->tableName} WHERE {$whereSql}");
    }

    /**
     * Fetches a single notice entry by its primary key identifier.
     */
    public function findById(int $id): ?Notice
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->tableName} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Persists or updates a notice entity inside database engine storage.
     */
    public function save(Notice $notice): void
    {
        global $wpdb;

        $data = [
            'group_id'       => $notice->groupId,
            'user_id'        => $notice->userId,
            'target_user_id' => $notice->targetUserId,
            'title'          => $notice->title,
            'content'        => $notice->message,
            'status'         => $notice->status,
            'priority'       => $notice->priority,
            'start_date'     => $notice->startDate?->format('Y-m-d H:i:s'),
            'end_date'       => $notice->endDate?->format('Y-m-d H:i:s'),
            'updated_at'     => $notice->updatedAt->format('Y-m-d H:i:s'),
        ];

        if ($notice->id === null) {
            $data['created_at'] = $notice->createdAt->format('Y-m-d H:i:s');
            $wpdb->insert($this->tableName, $data);
        } else {
            $wpdb->update($this->tableName, $data, ['id' => $notice->id]);
        }
    }

    /**
     * Deletes a single notice entity completely from storage.
     */
    public function delete(int $id): void
    {
        global $wpdb;
        $wpdb->delete($this->tableName, ['id' => $id]);
    }

    /**
     * Maps raw database array layout metadata structures to clean Domain DTO instances.
     */
    private function hydrate(array $data): Notice
    {
        $timezone = wp_timezone();

        return new Notice(
            id: isset($data['id']) ? (int)$data['id'] : null,
            groupId: (string)($data['group_id'] ?? '0'),
            userId: (int)($data['user_id'] ?? 0),
            targetUserId: (int)($data['target_user_id'] ?? 0),
            title: (string)($data['title'] ?? ''),
            message: (string)($data['content'] ?? ''),
            status: (string)($data['status'] ?? 'draft'),
            priority: (string)($data['priority'] ?? 'normal'),
            startDate: !empty($data['start_date']) ? new \DateTimeImmutable($data['start_date'], $timezone) : null,
            endDate: !empty($data['end_date']) ? new \DateTimeImmutable($data['end_date'], $timezone) : null,
            createdAt: new \DateTimeImmutable($data['created_at'] ?? 'now', $timezone),
            updatedAt: new \DateTimeImmutable($data['updated_at'] ?? 'now', $timezone),
        );
    }
}