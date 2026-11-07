<?php

namespace Galamoon\Notes\Dto;

use Bitrix\Main\Type\DateTime;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'NoteDto',
    description: 'Note entity'
)]
class NoteDto
{
    #[OAT\Property(property: 'id', type: 'integer', example: 1)]
    public int $id;

    #[OAT\Property(property: 'title', type: 'string', maxLength: 255, example: 'Meeting notes')]
    public string $title;

    #[OAT\Property(property: 'text', type: 'string', example: 'Discuss project timeline')]
    public string $text;

    #[OAT\Property(property: 'dateCreate', type: 'string', format: 'date-time', example: '2026-11-06 10:00:00')]
    public string $dateCreate;

    #[OAT\Property(property: 'dateModify', type: 'string', format: 'date-time', example: '2026-11-06 12:00:00')]
    public string $dateModify;

    #[OAT\Property(property: 'active', type: 'string', enum: ['Y', 'N'], example: 'Y')]
    public string $active;

    /**
     * Format a DB row into API response array.
     */
    public static function fromRow(array $row): array
    {
        return [
            'id' => (int)$row['ID'],
            'title' => $row['TITLE'],
            'text' => $row['TEXT'],
            'dateCreate' => self::formatDate($row['DATE_CREATE']),
            'dateModify' => self::formatDate($row['DATE_MODIFY']),
            'active' => $row['ACTIVE'],
        ];
    }

    /**
     * Format DateTime field to string.
     */
    protected static function formatDate(mixed $value): string
    {
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return (string)$value;
    }
}
