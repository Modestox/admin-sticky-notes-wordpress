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

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractListAction;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Shared\Ui\Component\FilterBuilder;
use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractGrid;
use Modestox\AdminStickyNotes\Group\Admin\Grid;
use Modestox\AdminStickyNotes\Group\Domain\Group;
use Modestox\AdminStickyNotes\Shared\Ui\GridRenderer;

/**
 * Single Action Controller responsible exclusively for fetching and rendering the groups grid.
 */
final readonly class ListAction extends AbstractListAction
{
    /**
     * Injected components wired strictly via PHP 8.3 constructor property promotion.
     */
    public function __construct(
        private GroupRepository $repository,
    ) {}

    /**
     * @inheritDoc
     */
    protected function getTotalItemsCount(array $filters): int
    {
        return $this->repository->countAll($filters);
    }

    /**
     * @inheritDoc
     * @return array<int, Group>
     */
    protected function loadEntities(string $orderBy, string $direction, int $perPage, int $offset, array $filters): array
    {
        return $this->repository->findAll($orderBy, $direction, $perPage, $offset, $filters);
    }

    /**
     * @inheritDoc
     */
    public function getGridDefinition(): AbstractGrid
    {
        return new Grid();
    }

    /**
     * @inheritDoc
     */
    public function getAddNewLabel(): string
    {
        return __('Add New Group', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function mapOrderByField(string $orderBy): string
    {
        return match ($orderBy) {
            'title'     => 'title',
            'slug'      => 'slug',
            'sortOrder' => 'sort_order',
            'createdAt' => 'created_at',
            default     => 'sort_order',
        };
    }

    /**
     * @inheritDoc
     */
    public function configureFilters(FilterBuilder $builder): FilterBuilder
    {
        return $builder->text('filter_search', 'search');
    }

    /**
     * @inheritDoc
     * @param array<int, Group> $entities
     */
    public function prepareGridRows(array $entities): array
    {
        global $wp_roles;

        return array_map(static function (Group $group) use ($wp_roles) {
            $assignedRoles = maybe_unserialize($group->allowedRoles);
            if (!is_array($assignedRoles) || empty($assignedRoles)) {
                $rolesText = '— ' . __('All Roles', 'modestox-admin-sticky-notes') . ' —';
            } else {
                $names = [];
                foreach ($assignedRoles as $roleKey) {
                    $names[] = translate_user_role($wp_roles->role_names[$roleKey] ?? $roleKey);
                }
                $rolesText = implode(', ', $names);
            }

            $editUrl = admin_url(sprintf('admin.php?page=%s&action=edit&id=%d', sanitize_key($_GET['page'] ?? ''), $group->id));
            $deleteUrl = wp_nonce_url(
                admin_url(sprintf('admin.php?page=%s&action=delete&id=%d', sanitize_key($_GET['page'] ?? ''), $group->id)),
                'delete_group_' . $group->id,
            );

            $actionsHtml = sprintf(
                '<a href="%s" class="edit">%s</a> | <a href="%s" class="submitdelete" style="color: #b32d2e;" onclick="return confirm(\'%s\');">%s</a>',
                esc_url($editUrl),
                esc_html__('Edit', 'modestox-admin-sticky-notes'),
                esc_url($deleteUrl),
                esc_attr__('Are you sure you want to delete this group?', 'modestox-admin-sticky-notes'),
                esc_html__('Delete', 'modestox-admin-sticky-notes'),
            );

            return [
                'id'           => $group->id,
                'title'        => $group->title,
                'slug'         => $group->slug,
                'allowedRoles' => $rolesText,
                'sortOrder'    => $group->sortOrder,
                'createdAt'    => $group->createdAt,
                'actions'      => $actionsHtml,
            ];
        }, $entities);
    }

    /**
     * @inheritDoc
     */
    public function createGridRenderer(AbstractGrid $gridDefinition, array $gridData, int $totalItems, int $perPage): GridRenderer
    {
        return new GridRenderer(
            $gridDefinition->getColumns(),
            $gridData,
            $totalItems,
            $perPage
        );
    }
}