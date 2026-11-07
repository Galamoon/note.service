<?php

declare(strict_types=1);

namespace Galamoon\Notes\Tests\Unit;

use Galamoon\Notes\Helpers\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Galamoon\Notes\Helpers\Sanitizer
 */
final class SanitizerTest extends TestCase
{
    public function testSanitizeBasic(): void
    {
        $result = Sanitizer::sanitize('Hello World');
        $this->assertSame('Hello World', $result);
    }

    public function testSanitizeStripsScriptTags(): void
    {
        $result = Sanitizer::sanitize('<script>alert("xss")</script>');
        // strip_tags removes the tags entirely
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('</script>', $result);
        // The inner content remains
        $this->assertStringContainsString('alert', $result);
    }

    public function testSanitizeStripsImgTags(): void
    {
        $result = Sanitizer::sanitize('<img src=x onerror=alert(1)>');
        $this->assertStringNotContainsString('<img', $result);
    }

    public function testSanitizeHandlesDoubleEncoding(): void
    {
        // htmlspecialchars_decode converts &lt; back to <, then strip_tags removes nothing,
        // but since there's no tag content to preserve as HTML, the < and > get re-encoded.
        // However, standalone &lt; without closing &gt; in a tag context may be stripped by strip_tags.
        $alreadyEncoded = '&lt;script&gt;';
        $result = Sanitizer::sanitize($alreadyEncoded);
        // strip_tags treats &lt;script&gt; as a tag and removes it
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testSanitizeRemovesNullBytes(): void
    {
        $result = Sanitizer::sanitize("Hello\0World");
        $this->assertStringNotContainsString("\0", $result);
        $this->assertSame('HelloWorld', $result);
    }

    public function testSanitizeEncodesHtmlEntities(): void
    {
        $result = Sanitizer::sanitize('Test "quotes" and \'apostrophes\'');
        $this->assertStringContainsString('&quot;', $result);
        // ENT_QUOTES encodes single quotes as &#039; (ENT_HTML5) or &apos; (ENT_XML1/ENT_XHTML)
        // PHP 8.x with ENT_HTML5 encodes ' as &#039;
        $this->assertTrue(
            str_contains($result, '&#039;') || str_contains($result, '&apos;'),
            "Expected apostrophe to be encoded as &#039; or &apos;, got: {$result}"
        );
    }

    public function testSanitizeStripsAhrefTag(): void
    {
        $result = Sanitizer::sanitize('<a href="javascript:alert(1)">click</a>');
        $this->assertStringNotContainsString('<a', $result);
        $this->assertStringContainsString('click', $result);
    }

    public function testSanitizeStripsOnerrorAttribute(): void
    {
        $result = Sanitizer::sanitize('<div onerror="alert(1)">test</div>');
        $this->assertStringNotContainsString('onerror', $result);
    }

    public function testSanitizeEmptyString(): void
    {
        $result = Sanitizer::sanitize('');
        $this->assertSame('', $result);
    }

    public function testSanitizeSpecialCharacters(): void
    {
        $input = '<>&"\'`=/';
        $result = Sanitizer::sanitize($input);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringNotContainsString('>', $result);
        $this->assertStringNotContainsString('"', $result);
    }

    public function testSanitizeUnicode(): void
    {
        $result = Sanitizer::sanitize('Привет мир 你好世界 مرحبا');
        $this->assertSame('Привет мир 你好世界 مرحبا', $result);
    }

    public function testSanitizeNestedTags(): void
    {
        $result = Sanitizer::sanitize('<div><span><script>alert(1)</script></span></div>');
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('<div>', $result);
        $this->assertStringNotContainsString('<span>', $result);
    }

    public function testSanitizePreservesTextInsideTags(): void
    {
        $result = Sanitizer::sanitize('<b>Bold text</b>');
        $this->assertStringNotContainsString('<b>', $result);
        $this->assertStringContainsString('Bold text', $result);
    }

    public function testSanitizeWithMixedContent(): void
    {
        $input = 'Safe text <script>bad</script> more safe text';
        $result = Sanitizer::sanitize($input);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('Safe text', $result);
        $this->assertStringContainsString('more safe text', $result);
    }
}
