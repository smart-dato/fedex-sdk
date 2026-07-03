<?php

namespace SmartDato\FedEx\Support;

class ApiCallRecord
{
    /**
     * Raw request/response snapshot of a single HTTP call, intended for
     * persistence by the consuming application. For multipart requests the
     * request is a JSON representation of the parts (file contents are
     * replaced by their file names).
     */
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly string $request,
        public readonly ?int $status = null,
        public readonly ?string $response = null,
    ) {}
}
