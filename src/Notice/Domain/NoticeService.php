<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Domain;

use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;

/**
 * Centralized domain service orchestrating business operations and filtered extractions for Notices.
 */
final readonly class NoticeService
{
    /**
     * Dependency Injection via Constructor Property Promotion.
     */
    public function __construct(
        private NoticeRepository $repository
    ) {}

    /**
     * Resolves a collection of active notices strictly matching business criteria limitations.
     *
     * @param array<string, mixed> $filters
     * @param string $orderBy
     * @param string $direction
     * @param int $limit
     * @param int $offset
     * @return array<int, Notice>
     */
    public function getActiveNotices(
        array $filters = [],
        string $orderBy = 'id',
        string $direction = 'DESC',
        int $limit = 20,
        int $offset = 0
    ): array {
        global $wpdb;

        $tableName = $wpdb->prefix . 'modestox_sticky_notes';
        $conditions = [];
        $parameters = [];

        if (isset($filters['groupId'])) {
            $conditions[] = 'group_id = %s';
            $parameters[] = (string)$filters['groupId'];
        }

        if (isset($filters['targetUserId'])) {
            $conditions[] = '(target_user_id = %d OR target_user_id = 0)';
            $parameters[] = (int)$filters['targetUserId'];
        }

        if (!empty($filters['statuses'])) {
            $statusPlaceholders = implode(',', array_fill(0, count($filters['statuses']), '%s'));
            $conditions[] = "status IN ($statusPlaceholders)";
            foreach ($filters['statuses'] as $status) {
                $parameters[] = (string)$status;
            }
        }

        if (isset($filters['activeAt']) && $filters['activeAt'] instanceof \DateTimeImmutable) {
            $formattedDate = $filters['activeAt']->format('Y-m-d H:i:s');
            $conditions[] = '(start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s)';
            $parameters[] = $formattedDate;
            $parameters[] = $formattedDate;
        }

        $whereSql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $orderBy = in_array($orderBy, ['id', 'priority', 'created_at'], true) ? $orderBy : 'id';

        $query = "SELECT * FROM {$tableName} {$whereSql} ORDER BY {$orderBy} {$direction} LIMIT %d OFFSET %d";

        $parameters[] = $limit;
        $parameters[] = $offset;

        $rows = $wpdb->get_results($wpdb->prepare($query, ...$parameters), ARRAY_A);

        if (!is_array($rows)) {
            return [];
        }

        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->repository->hydrate($row);
        }

        return $collection;
    }
}