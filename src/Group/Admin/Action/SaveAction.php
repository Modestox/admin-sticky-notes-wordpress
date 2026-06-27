<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Admin\Action;

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractSaveAction;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Group\Service\GroupService;
use Modestox\AdminStickyNotes\Group\Domain\Group;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

/**
 * Administrative action managing secure assembly and persistence of structural Group payloads.
 */
final readonly class SaveAction extends AbstractSaveAction
{
    /**
     * Injected components wired strictly via PHP 8.3 constructor property promotion.
     */
    public function __construct(
        private GroupRepository $repository,
        private GroupService $groupService,
        private DateFactory $dateFactory,
    ) {}

    /**
     * @inheritDoc
     */
    protected function loadExistingEntity(?int $id): ?Group
    {
        if ($id === null) {
            return null;
        }
        return $this->repository->findById($id);
    }

    /**
     * @inheritDoc
     * @param Group $entity
     */
    protected function persist(object $entity): void
    {
        try {
            $this->groupService->save($entity);
        } catch (\InvalidArgumentException $e) {
            wp_die(esc_html($e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getNonceActionName(): string
    {
        return 'save_group_action';
    }

    /**
     * @inheritDoc
     */
    public function getNonceFieldName(): string
    {
        return 'modestox_nonce';
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUrl(): string
    {
        return sprintf('admin.php?page=%s', sanitize_key($_GET['page'] ?? ''));
    }

    /**
     * @inheritDoc
     * @param Group|null $existingEntity
     * @return Group
     */
    public function buildEntityFromRequest(array $requestData, ?object $existingEntity): Group
    {
        $rolesRaw = $requestData['allowedRoles'] ?? [];
        $roles = is_array($rolesRaw) ? array_map('sanitize_key', $rolesRaw) : [];

        return new Group(
            id: $existingEntity?->id,
            slug: isset($requestData['slug']) ? sanitize_title($requestData['slug']) : '',
            title: isset($requestData['title']) ? sanitize_text_field($requestData['title']) : '',
            allowedRoles: maybe_serialize($roles),
            sortOrder: isset($requestData['sortOrder']) ? (int)$requestData['sortOrder'] : 0,
            createdAt: $existingEntity?->createdAt ?? $this->dateFactory->create('now'),
        );
    }
}