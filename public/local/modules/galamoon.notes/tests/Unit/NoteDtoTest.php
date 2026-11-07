<?php

declare(strict_types=1);

namespace Galamoon\Notes\Tests\Unit;

use Galamoon\Notes\Dto\NoteDto;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Galamoon\Notes\Dto\NoteDto
 */
final class NoteDtoTest extends TestCase
{
    public function testFromRowFormatsCorrectly(): void
    {
        $row = [
            'ID' => 42,
            'TITLE' => 'Test Title',
            'TEXT' => 'Test text content',
            'DATE_CREATE' => '2026-11-06 10:00:00',
            'DATE_MODIFY' => '2026-11-06 12:00:00',
            'ACTIVE' => 'Y',
        ];

        $result = NoteDto::fromRow($row);

        $this->assertSame([
            'id' => 42,
            'title' => 'Test Title',
            'text' => 'Test text content',
            'dateCreate' => '2026-11-06 10:00:00',
            'dateModify' => '2026-11-06 12:00:00',
            'active' => 'Y',
        ], $result);
    }

    public function testFromRowConvertsIdToInteger(): void
    {
        $row = [
            'ID' => '123',
            'TITLE' => 'Title',
            'TEXT' => 'Text',
            'DATE_CREATE' => '2026-01-01 00:00:00',
            'DATE_MODIFY' => '2026-01-01 00:00:00',
            'ACTIVE' => 'Y',
        ];

        $result = NoteDto::fromRow($row);

        $this->assertSame(123, $result['id']);
        $this->assertIsInt($result['id']);
    }

    public function testFromRowWithStringDateValues(): void
    {
        $row = [
            'ID' => 1,
            'TITLE' => 'Date Test',
            'TEXT' => 'Testing date formatting',
            'DATE_CREATE' => '2026-05-15 08:30:00',
            'DATE_MODIFY' => '2026-05-15 09:45:00',
            'ACTIVE' => 'N',
        ];

        $result = NoteDto::fromRow($row);

        $this->assertSame('2026-05-15 08:30:00', $result['dateCreate']);
        $this->assertSame('2026-05-15 09:45:00', $result['dateModify']);
        $this->assertSame('N', $result['active']);
    }

    public function testFromRowWithSpecialCharacters(): void
    {
        $row = [
            'ID' => 5,
            'TITLE' => 'Title with & < > " \' chars',
            'TEXT' => 'Text with HTML: <script>alert(1)</script>',
            'DATE_CREATE' => '2026-01-01 00:00:00',
            'DATE_MODIFY' => '2026-01-01 00:00:00',
            'ACTIVE' => 'Y',
        ];

        $result = NoteDto::fromRow($row);

        $this->assertSame('Title with & < > " \' chars', $result['title']);
        $this->assertSame('Text with HTML: <script>alert(1)</script>', $result['text']);
    }

    public function testFromRowWithEmptyValues(): void
    {
        $row = [
            'ID' => 0,
            'TITLE' => '',
            'TEXT' => '',
            'DATE_CREATE' => '2026-01-01 00:00:00',
            'DATE_MODIFY' => '2026-01-01 00:00:00',
            'ACTIVE' => 'Y',
        ];

        $result = NoteDto::fromRow($row);

        $this->assertSame(0, $result['id']);
        $this->assertSame('', $result['title']);
        $this->assertSame('', $result['text']);
    }
}
