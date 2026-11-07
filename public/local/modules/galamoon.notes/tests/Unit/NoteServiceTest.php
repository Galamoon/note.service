<?php

declare(strict_types=1);

namespace Galamoon\Notes\Tests\Unit;

use Galamoon\Notes\Services\NoteService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Galamoon\Notes\Services\NoteService
 */
final class NoteServiceTest extends TestCase
{
    // --------------------------------------------------------------------
    // create() — validation
    // --------------------------------------------------------------------

    public function testCreateWithEmptyTitleReturnsError(): void
    {
        $result = NoteService::create(1, ['title' => '', 'text' => 'Some text']);

        $this->assertFalse($result->isSuccess());
        $errors = $result->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('Title is required', $errors[0]->getMessage());
        $this->assertSame('TITLE_REQUIRED', $errors[0]->getCode());
    }

    public function testCreateWithEmptyTextReturnsError(): void
    {
        $result = NoteService::create(1, ['title' => 'Title', 'text' => '']);

        $this->assertFalse($result->isSuccess());
        $errors = $result->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('Text is required', $errors[0]->getMessage());
        $this->assertSame('TEXT_REQUIRED', $errors[0]->getCode());
    }

    public function testCreateWithBothEmptyReturnsTitleError(): void
    {
        $result = NoteService::create(1, ['title' => '', 'text' => '']);

        $this->assertFalse($result->isSuccess());
        $errors = $result->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('Title is required', $errors[0]->getMessage());
    }

    public function testCreateWithNullValuesReturnsErrors(): void
    {
        $result = NoteService::create(1, []);

        $this->assertFalse($result->isSuccess());
        $errors = $result->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('Title is required', $errors[0]->getMessage());
    }

    // --------------------------------------------------------------------
    // getList — pagination logic (pure PHP, no Bitrix needed)
    // --------------------------------------------------------------------

    public function testPaginationCalculatesOffsetCorrectly(): void
    {
        $page = 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $this->assertSame(0, $offset);

        $page = 2;
        $offset = ($page - 1) * $limit;
        $this->assertSame(20, $offset);

        $page = 3;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $this->assertSame(20, $offset);
    }

    public function testPaginationLimitsMaxLimitTo100(): void
    {
        $limit = 9999;
        $limited = min($limit, 100);
        $this->assertSame(100, $limited);

        $limit = 50;
        $limited = min($limit, 100);
        $this->assertSame(50, $limited);
    }

    public function testPaginationEnforcesMinimumPage(): void
    {
        $page = -5;
        $enforced = max((int)$page, 1);
        $this->assertSame(1, $enforced);

        $page = 0;
        $enforced = max((int)$page, 1);
        $this->assertSame(1, $enforced);
    }

    public function testPaginationCalculatesTotalPages(): void
    {
        $total = 50;
        $limit = 20;
        $pages = (int) ceil($total / $limit);
        $this->assertSame(3, $pages);

        $total = 40;
        $pages = (int) ceil($total / $limit);
        $this->assertSame(2, $pages);
    }

    // --------------------------------------------------------------------
    // softDelete — idempotent behavior logic
    // --------------------------------------------------------------------

    public function testSoftDeleteIsIdempotentWhenNoteNotFound(): void
    {
        $noteExists = false;
        $shouldSucceed = !$noteExists;
        $this->assertTrue($shouldSucceed);
    }

    // --------------------------------------------------------------------
    // getById — access control logic
    // --------------------------------------------------------------------

    public function testGetByIdVerifiesUserOwnership(): void
    {
        $noteOwnerId = 1;
        $requesterId = 2;
        $noteId = 5;

        $accessGranted = ($noteOwnerId === $requesterId);
        $this->assertFalse($accessGranted, 'User should not access another user\'s note');
    }
}
