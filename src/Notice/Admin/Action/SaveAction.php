<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Admin;

namespace Modestox\AdminStickyNotes\Notice\Admin\Action;

use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Notice\Domain\Notice;

/**
 * Single Action Controller processing and persisting payload updates inside database.
 */
final readonly class SaveAction
{
    /**
     * Dependency Injection handled via constructor property promotion.
     */
    public function __construct(
        private NoticeRepository $repository,
    ) {}

    /**
     * Intercepts POST updates, processes tokens, and triggers mutations on repositories.
     */
    public function execute(): void
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
            targetUserId: isset($_POST['targetUserId']) ? (int)$_POST['targetUserId'] : 0,
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
}