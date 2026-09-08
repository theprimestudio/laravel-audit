<?php

namespace ThePrimeStudio\Audit\Support;

use ThePrimeStudio\Audit\Contracts\AuditContext;

class AuditContextImpl implements AuditContext
{
    public function __construct(
        protected string $url,
        protected ?\Illuminate\Http\Client\Response $response,
        protected ?\DOMDocument $dom
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getResponse(): ?\Illuminate\Http\Client\Response
    {
        return $this->response;
    }

    public function getDom(): ?\DOMDocument
    {
        return $this->dom;
    }

    public function getCrawler(): mixed
    {
        return null;
    }
}
