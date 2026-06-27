<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Admin;

use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractForm;
use Modestox\AdminStickyNotes\Shared\Ui\Component\Field;
use Modestox\AdminStickyNotes\Shared\Ui\Component\FieldOption;

/**
 * Declarative single source of truth form structural layout metadata definition for Groups.
 */
final readonly class Form extends AbstractForm
{
    /**
     * @inheritDoc
     * @param array<int, string> ...$context Expects $wpRolesPairs lookup dictionary at index 0.
     * @return array<int, Field>
     */
    public function getFields(array ...$context): array
    {
        $wpRolesPairs = $context[0] ?? [];
        $rolesOptions = [];

        foreach ($wpRolesPairs as $roleKey => $roleData) {
            $roleName = is_array($roleData) ? ($roleData['name'] ?? $roleKey) : (string)$roleData;
            $rolesOptions[] = new FieldOption((string)$roleKey, (string)$roleName);
        }

        return [
            Field::text('title', __('Group Title', 'modestox-admin-sticky-notes'), true),
            Field::text('slug', __('URL Slug Identifier', 'modestox-admin-sticky-notes'), true),
            Field::multiselect('allowedRoles', __('Allowed User Roles', 'modestox-admin-sticky-notes'), $rolesOptions, false),
            Field::number('sortOrder', __('Sorting Priorities Order', 'modestox-admin-sticky-notes'), false),
        ];
    }
}