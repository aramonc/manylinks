<?php

namespace Arc\ManyLinks\Link;

class Link implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id = null;

    /**
     * @var array
     */
    private $urls = [];

    /**
     * @var string
     */
    private $bitly = '';

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Link
     */
    public function setId(string $id): Link
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param string[] $urls
     *
     * @throws \InvalidArgumentException
     *
     * @return Link
     */
    public function setUrls(array $urls): Link
    {
        foreach ($urls as $url) {
            $this->addUrl($url);
        }
        return $this;
    }

    /**
     * @param string $url
     *
     * @throws \InvalidArgumentException
     *
     * @return Link
     */
    public function addUrl(string $url): Link
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('%s is not a URL', $url));
        }
        $this->urls[] = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getBitly(): string
    {
        return $this->bitly;
    }

    /**
     * @param string $bitly
     *
     * @return Link
     */
    public function setBitly(string $bitly): Link
    {
        $this->bitly = $bitly;
        return $this;
    }

    function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'urls' => $this->urls,
            'bitly' => $this->bitly,
        ];
    }
}