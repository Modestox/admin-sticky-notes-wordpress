<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Repository\Notice;

use Modestox\AdminStickyNotes\Model\Notice\Notice;

/**
 * Handles persistent abstraction layer boundaries for Notice domain entities.
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
     * Resolves a unique identity record into a concrete domain entity instance.
     *
     * @param int $id
     * @return Notice|null
     */
    public function findById(int $id): ?Notice
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->tableName} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Fetches a paginated and sorted slice of the database registry state.
     *
     * @param string $orderBy
     * @param string $direction
     * @param int $limit
     * @param int $offset
     * @return array<int, Notice>
     */
    public function findAll(string $orderBy = 'id', string $direction = 'DESC', int $limit = 20, int $offset = 0): array
    {
        global $wpdb;

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        // Валидация колонки по белому списку названий из БД
        $allowedColumns = [
            'id', 'group_id', 'user_id', 'target_user_id',
            'title', 'content', 'status', 'priority',
            'start_date', 'end_date', 'created_at', 'updated_at'
        ];

        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'id';
        }

        // Безопасная сборка запроса с динамической сортировкой и плейсхолдерами лимитов
        $query = sprintf(
            "SELECT * FROM %s ORDER BY %s %s LIMIT %d OFFSET %d",
            $this->tableName,
            $orderBy,
            $direction,
            $limit,
            $offset
        );

        $rows = $wpdb->get_results($query, ARRAY_A);

        if (!is_array($rows)) {
            return [];
        }

        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->hydrate($row);
        }

        return $collection;
    }

    /**
     * Persists or updates the domain model registry state boundaries.
     *
     * @param Notice $notice
     * @return bool
     */
    public function save(Notice $notice): bool
    {
        global $wpdb;

        // Полное и точное сопоставление свойств DTO с реальными колонками в БД
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
            $result = $wpdb->insert($this->tableName, $data);
            return $result !== false;
        }

        $result = $wpdb->update($this->tableName, $data, ['id' => $notice->id]);
        return $result !== false;
    }

    /**
     * Evicts a target identity completely out of persistent database registries.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        global $wpdb;
        $result = $wpdb->delete($this->tableName, ['id' => $id]);
        return $result !== false;
    }

    /**
     * Hydrates a single raw database row context map into a strict Notice DTO object.
     *
     * @param array<string, mixed> $data
     * @return Notice
     */
    public function hydrate(array $data): Notice
    {
        $startDateRaw = $data['start_date'] ?? null;
        $endDateRaw   = $data['end_date'] ?? null;

        return new Notice(
            id: isset($data['id']) ? (int)$data['id'] : null,
            groupId: (string)($data['group_id'] ?? 0),
            userId: (int)($data['user_id'] ?? 0),
            targetUserId: (int)($data['target_user_id'] ?? 0),
            title: (string)($data['title'] ?? ''),
            message: (string)($data['content'] ?? ''),
            status: (string)($data['status'] ?? 'draft'),
            priority: (string)($data['priority'] ?? 'normal'),
            startDate: $startDateRaw ? new \DateTimeImmutable((string)$startDateRaw) : null,
            endDate: $endDateRaw ? new \DateTimeImmutable((string)$endDateRaw) : null,
            createdAt: new \DateTimeImmutable((string)($data['created_at'] ?? 'now')),
            updatedAt: new \DateTimeImmutable((string)($data['updated_at'] ?? 'now'))
        );
    }
}