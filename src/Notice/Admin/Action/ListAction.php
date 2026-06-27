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
            'id',
            'title',
            'status',
            'priority',
            'start_date',
            'end_date' => $orderBy,
            default    => 'id',
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
                'id'          => $notice->id,
                'title'       => $notice->title,
                'group_name'  => $groupName,
                'target_user' => $targetUserText,
                'status'      => $notice->status,
                'priority'    => $notice->priority,
                'start_date'  => $notice->startDate,
                'end_date'    => $notice->endDate,
                'actions'     => $actionsHtml,
            ];
        }, $entities);
    }

    /**
     * @inheritDoc
     */
    public function createGridRenderer(
        AbstractGrid $gridDefinition,
        array $gridData,
        int $totalItems,
        int $perPage,
        string $orderBy,
        string $direction
    ): GridRenderer
    {
        $groupsLookup = $this->groupRepository->getLookupPairs();
        $statusesLookup = Form::getStatusPairs();
        $prioritiesLookup = Form::getPriorityPairs();

        $filterCallback = static function () use ($groupsLookup, $statusesLookup, $prioritiesLookup): void {
            $activeFilters = (new FilterBuilder())
                ->key('filter_status', 'status')
                ->key('filter_priority', 'priority')
                ->integer('filter_group', 'group')
                ->text('filter_search', 'search')
                ->build();

            $currentStatus = $activeFilters['status'] ?? '';
            $currentPriority = $activeFilters['priority'] ?? '';
            $currentGroup = isset($activeFilters['group']) ? (string)$activeFilters['group'] : '';
            $searchQuery = $activeFilters['search'] ?? '';

            echo '<div class="alignleft actions" style="display: flex; gap: 6px; align-items: center; width: 100%; flex-wrap: wrap; margin-bottom: 10px;">';

            echo sprintf(
                '<input type="search" name="filter_search" value="%s" placeholder="%s" style="height: 30px; margin: 0;" />',
                esc_attr($searchQuery),
                esc_attr__('Search notices...', 'modestox-admin-sticky-notes'),
            );

            echo '<select name="filter_group" id="filter_group" style="margin: 0;">';
            echo sprintf('<option value="">%s</option>', esc_html__('All Target Groups', 'modestox-admin-sticky-notes'));
            echo sprintf(
                '<option value="0" %s>%s</option>',
                selected($currentGroup, '0', false),
                esc_html__('— All Groups —', 'modestox-admin-sticky-notes'),
            );
            foreach ($groupsLookup as $gId => $gName) {
                if ($gId === 0) {
                    continue;
                }
                echo sprintf('<option value="%d" %s>%s</option>', $gId, selected($currentGroup, (string)$gId, false), esc_html($gName));
            }
            echo '</select>';

            echo '<select name="filter_status" id="filter_status" style="margin: 0;">';
            echo sprintf('<option value="">%s</option>', esc_html__('All Statuses', 'modestox-admin-sticky-notes'));
            foreach ($statusesLookup as $val => $label) {
                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($val), selected($currentStatus, $val, false), esc_html($label));
            }
            echo '</select>';

            echo '<select name="filter_priority" id="filter_priority" style="margin: 0;">';
            echo sprintf('<option value="">%s</option>', esc_html__('All Priorities', 'modestox-admin-sticky-notes'));
            foreach ($prioritiesLookup as $val => $label) {
                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($val), selected($currentPriority, $val, false), esc_html($label));
            }
            echo '</select>';

            submit_button(__('Filter', 'modestox-admin-sticky-notes'), 'button', 'filter_action', false, ['style' => 'margin: 0;']);

            if (!empty($currentStatus) || !empty($currentPriority) || $currentGroup !== '' || !empty($searchQuery)) {
                $resetUrl = admin_url(sprintf('admin.php?page=%s', sanitize_key($_GET['page'] ?? '')));
                echo sprintf(
                    '<a href="%s" class="button button-secondary" style="margin: 0 0 0 4px; display: inline-flex; align-items: center; height: 30px;">%s</a>',
                    esc_url($resetUrl),
                    esc_html__('Reset', 'modestox-admin-sticky-notes'),
                );
            }

            echo '</div>';
        };

        return new GridRenderer(
            $gridDefinition->getColumns(),
            $gridData,
            $totalItems,
            $perPage,
            $orderBy,
            $direction,
            $filterCallback
        );
    }
}