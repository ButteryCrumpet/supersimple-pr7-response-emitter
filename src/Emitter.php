<?php

namespace SuperSimpleResponseEmitter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Emitter
{
    private $chunkSize;
    private $emptyResponse = [204, 205, 304];

    public function __construct($chunkSize = 4096)
    {
        $this->chunkSize = $chunkSize;
    }

    public function emit(ResponseInterface $response)
    {
        if (!headers_sent()) {
            $this->emitHeaders($response->getHeaders());
            $this->sendStatusLine(
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            );
        }
        if (!in_array($response->getStatusCode(), $this->emptyResponse)) {
            $this->emitBody($response->getBody());
        }
        return $response;
    }

    private function emitHeaders($headers)
    {
        foreach ($headers as $name => $values) {
            $first = $name !== 'Set-Cookie';
            foreach ($values as $value) {
                header(\sprintf('%s: %s', $name, $value), $first);
                $first = false;
            }
        }
    }

    private function sendStatusLine($protocol, $code, $reason)
    {
        header(
            sprintf('HTTP/%s %s %s', $protocol, $code, $reason),
            true,
            $code
        );
    }

    private function emitBody(StreamInterface $body)
    {
        if ($body->isSeekable()) {
            $body->rewind();
        }

        if (!$body->isReadable()) {
            echo $body;
            return;
        }

        while (!$body->eof()) {
            echo $body->read($this->chunkSize);
        }
    }
}
