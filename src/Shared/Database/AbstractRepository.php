<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Database;

/**
 * Baseline persistent database operations layer implementing generic CRUD orchestration templates.
 *
 * @template TEntity of object
 * @implements HydratorInterface<TEntity>
 */
abstract readonly class AbstractRepository implements HydratorInterface
{
    /**
     * Runtime computed fully qualified operational table name string marker.
     */
    protected string $tableName;

    /**
     * Initializing shared global database bridge access point connections.
     */
    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . $this->getTableNameKeyword();
    }

    /**
     * Fetches a single domain model entity mapped by its primary key identifier.
     *
     * @param int $id Database entry primary incremental record integer index key.
     * @return TEntity|null Hydrated operational entity target domain model snapshot instance.
     */
    public function findById(int $id): ?object
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
     * Persists or updates a domain model entity inside the database engine storage.
     *
     * @param TEntity $entity Targeted operational domain model description compound instance.
     * @return void
     */
    public function save(object $entity): void
    {
        global $wpdb;

        $data = $this->extract($entity);
        $id = isset($entity->id) ? (int)$entity->id : null;

        if ($id === null) {
            $wpdb->insert($this->tableName, $data);
        } else {
            $wpdb->update($this->tableName, $data, ['id' => $id]);
        }
    }

    /**
     * Returns the total count of untargeted or baseline records within the database table storage.
     *
     * @return int Computed summary calculation aggregate count size.
     */
    public function count(): int
    {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$this->tableName}");
    }

    /**
     * Completely evicts a single record boundary from the persistence layer registry maps.
     *
     * @param int $id Target identity record structure index integer marker key.
     * @return void
     */
    public function delete(int $id): void
    {
        global $wpdb;
        $wpdb->delete($this->tableName, ['id' => $id]);
    }

    /**
     * Extracts database matching raw text keyword segment representing actual targeted physical storage name.
     *
     * @return string Raw table structural catalog label marker keyword.
     */
    abstract protected function getTableNameKeyword(): string;
}