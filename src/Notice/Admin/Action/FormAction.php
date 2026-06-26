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
use Modestox\AdminStickyNotes\Notice\Admin\Form;
use Modestox\AdminStickyNotes\Shared\Ui\FormRenderer;

/**
 * Single Action Controller responsible exclusively for preparing and rendering the entity form.
 */
final readonly class FormAction
{
    /**
     * Dependency Injection handled via constructor property promotion.
     */
    public function __construct(
        private NoticeRepository $repository,
        private GroupRepository $groupRepository,
    ) {}

    /**
     * Hydrates selected models and maps field configurations into standard form views.
     */
    public function execute(): void
    {
        $formDefinition = new Form();
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
                    'title'        => $notice->title,
                    'message'      => $notice->message,
                    'groupId'      => $savedGroups,
                    'targetUserId' => (string)$notice->targetUserId,
                    'priority'     => $notice->priority,
                    'status'       => $notice->status,
                    'startDate'    => $notice->startDate?->format('Y-m-d\TH:i') ?? '',
                    'endDate'      => $notice->endDate?->format('Y-m-d\TH:i') ?? '',
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
        $availableUsers = $this->getWpUsersLookup();

        echo '<div class="wrap">';
        echo sprintf(
            '<h1>%s</h1>',
            $id ? esc_html__('Edit Notice', 'modestox-admin-sticky-notes') : esc_html__('Create Notice', 'modestox-admin-sticky-notes'),
        );
        echo sprintf('<form method="post" action="%s">', esc_url($formActionUrl));

        wp_nonce_field('save_notice_action', 'modestox_nonce');

        $renderer->render($formDefinition->getFields($availableGroups, $availableUsers), $formData);

        submit_button($id ? __('Update', 'modestox-admin-sticky-notes') : __('Save', 'modestox-admin-sticky-notes'));
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