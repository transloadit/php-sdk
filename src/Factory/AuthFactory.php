<?php

declare(strict_types=1);

namespace Transloadit\Factory;

use Transloadit\Model\Auth;
use DateTimeInterface;
use Transloadit\Model\Contracts\AuthInterface;

class AuthFactory
{
    public static function create(string $key, string $secret, DateTimeInterface $expires = null): AuthInterface
    {
        return new Auth($key, $secret, $expires);
    }
}
