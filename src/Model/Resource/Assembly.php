<?php

declare(strict_types=1);

namespace Transloadit\Model\Resource;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Transloadit\Model\Resource\Contracts\AssemblyInterface;
use Transloadit\Model\Resource\Contracts\ResourceFileInterface;

class Assembly extends AbstractResource implements AssemblyInterface
{
    /**
     * @Groups("read")
     * @SerializedName("assembly_id")
     *
     * @var string
     */
    private $id;

    /**
     * @var array
     * @Ignore()
     */
    private $files = [];

    /**
     * @Groups("read")
     * @SerializedName("assembly_url")
     * @var string
     */
    private $url;

    /**
     * @Groups("read")
     * @SerializedName("assembly_ssl_url")
     * @var string
     */
    private $sslUrl;

    /**
     * @Groups("read")
     * @SerializedName("ok")
     * @var string
     */
    private $status;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param  string $id
     * @return AssemblyInterface
     */
    public function setId(string $id): AssemblyInterface
    {
        $this->id = $id;

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function addFilePath(string $filePath): ResourceFileInterface
    {
        array_push($this->files, DataPart::fromPath($filePath));

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param  string $url
     * @return AssemblyInterface
     */
    public function setUrl(string $url): AssemblyInterface
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getSslUrl(): string
    {
        return $this->sslUrl;
    }

    /**
     * @param  string $sslUrl
     * @return AssemblyInterface
     */
    public function setSslUrl(string $sslUrl): AssemblyInterface
    {
        $this->sslUrl = $sslUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param  string $status
     * @return AssemblyInterface
     */
    public function setStatus(string $status): AssemblyInterface
    {
        $this->status = $status;

        return $this;
    }
}
