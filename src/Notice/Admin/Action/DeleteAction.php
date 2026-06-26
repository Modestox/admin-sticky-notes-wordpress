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

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractDeleteAction;
use Modestox\AdminStickyNotes\Notice\Service\NoticeService;

/**
 * Administrative action orchestrating safe removal of specific Notice records.
 */
final readonly class DeleteAction extends AbstractDeleteAction
{
    /**
     * Dependency Injection wires the domain service strictly via constructor property promotion.
     */
    public function __construct(
        private NoticeService $noticeService,
    ) {}

    /**
     * @inheritDoc
     */
    protected function performDelete(int $id): void
    {
        try {
            $this->noticeService->delete($id);
        } catch (\LogicException $e) {
            wp_die(esc_html($e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getNonceActionName(int $id): string
    {
        return 'delete_notice_' . $id;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUrl(): string
    {
        return sprintf('admin.php?page=%s', sanitize_key($_GET['page'] ?? ''));
    }
}