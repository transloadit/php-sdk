<?php

declare(strict_types=1);

namespace Transloadit\Model;

use DateTimeInterface;
use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Transloadit\Model\Contracts\AuthInterface;

class Auth implements AuthInterface
{
    /**
     * @Groups("write")
     * @var string
     */
    private $key;

    /**
     * @var string
     * @Ignore()
     */
    private $secret;

    /**
     * @Groups("write")
     * @var DateTimeInterface
     */
    private $expires;

    public function __construct(string $key, string $secret, DateTimeInterface $expires = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->expires = $expires ?? new DateTime('+2 hours');
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return DateTimeInterface
     */
    public function getExpires(): DateTimeInterface
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $params all parameters in json.
     * @return $this|string
     */
    public function generateSignature(string $params): string
    {
        return hash_hmac('sha1', $params, $this->getSecret());
    }
}
