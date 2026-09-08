<?php

namespace ThePrimeStudio\Audit\Contracts;

interface AuditCheck
{
    public function name(): string;
    public function category(): string;
    public function run(AuditContext $context): array;
}
