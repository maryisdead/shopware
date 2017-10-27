<?php declare(strict_types=1);

namespace Shopware\Product\Extension;

use Shopware\Api\Read\FactoryExtensionInterface;
use Shopware\Api\Search\QueryBuilder;
use Shopware\Api\Search\QuerySelection;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Product\Event\ProductBasicLoadedEvent;
use Shopware\Product\Event\ProductDetailLoadedEvent;
use Shopware\Product\Struct\ProductBasicStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class ProductExtension implements FactoryExtensionInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ProductBasicLoadedEvent::NAME => 'productBasicLoaded',
            ProductDetailLoadedEvent::NAME => 'productDetailLoaded',
        ];
    }

    public function joinDependencies(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
    }

    public function getDetailFields(): array
    {
        return [];
    }

    public function getBasicFields(): array
    {
        return [];
    }

    public function hydrate(
        ProductBasicStruct $product,
        array $data,
        QuerySelection $selection,
        TranslationContext $translation
    ): void {
    }

    public function productBasicLoaded(ProductBasicLoadedEvent $event): void
    {
    }

    public function productDetailLoaded(ProductDetailLoadedEvent $event): void
    {
    }
}
