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

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractFormAction;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractForm;
use Modestox\AdminStickyNotes\Shared\Ui\Component\Field;
use Modestox\AdminStickyNotes\Group\Admin\Form;
use Modestox\AdminStickyNotes\Group\Domain\Group;

/**
 * Single Action Controller responsible exclusively for preparing and rendering the group management form.
 */
final readonly class FormAction extends AbstractFormAction
{
    /**
     * Injected components wired strictly via PHP 8.3 constructor property promotion.
     */
    public function __construct(
        private GroupRepository $repository,
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
        global $wp_roles;
        return $formDefinition->getFields($wp_roles->roles ?? []);
    }

    /**
     * @inheritDoc
     * @param Group $entity
     */
    public function mapEntityToFormData(object $entity): array
    {
        $savedRoles = maybe_unserialize($entity->allowedRoles);
        if (!is_array($savedRoles)) {
            $savedRoles = [];
        } else {
            $savedRoles = array_map('strval', $savedRoles);
        }

        return [
            'title'        => $entity->title,
            'slug'         => $entity->slug,
            'allowedRoles' => $savedRoles,
            'sortOrder'    => $entity->sortOrder,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEditTitle(): string
    {
        return __('Edit Target Group', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function getCreateTitle(): string
    {
        return __('Create Target Group', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function getNonceActionName(): string
    {
        return 'save_group_action';
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
        return __('Update Group', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    public function getSaveLabel(): string
    {
        return __('Save Group', 'modestox-admin-sticky-notes');
    }
}