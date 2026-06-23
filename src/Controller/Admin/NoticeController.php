<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Controller\Admin;

use Modestox\AdminStickyNotes\Service\Admin\Ui\AbstractCrudController;
use Modestox\AdminStickyNotes\Repository\Notice\NoticeRepository;
use Modestox\AdminStickyNotes\Model\Notice\Notice;
use Modestox\AdminStickyNotes\Model\Notice\Ui\NoticeGridDefinition;
use Modestox\AdminStickyNotes\Model\Notice\Ui\NoticeFormDefinition;
use Modestox\AdminStickyNotes\Service\Admin\Ui\GridRenderer;
use Modestox\AdminStickyNotes\Service\Admin\Ui\FormRenderer;

/**
 * Concrete routing controller handling the administration lifecycles of notices.
 */
final class NoticeController extends AbstractCrudController
{
    /**
     * Dependency Injection via Constructor Property Promotion.
     * The DI container automatically resolves and injects the NoticeRepository instance.
     */
    public function __construct(
        private NoticeRepository $repository
    ) {}

    /**
     * Compiles grid metadata definitions and flushes standard system list views.
     */
    protected function renderGridAction(): void
    {
        $gridDefinition = new NoticeGridDefinition();
        $notices = $this->repository->findAll();

        $gridData = array_map(static function (Notice $notice) {
            return [
                'id'        => $notice->id,
                'title'     => $notice->message,
                'priority'  => $notice->priority,
                'status'    => $notice->status,
                'createdAt' => $notice->createdAt,
            ];
        }, $notices);

        $renderer = new GridRenderer($gridDefinition->getColumns(), $gridData);
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
                $formData = [
                    'title'    => $notice->title,
                    'message'  => $notice->message,
                    'priority' => $notice->priority,
                    'status'   => $notice->status,
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

        echo '<div class="wrap">';
        echo sprintf(
            '<h1>%s</h1>',
            $id ? esc_html__('Edit Notice', 'modestox-admin-sticky-notes') : esc_html__('Create Notice', 'modestox-admin-sticky-notes'),
        );
        echo sprintf('<form method="post" action="%s">', esc_url($formActionUrl));

        wp_nonce_field('save_notice_action', 'modestox_nonce');

        $renderer->render($formDefinition->getFields(), $formData);

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
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin'));

        $noticeDto = new Notice(
            id: $id,
            title: '',
            message: sanitize_textarea_field($_POST['message'] ?? ''),
            status: sanitize_key($_POST['status'] ?? 'draft'),
            priority: sanitize_key($_POST['priority'] ?? 'normal'),
            authorId: get_current_user_id(),
            allowedRoles: [],
            createdAt: $now,
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

        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_group_' . $id)) {
            wp_die(esc_html__('Security execution verification failed.', 'modestox-admin-sticky-notes'));
        }

        $this->repository->delete($id);

        wp_redirect(admin_url('admin.php?page=modestox-admin-sticky-notes'));
        exit;
    }
}