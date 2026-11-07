<?php

namespace Galamoon\Notes\Services;

use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\Type\DateTime;
use Galamoon\Notes\Dto\NoteDto;
use Galamoon\Notes\Helpers\Sanitizer;
use Galamoon\Notes\Orm\NoteTable;

class NoteService
{
    /**
     * Format a note row into API response array.
     */
    protected static function formatNote(array $row): array
    {
        return NoteDto::fromRow($row);
    }

    /**
     * Normalize request data keys to uppercase (DB field names).
     */
    protected static function normalizeData(array $data): array
    {
        $map = [
            'title' => 'TITLE',
            'text' => 'TEXT',
            'active' => 'ACTIVE',
            'id' => 'ID',
        ];
        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[$map[$key] ?? $key] = $value;
        }
        return $normalized;
    }

    /**
     * List active notes with pagination.
     */
    public static function getList(int $userId, array $params = []): array
    {
        $limit = min((int)($params['limit'] ?? 20), 100);
        $page = max((int)($params['page'] ?? 1), 1);
        $offset = ($page - 1) * $limit;

        $query = NoteTable::query()
            ->setFilter([
                '=USER_ID' => $userId,
                '=ACTIVE' => 'Y',
            ])
            ->setOrder(['DATE_MODIFY' => 'desc'])
            ->setLimit($limit)
            ->setOffset($offset)
            ->setSelect(['ID', 'TITLE', 'TEXT', 'DATE_CREATE', 'DATE_MODIFY', 'ACTIVE']);

        $result = $query->exec();

        // Get total count
        $countQuery = NoteTable::query()
            ->addSelect(new \Bitrix\Main\ORM\Fields\ExpressionField('CNT', 'COUNT(1)'))
            ->setFilter([
                '=USER_ID' => $userId,
                '=ACTIVE' => 'Y',
            ]);
        $countResult = $countQuery->exec()->fetch();
        $totalCount = (int)($countResult['CNT'] ?? 0);

        $notes = [];
        while ($row = $result->fetch()) {
            $notes[] = self::formatNote($row);
        }

        return [
            'items' => $notes,
            'total' => $totalCount,
            'limit' => $limit,
            'page' => $page,
        ];
    }

    /**
     * Get single note by ID, verifying ownership.
     */
    public static function getById(int $noteId, int $userId): ?array
    {
        $result = NoteTable::getList([
            'select' => ['ID', 'TITLE', 'TEXT', 'DATE_CREATE', 'DATE_MODIFY', 'ACTIVE'],
            'filter' => [
                '=ID' => $noteId,
                '=USER_ID' => $userId,
                '=ACTIVE' => 'Y',
            ],
            'limit' => 1,
        ]);

        $row = $result->fetch();
        if (!$row) {
            return null;
        }

        return self::formatNote($row);
    }

    /**
     * Create a new note.
     */
    public static function create(int $userId, array $data): AddResult
    {
        $raw = self::normalizeData($data);
        $title = Sanitizer::sanitize($raw['TITLE'] ?? '');
        $text = Sanitizer::sanitize($raw['TEXT'] ?? '');

        if (empty($title)) {
            $result = new AddResult();
            $result->addError(new Error('Title is required', 'TITLE_REQUIRED'));
            return $result;
        }

        if (empty($text)) {
            $result = new AddResult();
            $result->addError(new Error('Text is required', 'TEXT_REQUIRED'));
            return $result;
        }

        $now = new DateTime();

        $result = NoteTable::add([
            'fields' => [
                'USER_ID' => $userId,
                'TITLE' => $title,
                'TEXT' => $text,
                'DATE_CREATE' => $now,
                'DATE_MODIFY' => $now,
                'ACTIVE' => 'Y',
            ],
        ]);

        return $result;
    }

    /**
     * Update an existing note (partial update).
     */
    public static function update(int $noteId, int $userId, array $data): UpdateResult
    {
        // Verify ownership first
        $existing = self::getById($noteId, $userId);
        if (!$existing) {
            $result = new UpdateResult();
            $result->addError(new Error('Note not found or access denied', 'NOT_FOUND'));
            return $result;
        }

        $raw = self::normalizeData($data);
        $fields = [];

        if (array_key_exists('TITLE', $raw)) {
            $title = Sanitizer::sanitize($raw['TITLE']);
            if (empty($title)) {
                $result = new UpdateResult();
                $result->addError(new Error('Title is required', 'TITLE_REQUIRED'));
                return $result;
            }
            $fields['TITLE'] = $title;
        }

        if (array_key_exists('TEXT', $raw)) {
            $text = Sanitizer::sanitize($raw['TEXT']);
            if (empty($text)) {
                $result = new UpdateResult();
                $result->addError(new Error('Text is required', 'TEXT_REQUIRED'));
                return $result;
            }
            $fields['TEXT'] = $text;
        }

        if (!empty($fields)) {
            $fields['DATE_MODIFY'] = new DateTime();
        }

        return NoteTable::update($noteId, $fields);
    }

    /**
     * Soft delete (set ACTIVE = 'N').
     */
    public static function softDelete(int $noteId, int $userId): UpdateResult
    {
        // Check if note exists and is active
        $existing = self::getById($noteId, $userId);
        if (!$existing) {
            // Already deleted or doesn't exist — idempotent
            $result = new UpdateResult();
            $result->addSuccess();
            return $result;
        }

        return NoteTable::update($noteId, ['ACTIVE' => 'N']);
    }
}
