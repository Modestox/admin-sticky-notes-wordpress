<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Ui;

if (!class_exists(\WP_List_Table::class)) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use Modestox\AdminStickyNotes\Shared\Ui\Component\Column;

/**
 * Generic structural adapter bridge rendering datasets via standard WordPress table layouts.
 */
final class GridRenderer extends \WP_List_Table
{
    /**
     * Active structural table layout column blueprints specification map.
     *
     * @var array<int, Column>
     */
    private array $columnsSchema;

    /**
     * Total items found in database for current query boundaries.
     */
    private int $totalItemsCount;

    /**
     * Maximum allowed records shown simultaneously inside single slice view.
     */
    private int $perPageLimit;

    /**
     * Map of available groups for filtering context lookup.
     * @var array<int, string>
     */
    private array $groupsLookup;

    /**
     * @var array<string, string>
     */
    private array $statusesLookup;

    /**
     * @var array<string, string>
     */
    private array $prioritiesLookup;

    /**
     * Runtime Constructor mapping entity collections configuration layers.
     *
     * @param array<int, Column> $columnsSchema Blueprint specification maps layout setup.
     * @param array<int, array<string, mixed>> $items Renderable associative grid collection database stack data.
     * @param int $totalItemsCount Total count for calculated pagination limits.
     * @param int $perPageLimit Items count slice constraint per view page.
     */
    public function __construct(
        array $columnsSchema,
        array $items = [],
        int $totalItemsCount = 0,
        int $perPageLimit = 10,
        array $groupsLookup = [],
        array $statusesLookup = [],
        array $prioritiesLookup = [],
    ) {
        parent::__construct([
            'singular' => 'ui_item',
            'plural'   => 'ui_items',
            'ajax'     => false,
        ]);

        $this->columnsSchema = $columnsSchema;
        $this->items = $items;
        $this->totalItemsCount = $totalItemsCount;
        $this->perPageLimit = $perPageLimit;
        $this->groupsLookup = $groupsLookup;
        $this->statusesLookup = $statusesLookup;
        $this->prioritiesLookup = $prioritiesLookup;
    }

    /**
     * Obligatory WordPress core abstraction contract override.
     *
     * @return void
     */
    public function prepare_items(): void
    {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];

        $this->set_pagination_args([
            'total_items' => $this->totalItemsCount,
            'per_page'    => $this->perPageLimit,
            'total_pages' => (int)ceil($this->totalItemsCount / $this->perPageLimit),
        ]);
    }

    /**
     * Returns standard structural list array maps representing columns configuration keys.
     *
     * @return array<string, string>
     */
    public function get_columns(): array
    {
        $columns = [];
        foreach ($this->columnsSchema as $column) {
            $columns[$column->id] = $column->label;
        }
        return $columns;
    }

    /**
     * Defines sortable parameters metadata context schema.
     *
     * @return array<string, array{0: string, 1: bool}>
     */
    protected function get_sortable_columns(): array
    {
        $sortable = [];
        foreach ($this->columnsSchema as $column) {
            if ($column->isSortable) {
                $sortable[$column->id] = [$column->id, false];
            }
        }
        return $sortable;
    }

    /**
     * Fallback macro interceptor parsing specific layout content generation cells dynamically.
     *
     * @param array<string, mixed> $item Single item data row package.
     * @param string $column_name Raw column identifier string key.
     * @return string
     */
    protected function column_default($item, $column_name): string
    {
        $value = $item[$column_name] ?? '';

        if ($value instanceof \DateTimeImmutable) {
            return esc_html($value->format('Y-m-d H:i'));
        }

        if (is_string($value) || is_numeric($value)) {
            return (string)$value;
        }

        return '';
    }

    /**
     * Renders extra filtering controls above and below the data table layout.
     *
     * @param string $which Location context indicator (top|bottom).
     * @return void
     */
    protected function extra_tablenav($which): void
    {
        if ($which !== 'top') {
            return;
        }

        $currentStatus = isset($_GET['filter_status']) ? sanitize_key($_GET['filter_status']) : '';
        $currentPriority = isset($_GET['filter_priority']) ? sanitize_key($_GET['filter_priority']) : '';
        $currentGroup = isset($_GET['filter_group']) ? sanitize_text_field($_GET['filter_group']) : '';
        $searchQuery = isset($_GET['filter_search']) ? sanitize_text_field($_GET['filter_search']) : '';

        echo '<div class="alignleft actions" style="display: flex; gap: 6px; align-items: center; width: 100%; flex-wrap: wrap; margin-bottom: 10px;">';

        echo sprintf(
            '<input type="search" name="filter_search" value="%s" placeholder="%s" style="height: 30px; margin: 0;" />',
            esc_attr($searchQuery),
            esc_attr__('Search notices...', 'modestox-admin-sticky-notes'),
        );

        echo '<select name="filter_group" id="filter_group" style="margin: 0;">';
        echo sprintf('<option value="">%s</option>', esc_html__('All Target Groups', 'modestox-admin-sticky-notes'));
        echo sprintf(
            '<option value="0" %s>%s</option>',
            selected($currentGroup, '0', false),
            esc_html__('— All Groups —', 'modestox-admin-sticky-notes'),
        );
        foreach ($this->groupsLookup as $gId => $gName) {
            if ($gId === 0) {
                continue;
            }
            echo sprintf(
                '<option value="%d" %s>%s</option>',
                $gId,
                selected($currentGroup, (string)$gId, false),
                esc_html($gName),
            );
        }
        echo '</select>';

        if (!empty($this->statusesLookup)) {
            echo '<select name="filter_status" id="filter_status" style="margin: 0;">';
            echo sprintf('<option value="">%s</option>', esc_html__('All Statuses', 'modestox-admin-sticky-notes'));
            foreach ($this->statusesLookup as $val => $label) {
                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($val), selected($currentStatus, $val, false), esc_html($label));
            }
            echo '</select>';
        }

        if (!empty($this->prioritiesLookup)) {
            echo '<select name="filter_priority" id="filter_priority" style="margin: 0;">';
            echo sprintf('<option value="">%s</option>', esc_html__('All Priorities', 'modestox-admin-sticky-notes'));
            foreach ($this->prioritiesLookup as $val => $label) {
                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($val), selected($currentPriority, $val, false), esc_html($label));
            }
            echo '</select>';
        }

        submit_button(__('Filter', 'modestox-admin-sticky-notes'), 'button', 'filter_action', false, ['style' => 'margin: 0;']);

        if (!empty($currentStatus) || !empty($currentPriority) || $currentGroup !== '' || !empty($searchQuery)) {
            $resetUrl = admin_url(sprintf('admin.php?page=%s', sanitize_key($_GET['page'] ?? '')));

            echo sprintf(
                '<a href="%s" class="button button-secondary" style="margin: 0 0 0 4px; display: inline-flex; align-items: center; height: 30px;">%s</a>',
                esc_url($resetUrl),
                esc_html__('Reset', 'modestox-admin-sticky-notes'),
            );
        }

        echo '</div>';
    }
}