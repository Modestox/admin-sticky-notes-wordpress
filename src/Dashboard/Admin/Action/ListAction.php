<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Dashboard\Admin\Action;

use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Notice\Domain\Notice;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;
use Modestox\AdminStickyNotes\Dashboard\Admin\Data\DashboardGroup;

final readonly class ListAction
{
    /**
     * Dependency injection handled via constructor property promotion.
     */
    public function __construct(
        private GroupRepository $groupRepository,
        private NoticeRepository $noticeRepository,
        private DateFactory $dateFactory,
    ) {}

    /**
     * Executes the action to orchestrate and assemble the structured dashboard dataset.
     *
     * @return array<int, array{group: object, notes: array<int, Notice>}>
     */
    public function execute(): array
    {
        $groups = $this->groupRepository->findVisible();
        $notes = $this->noticeRepository->findVisible();

        $activeNotes = $this->filterByDate($notes);
        $groupedNotes = $this->groupNotes($activeNotes);

        return $this->prepareDashboard($groups, $groupedNotes);
    }

    /**
     * Filters out notices that are out of bounds based on current chronological restrictions.
     *
     * @param array<int, Notice> $notes
     * @return array<int, Notice>
     */
    private function filterByDate(array $notes): array
    {
        $now = $this->dateFactory->create('now');
        $filtered = [];

        foreach ($notes as $note) {
            if ($note->startDate !== null && $now < $note->startDate) {
                continue;
            }

            if ($note->endDate !== null && $now > $note->endDate) {
                continue;
            }

            $filtered[] = $note;
        }

        return $filtered;
    }

    /**
     * Groups active notices inside an associative array mapped by target group identifiers.
     *
     * @param array<int, Notice> $notes
     * @return array<string, array<int, Notice>>
     */
    private function groupNotes(array $notes): array
    {
        $groupedNotes = [];

        foreach ($notes as $note) {
            $groupIds = maybe_unserialize($note->groupId);

            if (!is_array($groupIds)) {
                $groupIds = [$groupIds];
            }

            foreach ($groupIds as $id) {
                $groupedNotes[(string)$id][] = $note;
            }
        }

        return $groupedNotes;
    }

    /**
     * Builds the unified dashboard array boundary filtering out empty entries.
     *
     * @param array<int, object> $groups
     * @param array<string, array<int, Notice>> $groupedNotes
     * @return array<int, array{group: object, notes: array<int, Notice>}>
     */
    private function prepareDashboard(array $groups, array $groupedNotes): array
    {
        $data = [];

        if (!empty($groupedNotes['0'])) {
            $data[] = [
                'group' => new DashboardGroup(id: 0, title: 'All Groups'),
                'notes' => $groupedNotes['0'],
            ];
        }

        foreach ($groups as $group) {
            $groupId = (string)$group->id;

            if (empty($groupedNotes[$groupId])) {
                continue;
            }

            $data[] = [
                'group' => $group,
                'notes' => $groupedNotes[$groupId],
            ];
        }

        return $data;
    }
}