<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Service;

use Modestox\AdminStickyNotes\Notice\Domain\Notice;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

/**
 * Domain Service encapsulating strict business logic, validation rules, and guards for Notices.
 */
final readonly class NoticeService
{
    /**
     * Dependency Injection handled via constructor property promotion standard.
     */
    public function __construct(
        private NoticeRepository $repository,
        private DateFactory $dateFactory,
    ) {}

    /**
     * Validates and persists a Notice entity. Throws exceptions on business invariant violations.
     *
     * @throws \InvalidArgumentException If date ranges or content statuses are invalid.
     */
    public function save(Notice $notice): void
    {
        $this->guardValidDates($notice);
        $this->guardPublishableState($notice);

        $this->repository->save($notice);
    }

    /**
     * Safe guards deletion processes based on active publication business rules.
     *
     * @throws \LogicException If an active published notice attempts to be deleted directly.
     */
    public function delete(int $id): void
    {
        $notice = $this->repository->findById($id);
        if ($notice === null) {
            return;
        }

        if ($notice->status === 'published' && $this->isNoticeCurrentlyActive($notice)) {
            throw new \LogicException(
                __('Cannot delete a live active notice. Please switch its status to draft first.', 'modestox-admin-sticky-notes')
            );
        }

        $this->repository->delete($id);
    }

    /**
     * Checks if the notice temporal execution boundaries cover the current runtime state.
     */
    private function isNoticeCurrentlyActive(Notice $notice): bool
    {
        $now = $this->dateFactory->create('now');

        if ($notice->startDate !== null && $now < $notice->startDate) {
            return false;
        }

        if ($notice->endDate !== null && $now > $notice->endDate) {
            return false;
        }

        return true;
    }

    /**
     * Ensures that activation boundaries do not cross expiration limitations.
     */
    private function guardValidDates(Notice $notice): void
    {
        if ($notice->startDate !== null && $notice->endDate !== null) {
            if ($notice->startDate > $notice->endDate) {
                throw new \InvalidArgumentException(
                    __('The activation start date cannot be later than the expiration end date.', 'modestox-admin-sticky-notes')
                );
            }
        }
    }

    /**
     * Prevents empty or malformed staging items from going live into broadcast grids.
     */
    private function guardPublishableState(Notice $notice): void
    {
        if ($notice->status === 'published') {
            if (trim($notice->title) === '') {
                throw new \InvalidArgumentException(
                    __('A notice cannot be published with an empty title.', 'modestox-admin-sticky-notes')
                );
            }

            if (trim($notice->message) === '') {
                throw new \InvalidArgumentException(
                    __('A notice cannot be published with empty message content.', 'modestox-admin-sticky-notes')
                );
            }
        }
    }
}