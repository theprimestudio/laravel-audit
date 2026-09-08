<?php

namespace ThePrimeStudio\Audit\Contracts;

interface NotificationChannel
{
    public function send(string $title, string $message, string $type = 'info'): void;
}
