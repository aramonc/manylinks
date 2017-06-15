<?php

namespace Arc\ManyLinks\Bitly;

use Arc\ManyLinks\Link\Service as LinkService;
use GuzzleHttp\Client;
use MongoDB\Client as DbClient;
use MongoDB\Collection;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Psr7\build_query;
use MongoDB\Model\BSONDocument;

class Service
{
    const URL_API = 'https://api-ssl.bitly.com';
    const URL_AUTH = 'https://bitly.com/oauth/authorize';

    const DB_COLLECTION = 'bitly_users';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var LinkService
     */
    private $linkService;

    public function __construct(Client $client, string $clientId, string $clientSecret, DbClient $db, LinkService $linkService)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->collection = $db->selectCollection('many-links', self::DB_COLLECTION);
        $this->linkService = $linkService;
    }

    public function getTokenWithCode(string $code, string $redirectUrl): User
    {
        $response = $this->client->post(
            '/oauth/access_token',
            [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'redirect_uri' => $redirectUrl,
                ],
            ]
        );

        $authData = json_decode((string)$response->getBody(), true);

        if (isset($authData['status_code']) && $authData['status_code'] >= 400) {
            throw new \RuntimeException(sprintf('Something went wrong: %s', $authData['status_txt']),
                $authData['status_code']);
        }



        return $this->createUserFromAuth($authData);
    }

    public function buildAuthUrl(string $redirectUrl, array $state): string
    {
        $query = build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUrl,
            'state' => json_encode($state),
        ]);

        return sprintf('%s?%s', self::URL_AUTH, $query);
    }

    public function createUserFromAuth(array $data): User
    {
        $user = $this->getUser($data['login']);

        if (!$user) {
            $user = new User($data['login'], $data['apiKey'], $data['access_token']);
        } else {
            $user->setAccessToken($data['access_token']);
        }

        $this->saveUser($user);

        return $user;
    }

    public function getUser(string $id)
    {
        /** @var BSONDocument|null $model */
        $model = $this->collection->findOne(['_id' => $id]);
        return $this->databaseModelToUser($model);
    }

    public function saveUser(User $user)
    {
        $this->collection->updateOne(
            ['_id' => $user->getId()],
            ['$set' => $this->userToDatabaseModel($user)],
            ['upsert' => true]
        );
    }

    public function getShortUrl(string $url, User $user): string
    {
        $response = $this->client->get('/v3/shorten', [
            'query' => [
                'access_token' => $user->getAccessToken(),
                'longUrl' => $url,
            ],
        ]);

        $data = json_decode((string)$response->getBody(), true);

        if ($data['status_code'] >= 400) {
            throw new \RuntimeException('Something went wrong creating the short url: ' . $data['status_txt']);
        }

        return $data['data']['url'] ?? $url;
    }

    protected function userToDatabaseModel(User $user): array
    {
        return [
            '_id' => $user->getId(),
            'api_key' => $user->getApiKey(),
            'access_token' => $user->getAccessToken(),
            'links' => $user->getLinks(),
        ];
    }

    protected function databaseModelToUser(BSONDocument $model = null)
    {
        if (!$model) {
            return $model;
        }

        $user = new User((string) $model['_id'], $model['api_key'], $model['access_token']);
        $user->setLinks($this->linkService->getLinks($model['links']->getArrayCopy()));

        return $user;
    }
}