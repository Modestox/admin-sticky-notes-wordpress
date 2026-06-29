<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

/**
 * @var array<int, array{group: \Modestox\AdminStickyNotes\Group\Domain\Group, notes: array}> $data
 */

?>

<div class="wrap dashboard-list">
    <h1><?php echo esc_html(__('Sticky Notes Dashboard', 'modestox-admin-sticky-notes')); ?></h1>

    <?php foreach ($data['data'] as $item): ?>
        <?php $group = $item['group']; ?>
        <div class="postbox dashboard-group">
            <button type="button" class="handlediv" aria-expanded="true">
                <span class="screen-reader-text"><?php _e('Toggle panel'); ?></span>
                <span class="toggle-indicator" aria-hidden="true"></span>
            </button>
            <h2 class="hndle">
                <span>
                    <?php echo esc_html($item['group']->title ?? 'All Groups'); ?>
                    (<?php echo count($item['notes']); ?>)
                </span>
            </h2>

            <div class="inside" style="display: block;">
                <?php if (empty($item['notes'])): ?>
                    <p><?php _e('No notes in this group.'); ?></p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($item['notes'] as $note): ?>
                            <li class="note-<?php echo esc_attr($note->status); ?>">
                                <div class="note-status-wrapper">
                                    <span class="modestox-badge mtx-badge-<?php echo esc_attr($note->status); ?>">
                                        <?php echo esc_html($note->status); ?>
                                    </span>
                                </div>
                                <div class="note-content">
                                    <strong><?php echo esc_html($note->title); ?></strong>
                                    <br>
                                    <?php echo esc_html($note->message); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    jQuery(document).ready(function ($) {
        $('.postbox .handlediv').on('click', function () {
            $(this).parent().find('.inside').toggle();
        });
        $('.dashboard-group .inside li').on('click', function () {
            $(this).toggleClass('expanded');
        });
    });
</script>