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
use Modestox\AdminStickyNotes\Shared\Database\QueryBuilder;
use Modestox\AdminStickyNotes\Group\Domain\Group;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

/**
 * Handles persistent database operations for Sticky Note Groups.
 *
 * @extends AbstractRepository<Group>
 */
final readonly class GroupRepository extends AbstractRepository
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
        return 'modestox_sticky_note_groups';
    }

    /**
     * Fetches groups with strict sorting, pagination, and structural filtering boundaries.
     *
     * @return array<int, Group>
     */
    public function findAll(string $orderBy = 'sort_order', string $direction = 'ASC', int $limit = 20, int $offset = 0, array $filters = []): array
    {
        global $wpdb;

        $allowedColumns = ['id', 'slug', 'title', 'sort_order', 'created_at'];

        $query = (new QueryBuilder($this->tableName))
            ->generalSearch(['slug', 'title'], $filters['search'] ?? null)
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
     * Fetches absolutely all groups for the dashboard without limits or filters.
     *
     * @return array<int, Group>
     */
    public function findVisible(): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT * FROM {$this->tableName} ORDER BY sort_order ASC",
            ARRAY_A,
        );

        if (!is_array($rows)) {
            return [];
        }

        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * Returns the total count of filtered rows inside the database table.
     */
    public function countAll(array $filters = []): int
    {
        global $wpdb;

        $query = (new QueryBuilder($this->tableName))
            ->generalSearch(['slug', 'title'], $filters['search'] ?? null);

        return (int)$wpdb->get_var($query->getCountSql());
    }

    /**
     * Checks if a specific slug already exists in the database table boundaries.
     */
    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        global $wpdb;

        if ($excludeId !== null) {
            $sql = $wpdb->prepare("SELECT COUNT(*) FROM {$this->tableName} WHERE slug = %s AND id != %d", $slug, $excludeId);
        } else {
            $sql = $wpdb->prepare("SELECT COUNT(*) FROM {$this->tableName} WHERE slug = %s", $slug);
        }

        return (int)$wpdb->get_var($sql) > 0;
    }

    /**
     * Cultivates all registered groups mapped as an associative ID to Title registry.
     *
     * @return array<int, string>
     */
    public function getLookupPairs(): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT id, title FROM {$this->tableName} ORDER BY sort_order ASC, title ASC",
            ARRAY_A,
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
     * @return Group
     */
    public function hydrate(array $data): Group
    {
        return new Group(
            id: isset($data['id']) ? (int)$data['id'] : null,
            slug: (string)($data['slug'] ?? ''),
            title: (string)($data['title'] ?? ''),
            allowedRoles: (string)($data['allowed_roles'] ?? ''),
            sortOrder: (int)($data['sort_order'] ?? 0),
            createdAt: $this->dateFactory->create($data['created_at'] ?? 'now'),
        );
    }

    /**
     * @inheritDoc
     * @param Group $entity
     */
    public function extract(object $entity): array
    {
        return [
            'slug'          => $entity->slug,
            'title'         => $entity->title,
            'allowed_roles' => $entity->allowedRoles,
            'sort_order'    => $entity->sortOrder,
            'created_at'    => $entity->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}