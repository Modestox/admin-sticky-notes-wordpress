<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Repository;

use Modestox\AdminStickyNotes\Shared\Database\AbstractRepository;

/**
 * Handles persistent database operations for Sticky Note Groups.
 *
 * @extends AbstractRepository<\stdClass>
 */
final readonly class GroupRepository extends AbstractRepository
{
    /**
     * @inheritDoc
     */
    protected function getTableNameKeyword(): string
    {
        return 'modestox_sticky_note_groups';
    }

    /**
     * Fetches all registered groups mapped as an associative ID to Title registry.
     *
     * @return array<int, string>
     */
    public function getLookupPairs(): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT id, title FROM {$this->tableName} ORDER BY sort_order ASC, title ASC",
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        $pairs = [];
        foreach ($rows as $row) {
            $pairs[(int)$row['id']] = (string)$row['title'];
        }

        return $pairs;
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data): \stdClass
    {
        $entity = new \stdClass();
        $entity->id = isset($data['id']) ? (int)$data['id'] : null;
        $entity->slug = (string)$data['slug'];
        $entity->title = (string)$data['title'];
        $entity->allowedRoles = (string)$data['allowed_roles'];
        $entity->sortOrder = (int)$data['sort_order'];
        $entity->createdAt = (string)$data['created_at'];

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function extract(object $entity): array
    {
        return [
            'slug'          => $entity->slug,
            'title'         => $entity->title,
            'allowed_roles' => $entity->allowedRoles,
            'sort_order'    => $entity->sortOrder,
            'created_at'    => $entity->createdAt,
        ];
    }
}