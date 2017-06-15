<?php

namespace Arc\ManyLinks\Bitly;

use Arc\ManyLinks\Link\Link;

class User
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string[]
     */
    private $links = [];

    public function __construct(string $id, string $apiKey, string $accessToken)
    {
        $this->id = $id;
        $this->apiKey = $apiKey;
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $accessToken
     *
     * @return User
     */
    public function setAccessToken(string $accessToken): User
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param Link[] $links
     *
     * @return User
     */
    public function setLinks(array $links): User
    {
        foreach ($links as $link) {
            $this->addLink($link);
        }
        return $this;
    }

    public function addLink(Link $link): User
    {
        if (!in_array($link->getId(), $this->links)) {
            $this->links[] = $link->getId();
        }

        return $this;
    }
}