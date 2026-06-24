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
     * Fetches all notices with strict sorting and pagination boundaries.
     *
     * @param string $orderBy Validated database column name.
     * @param string $direction Sort order direction (ASC|DESC).
     * @param int $limit Maximum number of records to return.
     * @param int $offset Number of records to skip.
     * @return array<int, Notice>
     */
    public function findAll(string $orderBy = 'id', string $direction = 'DESC', int $limit = 20, int $offset = 0): array
    {
        global $wpdb;

        // Strict whitelist filtering to prevent SQL injection vulnerabilities
        $allowedColumns = ['id', 'title', 'status', 'priority', 'start_date', 'end_date'];
        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'id';
        }

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = sprintf(
            "SELECT * FROM %s ORDER BY %s %s LIMIT %d OFFSET %d",
            $this->tableName,
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
     * Returns the total count of rows inside the database table.
     *
     * @return int
     */
    public function countAll(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->tableName}");
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