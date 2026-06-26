<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Admin\Action;

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractListAction;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\WpUserDirectory;
use Modestox\AdminStickyNotes\Shared\Ui\Component\FilterBuilder;
use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractGrid;
use Modestox\AdminStickyNotes\Notice\Admin\Grid;
use Modestox\AdminStickyNotes\Notice\Admin\Form;
use Modestox\AdminStickyNotes\Notice\Domain\Notice;
use Modestox\AdminStickyNotes\Shared\Ui\GridRenderer;

/**
 * Single Action Controller responsible exclusively for fetching and rendering the notices grid.
 */
final readonly class ListAction extends AbstractListAction
{
    /**
     * Injected components wired strictly via PHP 8.3 constructor property promotion.
     */
    public function __construct(
        private NoticeRepository $repository,
        private GroupRepository $groupRepository,
        private WpUserDirectory $wpUserDirectory,
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
     * @return array<int, Notice>
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
        return __('Add New Notice', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function mapOrderByField(string $orderBy): string
    {
        return match ($orderBy) {
            'title'     => 'title',
            'status'    => 'status',
            'priority'  => 'priority',
            'startDate' => 'start_date',
            'endDate'   => 'end_date',
            default     => 'id',
        };
    }

    /**
     * @inheritDoc
     */
    public function configureFilters(FilterBuilder $builder): FilterBuilder
    {
        return $builder
            ->key('filter_status', 'status')
            ->key('filter_priority', 'priority')
            ->integer('filter_group', 'group')
            ->text('filter_search', 'search');
    }

    /**
     * @inheritDoc
     * @param array<int, Notice> $entities
     */
    public function prepareGridRows(array $entities): array
    {
        $groupsLookup = $this->groupRepository->getLookupPairs();
        $usersLookup = $this->wpUserDirectory->getLookupPairs();

        return array_map(static function (Notice $notice) use ($groupsLookup, $usersLookup) {
            $assignedGroups = maybe_unserialize($notice->groupId);

            if (!is_array($assignedGroups) || in_array(0, $assignedGroups, true) || empty($assignedGroups)) {
                $groupName = sprintf('<strong>%s</strong>', esc_html__('All Groups', 'modestox-admin-sticky-notes'));
            } else {
                $names = [];
                foreach ($assignedGroups as $gId) {
                    $names[] = $groupsLookup[(int)$gId] ?? sprintf('#%d', $gId);
                }
                $groupName = implode(', ', $names);
            }

            $targetUserText = $notice->targetUserId === 0
                ? '— ' . __('All Users', 'modestox-admin-sticky-notes') . ' —'
                : ($usersLookup[$notice->targetUserId] ?? sprintf('#%d', $notice->targetUserId));

            $editUrl = admin_url(sprintf('admin.php?page=%s&action=edit&id=%d', sanitize_key($_GET['page'] ?? ''), $notice->id));
            $deleteUrl = wp_nonce_url(
                admin_url(sprintf('admin.php?page=%s&action=delete&id=%d', sanitize_key($_GET['page'] ?? ''), $notice->id)),
                'delete_notice_' . $notice->id,
            );

            $actionsHtml = sprintf(
                '<a href="%s" class="edit">%s</a> | <a href="%s" class="submitdelete" style="color: #b32d2e;" onclick="return confirm(\'%s\');">%s</a>',
                esc_url($editUrl),
                esc_html__('Edit', 'modestox-admin-sticky-notes'),
                esc_url($deleteUrl),
                esc_attr__('Are you sure you want to delete this notice?', 'modestox-admin-sticky-notes'),
                esc_html__('Delete', 'modestox-admin-sticky-notes'),
            );

            return [
                'id'         => $notice->id,
                'title'      => $notice->title,
                'groupName'  => $groupName,
                'targetUser' => $targetUserText,
                'status'     => $notice->status,
                'priority'   => $notice->priority,
                'startDate'  => $notice->startDate,
                'endDate'    => $notice->endDate,
                'actions'    => $actionsHtml,
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
            $perPage,
            $this->groupRepository->getLookupPairs(),
            Form::getStatusPairs(),
            Form::getPriorityPairs(),
        );
    }
}