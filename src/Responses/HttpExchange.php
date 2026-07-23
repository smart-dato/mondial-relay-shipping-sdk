<?php

namespace SmartDato\MondialRelayShipping\Responses;

class HttpExchange
{
    public function __construct(
        public readonly string $request,
        public readonly ?string $response = null,
        public readonly ?int $status = null,
    ) {}

    public function sanitizedRequest(): string
    {
        $sanitized = preg_replace('/<Password>.*?<\/Password>/s', '<Password>***</Password>', $this->request);

        return $sanitized ?? $this->request;
    }
}
