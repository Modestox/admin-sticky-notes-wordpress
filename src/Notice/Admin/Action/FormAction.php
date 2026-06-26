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

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractFormAction;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\WpUserDirectory;
use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractForm;
use Modestox\AdminStickyNotes\Shared\Ui\Component\Field;
use Modestox\AdminStickyNotes\Notice\Admin\Form;
use Modestox\AdminStickyNotes\Notice\Domain\Notice;

/**
 * Single Action Controller responsible exclusively for preparing and rendering the entity form.
 */
final readonly class FormAction extends AbstractFormAction
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
    protected function loadEntity(int $id): ?object
    {
        return $this->repository->findById($id);
    }

    /**
     * @inheritDoc
     */
    public function getFormDefinition(): AbstractForm
    {
        return new Form();
    }

    /**
     * @inheritDoc
     * @param Form $formDefinition
     * @return array<int, Field>
     */
    public function getFormFields(object $formDefinition): array
    {
        return $formDefinition->getFields(
            $this->groupRepository->getLookupPairs(),
            $this->wpUserDirectory->getLookupPairs()
        );
    }

    /**
     * @inheritDoc
     * @param Notice $entity
     */
    public function mapEntityToFormData(object $entity): array
    {
        $savedGroups = maybe_unserialize($entity->groupId);

        if (!is_array($savedGroups)) {
            $savedGroups = ['0'];
        } else {
            $savedGroups = array_map('strval', $savedGroups);
        }

        return [
            'title'        => $entity->title,
            'message'      => $entity->message,
            'groupId'      => $savedGroups,
            'targetUserId' => (string)$entity->targetUserId,
            'priority'     => $entity->priority,
            'status'       => $entity->status,
            'startDate'    => $entity->startDate?->format('Y-m-d\TH:i') ?? '',
            'endDate'      => $entity->endDate?->format('Y-m-d\TH:i') ?? '',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEditTitle(): string
    {
        return __('Edit Notice', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function getCreateTitle(): string
    {
        return __('Create Notice', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function getNonceActionName(): string
    {
        return 'save_notice_action';
    }

    /**
     * @inheritDoc
     */
    public function getNonceFieldName(): string
    {
        return 'modestox_nonce';
    }

    /**
     * @inheritDoc
     */
    public function getUpdateLabel(): string
    {
        return __('Update', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function getSaveLabel(): string
    {
        return __('Save', 'modestox-admin-sticky-notes');
    }
}