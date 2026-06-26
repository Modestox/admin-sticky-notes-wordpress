<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure\Wordpress;

/**
 * Infrastructure bridge responsible for querying the native WordPress core user directory layers.
 */
final readonly class WpUserDirectory
{
    /**
     * Compiles a flat map of registered core backend users linking internal IDs to display names.
     *
     * @param int $limit Maximum number of user records to pull from WordPress.
     * @return array<int, string> Associative map of user ID keys to display name strings.
     */
    public function getLookupPairs(int $limit = 500): array
    {
        $users = get_users([
            'fields' => ['ID', 'display_name'],
            'number' => $limit,
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