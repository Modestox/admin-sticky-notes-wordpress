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

/**
 * Single Action Controller strictly isolating deletion mutations with token verification.
 */
final readonly class DeleteAction
{
    /**
     * Dependency Injection handled via constructor property promotion.
     */
    public function __construct(
        private NoticeRepository $repository,
    ) {}

    /**
     * Validates cryptographic tokens and deletes entity entries out of storage.
     */
    public function execute(): void
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