<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Service;

use Modestox\AdminStickyNotes\Group\Domain\Group;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;

/**
 * Domain Service encapsulating strict business logic, validation rules, and guards for Groups.
 */
final readonly class GroupService
{
    /**
     * Dependency Injection of persistence layer handled via constructor.
     */
    public function __construct(
        private GroupRepository $repository,
    ) {}

    /**
     * Validates and persists a Group entity. Throws exceptions on business invariant violations.
     *
     * @throws \InvalidArgumentException If required properties are missing or slug collisions happen.
     */
    public function save(Group $group): void
    {
        if (trim($group->title) === '') {
            throw new \InvalidArgumentException(
                __('The group title configuration cannot be empty.', 'modestox-admin-sticky-notes')
            );
        }

        if (trim($group->slug) === '') {
            throw new \InvalidArgumentException(
                __('The group URL identifier slug cannot be empty.', 'modestox-admin-sticky-notes')
            );
        }

        if ($this->repository->existsBySlug($group->slug, $group->id)) {
            throw new \InvalidArgumentException(
                sprintf(__('The group slug context "%s" is already registered.', 'modestox-admin-sticky-notes'), $group->slug)
            );
        }

        $this->repository->save($group);
    }

    /**
     * Safe guards deletion processes checking linked structural relationships.
     */
    public function delete(int $id): void
    {
        // Future boundary: Guard deletion if there are active assignments or notes referencing this group
        $this->repository->delete($id);
    }
}