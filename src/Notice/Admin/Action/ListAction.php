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

use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Notice\Admin\Grid;
use Modestox\AdminStickyNotes\Notice\Admin\Form;
use Modestox\AdminStickyNotes\Notice\Domain\Notice;
use Modestox\AdminStickyNotes\Shared\Ui\GridRenderer;

/**
 * Single Action Controller responsible exclusively for fetching and rendering the notices grid.
 */
final readonly class ListAction
{
    /**
     * Dependency Injection handled via constructor property promotion.
     */
    public function __construct(
        private NoticeRepository $repository,
        private GroupRepository $groupRepository,
    ) {}

    /**
     * Compiles grid metadata definitions and flushes standard system list views.
     */
    public function execute(): void
    {
        $gridDefinition = new Grid();

        $orderBy = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'id';
        $direction = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

        $dbOrderBy = match ($orderBy) {
            'title'     => 'title',
            'status'    => 'status',
            'priority'  => 'priority',
            'startDate' => 'start_date',
            'endDate'   => 'end_date',
            default     => 'id',
        };

        $filters = [];
        if (!empty($_GET['filter_status'])) {
            $filters['status'] = sanitize_key($_GET['filter_status']);
        }
        if (!empty($_GET['filter_priority'])) {
            $filters['priority'] = sanitize_key($_GET['filter_priority']);
        }
        if (isset($_GET['filter_group']) && $_GET['filter_group'] !== '') {
            $filters['group'] = sanitize_text_field($_GET['filter_group']);
        }
        if (!empty($_GET['filter_search'])) {
            $filters['search'] = sanitize_text_field($_GET['filter_search']);
        }

        $configKey = 'modestox_adminstickynotes_general_grid_page_limit';
        $perPage = (int)get_option($configKey, 10);

        if ($perPage <= 0) {
            $perPage = 10;
        }

        $currentPage = isset($_GET['paged']) ? max(1, (int)$_GET['paged']) : 1;
        $offset = ($currentPage - 1) * $perPage;

        $totalItems = $this->repository->countAll($filters);
        $notices = $this->repository->findAll($dbOrderBy, $direction, $perPage, $offset, $filters);
        $groupsLookup = $this->groupRepository->getLookupPairs();
        $usersLookup = $this->getWpUsersLookup();

        $gridData = array_map(static function (Notice $notice) use ($groupsLookup, $usersLookup) {
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
        }, $notices);

        $renderer = new GridRenderer(
            $gridDefinition->getColumns(),
            $gridData,
            $totalItems,
            $perPage,
            $groupsLookup,
            Form::getStatusPairs(),
            Form::getPriorityPairs(),
        );

        $renderer->prepare_items();

        echo '<div class="wrap">';
        echo sprintf('<h1 class="wp-heading-inline">%s</h1>', esc_html__('Admin Notices Pool', 'modestox-admin-sticky-notes'));
        echo sprintf(
            '<a href="?page=%s&action=new" class="page-title-action">%s</a>',
            esc_attr($_GET['page'] ?? ''),
            esc_html__('Add New Notice', 'modestox-admin-sticky-notes'),
        );
        echo '<hr class="wp-header-end">';

        echo sprintf('<form method="get" action="%s">', esc_url(admin_url('admin.php')));
        echo sprintf('<input type="hidden" name="page" value="%s" />', esc_attr($_GET['page'] ?? ''));

        $renderer->display();

        echo '</form>';
        echo '</div>';
    }

    /**
     * Compiles maps of registered backend users linking internal id structures to display names.
     *
     * @return array<int, string>
     */
    private function getWpUsersLookup(): array
    {
        $users = get_users([
            'fields' => ['ID', 'display_name'],
            'number' => 500,
        ]);

        $lookup = [];

        if (is_array($users) && !empty($users)) {
            foreach ($users as $user) {
                $lookup[(int)$user->ID] = (string)$user->display_name;
            }
        }

        if (empty($lookup)) {
            $currentUser = wp_get_current_user();
            if ($currentUser->ID > 0) {
                $lookup[(int)$currentUser->ID] = (string)$currentUser->display_name;
            }
        }

        return $lookup;
    }
}