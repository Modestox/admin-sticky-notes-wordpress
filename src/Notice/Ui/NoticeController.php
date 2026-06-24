<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Ui;

use Modestox\AdminStickyNotes\Shared\Crud\AbstractCrudController;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;

use Modestox\AdminStickyNotes\Notice\Domain\Notice;
use Modestox\AdminStickyNotes\Shared\Ui\GridRenderer;
use Modestox\AdminStickyNotes\Shared\Ui\FormRenderer;

/**
 * Concrete routing controller handling the administration lifecycles of notices.
 */
final class NoticeController extends AbstractCrudController
{
    /**
     * Dependency Injection via Constructor Property Promotion.
     */
    public function __construct(
        private NoticeRepository $repository,
        private GroupRepository $groupRepository, // Now strictly expects the domain version
    ) {}

    /**
     * Compiles grid metadata definitions and flushes standard system list views.
     */
    protected function renderGridAction(): void
    {
        $gridDefinition = new NoticeGridDefinition();

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

        $configKey = 'modestox_adminstickynotes_general_grid_page_limit';
        $perPage = (int)get_option($configKey, 10);

        if ($perPage <= 0) {
            $perPage = 10;
        }

        $currentPage = isset($_GET['paged']) ? max(1, (int)$_GET['paged']) : 1;
        $offset = ($currentPage - 1) * $perPage;

        $totalItems = $this->repository->countAll();
        $notices = $this->repository->findAll($dbOrderBy, $direction, $perPage, $offset);
        $groupsLookup = $this->groupRepository->getLookupPairs();

        $gridData = array_map(static function (Notice $notice) use ($groupsLookup) {
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
                'id'        => $notice->id,
                'title'     => $notice->title,
                'groupName' => $groupName,
                'status'    => $notice->status,
                'priority'  => $notice->priority,
                'startDate' => $notice->startDate,
                'endDate'   => $notice->endDate,
                'actions'   => $actionsHtml,
            ];
        }, $notices);

        $renderer = new GridRenderer($gridDefinition->getColumns(), $gridData, $totalItems, $perPage);
        $renderer->prepare_items();

        echo '<div class="wrap">';
        echo sprintf('<h1 class="wp-heading-inline">%s</h1>', esc_html__('Admin Notices Pool', 'modestox-admin-sticky-notes'));
        echo sprintf(
            '<a href="?page=%s&action=new" class="page-title-action">%s</a>',
            esc_attr($_GET['page'] ?? ''),
            esc_html__('Add New', 'modestox-admin-sticky-notes'),
        );
        echo '<hr class="wp-header-end">';

        $renderer->display();
        echo '</div>';
    }

    /**
     * Hydrates selected models and maps field configurations into standard form views.
     */
    protected function renderFormAction(): void
    {
        $formDefinition = new NoticeFormDefinition();
        $renderer = new FormRenderer();

        $formData = [];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if ($id !== null) {
            $notice = $this->repository->findById($id);
            if ($notice) {
                $savedGroups = maybe_unserialize($notice->groupId);

                if (!is_array($savedGroups)) {
                    $savedGroups = ['0'];
                } else {
                    $savedGroups = array_map('strval', $savedGroups);
                }

                $formData = [
                    'title'     => $notice->title,
                    'message'   => $notice->message,
                    'groupId'   => $savedGroups,
                    'priority'  => $notice->priority,
                    'status'    => $notice->status,
                    'startDate' => $notice->startDate?->format('Y-m-d\TH:i') ?? '',
                    'endDate'   => $notice->endDate?->format('Y-m-d\TH:i') ?? '',
                ];
            }
        }

        $formActionUrl = admin_url(
            sprintf(
                'admin.php?page=%s&action=save%s',
                sanitize_key($_GET['page'] ?? ''),
                $id ? '&id=' . $id : '',
            ),
        );

        $availableGroups = $this->groupRepository->getLookupPairs();

        echo '<div class="wrap">';
        echo sprintf(
            '<h1>%s</h1>',
            $id ? esc_html__('Edit Notice', 'modestox-admin-sticky-notes') : esc_html__('Create Notice', 'modestox-admin-sticky-notes'),
        );
        echo sprintf('<form method="post" action="%s">', esc_url($formActionUrl));

        wp_nonce_field('save_notice_action', 'modestox_nonce');

        $renderer->render($formDefinition->getFields($availableGroups), $formData);

        submit_button($id ? __('Update', 'modestox-admin-sticky-notes') : __('Save', 'modestox-admin-sticky-notes'));
        echo '</form>';
        echo '</div>';
    }

    /**
     * Intercepts POST updates, processes tokens, and triggers mutations on repositories.
     */
    protected function saveAction(): void
    {
        if (!isset($_POST['modestox_nonce']) || !wp_verify_nonce($_POST['modestox_nonce'], 'save_notice_action')) {
            wp_die(esc_html__('Security execution verification failed.', 'modestox-admin-sticky-notes'));
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $timezone = wp_timezone();
        $now = new \DateTimeImmutable('now', $timezone);

        $startDateRaw = sanitize_text_field($_POST['startDate'] ?? '');
        $endDateRaw = sanitize_text_field($_POST['endDate'] ?? '');

        $startDate = null;
        $endDate = null;

        if (!empty($startDateRaw)) {
            try {
                $startDate = new \DateTimeImmutable($startDateRaw, $timezone);
            } catch (\DateMalformedStringException $e) {
                $startDate = null;
            }
        }

        if (!empty($endDateRaw)) {
            try {
                $endDate = new \DateTimeImmutable($endDateRaw, $timezone);
            } catch (\DateMalformedStringException $e) {
                $endDate = null;
            }
        }

        $createdAt = $now;
        if ($id !== null) {
            $existingNotice = $this->repository->findById($id);
            if ($existingNotice) {
                $createdAt = $existingNotice->createdAt;
            }
        }

        $postedGroups = $_POST['groupId'] ?? [];

        if (in_array('0', $postedGroups, true)) {
            $groupIdsArray = [0];
        } else {
            $groupIdsArray = array_map('intval', $postedGroups);
        }

        $noticeDto = new Notice(
            id: $id,
            groupId: maybe_serialize($groupIdsArray),
            userId: get_current_user_id(),
            targetUserId: 0,
            title: sanitize_text_field($_POST['title'] ?? ''),
            message: sanitize_textarea_field($_POST['message'] ?? ''),
            status: sanitize_key($_POST['status'] ?? 'draft'),
            priority: sanitize_key($_POST['priority'] ?? 'normal'),
            startDate: $startDate,
            endDate: $endDate,
            createdAt: $createdAt,
            updatedAt: $now,
        );

        $this->repository->save($noticeDto);

        wp_redirect(admin_url('admin.php?page=modestox-admin-sticky-notes'));
        exit;
    }

    /**
     * Validates cryptographic tokens and deletes entity entries out of storage.
     */
    protected function deleteAction(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_notice_' . $id)) {
            wp_die(esc_html__('Security execution verification failed.', 'modestox-admin-sticky-notes'));
        }

        $this->repository->delete($id);

        wp_redirect(admin_url('admin.php?page=modestox-admin-sticky-notes'));
        exit;
    }
}