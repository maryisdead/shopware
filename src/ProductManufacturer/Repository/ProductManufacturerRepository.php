<?php declare(strict_types=1);

namespace Shopware\ProductManufacturer\Repository;

use Shopware\Context\Struct\TranslationContext;
use Shopware\ProductManufacturer\Event\ProductManufacturerBasicLoadedEvent;
use Shopware\ProductManufacturer\Event\ProductManufacturerWrittenEvent;
use Shopware\ProductManufacturer\Loader\ProductManufacturerBasicLoader;
use Shopware\ProductManufacturer\Searcher\ProductManufacturerSearcher;
use Shopware\ProductManufacturer\Searcher\ProductManufacturerSearchResult;
use Shopware\ProductManufacturer\Struct\ProductManufacturerBasicCollection;
use Shopware\ProductManufacturer\Writer\ProductManufacturerWriter;
use Shopware\Search\AggregationResult;
use Shopware\Search\Criteria;
use Shopware\Search\UuidSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductManufacturerRepository
{
    /**
     * @var ProductManufacturerBasicLoader
     */
    private $basicLoader;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProductManufacturerSearcher
     */
    private $searcher;

    /**
     * @var ProductManufacturerWriter
     */
    private $writer;

    public function __construct(
        ProductManufacturerBasicLoader $basicLoader,
        EventDispatcherInterface $eventDispatcher,
        ProductManufacturerSearcher $searcher,
        ProductManufacturerWriter $writer
    ) {
        $this->basicLoader = $basicLoader;
        $this->eventDispatcher = $eventDispatcher;
        $this->searcher = $searcher;
        $this->writer = $writer;
    }

    public function read(array $uuids, TranslationContext $context): ProductManufacturerBasicCollection
    {
        if (empty($uuids)) {
            return new ProductManufacturerBasicCollection();
        }

        $collection = $this->basicLoader->load($uuids, $context);

        $this->eventDispatcher->dispatch(
            ProductManufacturerBasicLoadedEvent::NAME,
            new ProductManufacturerBasicLoadedEvent($collection, $context)
        );

        return $collection;
    }

    public function search(Criteria $criteria, TranslationContext $context): ProductManufacturerSearchResult
    {
        /** @var ProductManufacturerSearchResult $result */
        $result = $this->searcher->search($criteria, $context);

        $this->eventDispatcher->dispatch(
            ProductManufacturerBasicLoadedEvent::NAME,
            new ProductManufacturerBasicLoadedEvent($result, $context)
        );

        return $result;
    }

    public function searchUuids(Criteria $criteria, TranslationContext $context): UuidSearchResult
    {
        return $this->searcher->searchUuids($criteria, $context);
    }

    public function aggregate(Criteria $criteria, TranslationContext $context): AggregationResult
    {
        $result = $this->searcher->aggregate($criteria, $context);

        return $result;
    }

    public function update(array $data, TranslationContext $context): ProductManufacturerWrittenEvent
    {
        $event = $this->writer->update($data, $context);

        $this->eventDispatcher->dispatch($event::NAME, $event);

        return $event;
    }

    public function upsert(array $data, TranslationContext $context): ProductManufacturerWrittenEvent
    {
        $event = $this->writer->upsert($data, $context);

        $this->eventDispatcher->dispatch($event::NAME, $event);

        return $event;
    }

    public function create(array $data, TranslationContext $context): ProductManufacturerWrittenEvent
    {
        $event = $this->writer->create($data, $context);

        $this->eventDispatcher->dispatch($event::NAME, $event);

        return $event;
    }
}