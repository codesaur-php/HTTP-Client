<?php

namespace codesaur\Http\Client\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\Response;

/**
 * Response классын unit тест.
 *
 * Энэ тест нь Response обьектын бүх функцүүдийг шалгана:
 * - statusCode, headers, body шинж чанарууд
 * - json() decode функц
 * - isOk(), isError() шалгах функцүүд
 * - getHeader() header хайх функц
 */
class ResponseTest extends TestCase
{
    /**
     * Response обьект зөв үүсэх тест.
     */
    public function testConstructor(): void
    {
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Hello');

        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(['Content-Type' => 'text/html'], $response->headers);
        $this->assertEquals('Hello', $response->body);
    }

    /**
     * JSON body-г decode хийх тест.
     */
    public function testJson(): void
    {
        $data = ['name' => 'codesaur', 'version' => 2];
        $response = new Response(200, [], \json_encode($data));

        $this->assertEquals($data, $response->json());
    }

    /**
     * JSON биш body-г decode хийхэд null буцаах тест.
     */
    public function testJsonReturnsNullForInvalidJson(): void
    {
        $response = new Response(200, [], 'not json');

        $this->assertNull($response->json());
    }

    /**
     * 2xx статус код isOk() true буцаах тест.
     */
    public function testIsOkWithSuccessCode(): void
    {
        $this->assertTrue((new Response(200, [], ''))->isOk());
        $this->assertTrue((new Response(201, [], ''))->isOk());
        $this->assertTrue((new Response(204, [], ''))->isOk());
        $this->assertTrue((new Response(299, [], ''))->isOk());
    }

    /**
     * 2xx биш статус код isOk() false буцаах тест.
     */
    public function testIsOkWithNonSuccessCode(): void
    {
        $this->assertFalse((new Response(301, [], ''))->isOk());
        $this->assertFalse((new Response(404, [], ''))->isOk());
        $this->assertFalse((new Response(500, [], ''))->isOk());
    }

    /**
     * 4xx/5xx статус код isError() true буцаах тест.
     */
    public function testIsErrorWithErrorCode(): void
    {
        $this->assertTrue((new Response(400, [], ''))->isError());
        $this->assertTrue((new Response(401, [], ''))->isError());
        $this->assertTrue((new Response(404, [], ''))->isError());
        $this->assertTrue((new Response(500, [], ''))->isError());
        $this->assertTrue((new Response(503, [], ''))->isError());
    }

    /**
     * 4xx-аас бага статус код isError() false буцаах тест.
     */
    public function testIsErrorWithNonErrorCode(): void
    {
        $this->assertFalse((new Response(200, [], ''))->isError());
        $this->assertFalse((new Response(301, [], ''))->isError());
        $this->assertFalse((new Response(304, [], ''))->isError());
    }

    /**
     * getHeader() функц header олох тест.
     */
    public function testGetHeader(): void
    {
        $response = new Response(200, [
            'Content-Type' => 'application/json',
            'X-Request-Id' => 'abc123'
        ], '');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals('abc123', $response->getHeader('X-Request-Id'));
    }

    /**
     * getHeader() том жижиг үсэг ялгахгүй хайх тест.
     */
    public function testGetHeaderCaseInsensitive(): void
    {
        $response = new Response(200, ['Content-Type' => 'text/html'], '');

        $this->assertEquals('text/html', $response->getHeader('content-type'));
        $this->assertEquals('text/html', $response->getHeader('CONTENT-TYPE'));
    }

    /**
     * getHeader() олдохгүй бол анхдагч утга буцаах тест.
     */
    public function testGetHeaderDefault(): void
    {
        $response = new Response(200, [], '');

        $this->assertNull($response->getHeader('X-Missing'));
        $this->assertEquals('fallback', $response->getHeader('X-Missing', 'fallback'));
    }

    /**
     * Readonly шинж чанаруудыг өөрчлөх боломжгүй тест.
     */
    public function testReadonlyProperties(): void
    {
        $response = new Response(200, [], 'test');

        $reflection = new \ReflectionClass($response);
        $this->assertTrue($reflection->getProperty('statusCode')->isReadOnly());
        $this->assertTrue($reflection->getProperty('headers')->isReadOnly());
        $this->assertTrue($reflection->getProperty('body')->isReadOnly());
    }
}
