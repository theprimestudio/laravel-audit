<?php

namespace ThePrimeStudio\Audit\Contracts;

interface PermissionResolver
{
    public function resolve($user, string $permission): bool;
}
