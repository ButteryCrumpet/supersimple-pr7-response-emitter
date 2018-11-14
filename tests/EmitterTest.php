<?php

namespace SuperSimpleResponseEmitter\Tests;

use PHPUnit\Framework\TestCase;
use SuperSimpleResponseEmitter\Emitter;

function header($value, $replace = true)
{
    \SuperSimpleResponseEmitter\header($value, $replace);
}

class EmitterTest extends TestCase
{
    public function testItInitializes()
    {
        $this->assertInstanceOf(
            Emitter::class,
            new Emitter(4096)
        );
    }

    public function testCorrectOutput()
    {
        $stream = $this->createStreamInterface();
        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->method("getHeaders")
            ->willReturn([
                'Set-Cookie' => [ "true" ],
                'Cache-Control' => ["max-age=3600"],
                'Allow' => ["GET", "HEAD"]
            ]);
        $response
            ->method("getBody")
            ->willReturn($stream);
        $response
            ->method("getProtocolVersion")
            ->willReturn("1.1");
        $response
            ->method("getStatusCode")
            ->willReturn(200);
        $response
            ->method("getReasonPhrase")
            ->willReturn("because");

        $emitter = new Emitter();
        ob_start();
        $emitter->emit($response);
        $result = ob_get_clean();
        $expected = "";
        $expected .= "Set-Cookie: true false\n";
        $expected .= "Cache-Control: max-age=3600 true\n";
        $expected .= "Allow: GET true\n";
        $expected .= "Allow: HEAD false\n";
        $expected .= "HTTP/1.1 200 because true\n";
        $expected .= "go no\n";
        $expected .= "ho";

        $this->assertEquals($expected, $result);
    }

    private function createStreamInterface()
    {
        $body = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $body
            ->method("isSeekable")
            ->willReturn(true);
        $body
            ->method("isReadable")
            ->willReturn(true);
        $body
            ->method("read")
            ->will($this->onConsecutiveCalls("go ", "no\n", "ho"));
        $body
            ->method("eof")
            ->will($this->onConsecutiveCalls(false, false, false, true));
        return $body;
    }
}