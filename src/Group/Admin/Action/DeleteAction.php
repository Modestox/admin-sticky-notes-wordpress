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

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractDeleteAction;
use Modestox\AdminStickyNotes\Group\Service\GroupService;

/**
 * Administrative action orchestrating safe removal of specific Group records.
 */
final readonly class DeleteAction extends AbstractDeleteAction
{
    /**
     * Dependency Injection wires the domain service strictly via constructor property promotion.
     */
    public function __construct(
        private GroupService $groupService,
    ) {}

    /**
     * @inheritDoc
     */
    protected function performDelete(int $id): void
    {
        try {
            $this->groupService->delete($id);
        } catch (\LogicException $e) {
            wp_die(esc_html($e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getNonceActionName(int $id): string
    {
        return 'delete_group_' . $id;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUrl(): string
    {
        return sprintf('admin.php?page=%s', sanitize_key($_GET['page'] ?? ''));
    }
}