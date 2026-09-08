<?php

namespace ThePrimeStudio\Audit\Contracts;

interface AuditContext
{
    public function getUrl(): string;
    public function getResponse(): ?\Illuminate\Http\Client\Response;
    public function getDom(): ?\DOMDocument;
    public function getCrawler(): mixed;
}
