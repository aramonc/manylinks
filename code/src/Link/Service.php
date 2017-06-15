<?php

namespace Arc\ManyLinks\Link;

use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

class Service
{
    const DB_COLLECTION = 'links';

    /**
     * @var Collection
     */
    private $collection;

    public function __construct(Client $client)
    {
        $this->collection = $client->selectCollection('many-links', self::DB_COLLECTION);
    }

    public function save(Link $link)
    {
        if (!$link->getId()) {
            $result = $this->collection->insertOne($this->linkToDatabaseModel($link));
            $link->setId((string)$result->getInsertedId());
        } else {
            $this->collection->updateOne(
                ['_id' => new ObjectID($link->getId())],
                ['$set' => $this->linkToDatabaseModel($link)]
            );
        }
    }

    public function getLink(string $id): Link
    {
        /** @var BSONDocument $model */
        $model = $this->collection->findOne(['_id' => new ObjectID($id)]);
        return $this->databaseModelToLink($model);
    }

    /**
     * @param string[] $ids
     *
     * @return Link[]
     */
    public function getLinks(array $ids): array
    {
        $objectIds = [];
        foreach ($ids as $id) {
            $objectIds[] = new ObjectID($id);
        }

        $models = $this->collection->find(['_id' => ['$in' => $objectIds]]);

        $links = [];
        foreach ($models as $model) {
            $links[] = $this->databaseModelToLink($model);
        }

        return $links;
    }

    protected function linkToDatabaseModel(Link $link): array
    {
        return [
            'urls' => $link->getUrls(),
            'bitly' => $link->getBitly(),
        ];
    }

    protected function databaseModelToLink(BSONDocument $model): Link
    {
        /** @var BSONArray $urls */
        $urls = $model['urls'];
        $link = new Link();
        $link
            ->setId((string) $model['_id'])
            ->setUrls($urls->getArrayCopy())
            ->setBitly($model['bitly'] ?? '');

        return $link;
    }
}