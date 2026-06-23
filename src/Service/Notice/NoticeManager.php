<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Service\Notice;

use Modestox\AdminStickyNotes\Repository\Notice\NoticeRepository;
use Modestox\AdminStickyNotes\Model\Notice\Notice;
use Modestox\AdminStickyNotes\Model\Notice\NoticeCriteria;

/**
 * Centralized domain service orchestrating business operations and filtered extractions for Notices.
 */
final readonly class NoticeManager
{
    public function __construct(
        private NoticeRepository $repository
    ) {}

    /**
     * Resolves a collection of active notices strictly matching business criteria limitations.
     *
     * @param NoticeCriteria $criteria
     * @return array<int, Notice>
     */
    public function getActiveNotices(NoticeCriteria $criteria): array
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'modestox_sticky_notes';
        $conditions = [];
        $parameters = [];

        if ($criteria->groupId !== null) {
            $conditions[] = 'group_id = %d';
            $parameters[] = $criteria->groupId;
        }

        if ($criteria->targetUserId !== null) {
            $conditions[] = '(target_user_id = %d OR target_user_id = 0)';
            $parameters[] = $criteria->targetUserId;
        }

        if (!empty($criteria->statuses)) {
            $statusPlaceholders = implode(',', array_fill(0, count($criteria->statuses), '%s'));
            $conditions[] = "status IN ($statusPlaceholders)";
            foreach ($criteria->statuses as $status) {
                $parameters[] = $status;
            }
        }

        if ($criteria->activeAt !== null) {
            $formattedDate = $criteria->activeAt->format('Y-m-d H:i:s');
            $conditions[] = '(start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s)';
            $parameters[] = $formattedDate;
            $parameters[] = $formattedDate;
        }

        $whereSql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $direction = strtoupper($criteria->direction) === 'ASC' ? 'ASC' : 'DESC';
        $orderBy = in_array($criteria->orderBy, ['id', 'priority', 'created_at'], true) ? $criteria->orderBy : 'id';

        $query = "SELECT * FROM {$tableName} {$whereSql} ORDER BY {$orderBy} {$direction} LIMIT %d OFFSET %d";

        $parameters[] = $criteria->limit;
        $parameters[] = $criteria->offset;

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