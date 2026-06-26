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

use Modestox\AdminStickyNotes\Shared\Crud\Action\AbstractSaveAction;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Notice\Service\NoticeService;
use Modestox\AdminStickyNotes\Notice\Domain\Notice;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

/**
 * Administrative action managing secure assembly and persistence of structural Notice payloads.
 */
final readonly class SaveAction extends AbstractSaveAction
{
    /**
     * Injected components wired strictly via PHP 8.3 constructor property promotion.
     */
    public function __construct(
        private NoticeRepository $repository,
        private NoticeService $noticeService,
        private DateFactory $dateFactory,
    ) {}

    /**
     * @inheritDoc
     */
    protected function loadExistingEntity(?int $id): ?Notice
    {
        if ($id === null) {
            return null;
        }
        return $this->repository->findById($id);
    }

    /**
     * @inheritDoc
     * @param Notice $entity
     */
    protected function persist(object $entity): void
    {
        try {
            $this->noticeService->save($entity);
        } catch (\InvalidArgumentException $e) {
            wp_die(esc_html($e->getMessage()));
        }
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
    public function getRedirectUrl(): string
    {
        return sprintf('admin.php?page=%s', sanitize_key($_GET['page'] ?? ''));
    }

    /**
     * @inheritDoc
     * @param Notice|null $existingEntity
     * @return Notice
     */
    public function buildEntityFromRequest(array $requestData, ?object $existingEntity): Notice
    {
        $groupRaw = $requestData['groupId'] ?? [0];
        $groupIds = is_array($groupRaw) ? array_map('intval', $groupRaw) : [0];
        if (empty($groupIds)) {
            $groupIds = [0];
        }

        return new Notice(
            id: $existingEntity?->id,
            groupId: maybe_serialize($groupIds),
            userId: $existingEntity?->userId ?? (int)get_current_user_id(),
            targetUserId: isset($requestData['targetUserId']) ? (int)$requestData['targetUserId'] : 0,
            title: isset($requestData['title']) ? sanitize_text_field($requestData['title']) : '',
            message: isset($requestData['message']) ? wp_kses_post($requestData['message']) : '',
            status: isset($requestData['status']) ? sanitize_key($requestData['status']) : 'draft',
            priority: isset($requestData['priority']) ? sanitize_key($requestData['priority']) : 'normal',
            startDate: !empty($requestData['startDate']) ? $this->dateFactory->create($requestData['startDate']) : null,
            endDate: !empty($requestData['endDate']) ? $this->dateFactory->create($requestData['endDate']) : null,
            createdAt: $existingEntity?->createdAt ?? $this->dateFactory->create('now'),
            updatedAt: $this->dateFactory->create('now'),
        );
    }
}