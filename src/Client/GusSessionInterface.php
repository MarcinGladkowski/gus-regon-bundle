<?php

declare(strict_types=1);

namespace GusBundle\Client;

interface GusSessionInterface
{
    public function login(): void;

    public function logout(): void;

    public function ensureLoggedIn(): void;

    public function isLoggedIn(): bool;
}
