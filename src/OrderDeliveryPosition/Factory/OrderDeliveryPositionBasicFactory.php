<?php declare(strict_types=1);

namespace Shopware\OrderDeliveryPosition\Factory;

use Doctrine\DBAL\Connection;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Factory\ExtensionRegistryInterface;
use Shopware\Framework\Factory\Factory;
use Shopware\OrderDeliveryPosition\Extension\OrderDeliveryPositionExtension;
use Shopware\OrderDeliveryPosition\Struct\OrderDeliveryPositionBasicStruct;
use Shopware\OrderLineItem\Factory\OrderLineItemBasicFactory;
use Shopware\OrderLineItem\Struct\OrderLineItemBasicStruct;
use Shopware\Search\QueryBuilder;
use Shopware\Search\QuerySelection;

class OrderDeliveryPositionBasicFactory extends Factory
{
    const ROOT_NAME = 'order_delivery_position';
    const EXTENSION_NAMESPACE = 'orderDeliveryPosition';

    const FIELDS = [
       'uuid' => 'uuid',
       'orderDeliveryUuid' => 'order_delivery_uuid',
       'orderLineItemUuid' => 'order_line_item_uuid',
       'unitPrice' => 'unit_price',
       'totalPrice' => 'total_price',
       'quantity' => 'quantity',
       'payload' => 'payload',
       'createdAt' => 'created_at',
       'updatedAt' => 'updated_at',
    ];

    /**
     * @var OrderLineItemBasicFactory
     */
    protected $orderLineItemFactory;

    public function __construct(
        Connection $connection,
        ExtensionRegistryInterface $registry,
        OrderLineItemBasicFactory $orderLineItemFactory
    ) {
        parent::__construct($connection, $registry);
        $this->orderLineItemFactory = $orderLineItemFactory;
    }

    public function hydrate(
        array $data,
        OrderDeliveryPositionBasicStruct $orderDeliveryPosition,
        QuerySelection $selection,
        TranslationContext $context
    ): OrderDeliveryPositionBasicStruct {
        $orderDeliveryPosition->setUuid((string) $data[$selection->getField('uuid')]);
        $orderDeliveryPosition->setOrderDeliveryUuid((string) $data[$selection->getField('orderDeliveryUuid')]);
        $orderDeliveryPosition->setOrderLineItemUuid((string) $data[$selection->getField('orderLineItemUuid')]);
        $orderDeliveryPosition->setUnitPrice((float) $data[$selection->getField('unitPrice')]);
        $orderDeliveryPosition->setTotalPrice((float) $data[$selection->getField('totalPrice')]);
        $orderDeliveryPosition->setQuantity((float) $data[$selection->getField('quantity')]);
        $orderDeliveryPosition->setPayload((string) $data[$selection->getField('payload')]);
        $orderDeliveryPosition->setCreatedAt(isset($data[$selection->getField('created_at')]) ? new \DateTime($data[$selection->getField('createdAt')]) : null);
        $orderDeliveryPosition->setUpdatedAt(isset($data[$selection->getField('updated_at')]) ? new \DateTime($data[$selection->getField('updatedAt')]) : null);
        $orderLineItem = $selection->filter('lineItem');
        if ($orderLineItem && !empty($data[$orderLineItem->getField('uuid')])) {
            $orderDeliveryPosition->setLineItem(
                $this->orderLineItemFactory->hydrate($data, new OrderLineItemBasicStruct(), $orderLineItem, $context)
            );
        }

        /** @var $extension OrderDeliveryPositionExtension */
        foreach ($this->getExtensions() as $extension) {
            $extension->hydrate($orderDeliveryPosition, $data, $selection, $context);
        }

        return $orderDeliveryPosition;
    }

    public function getFields(): array
    {
        $fields = array_merge(self::FIELDS, parent::getFields());

        $fields['lineItem'] = $this->orderLineItemFactory->getFields();

        return $fields;
    }

    public function joinDependencies(QuerySelection $selection, QueryBuilder $query, TranslationContext $context): void
    {
        $this->joinLineItem($selection, $query, $context);
        $this->joinTranslation($selection, $query, $context);

        $this->joinExtensionDependencies($selection, $query, $context);
    }

    public function getAllFields(): array
    {
        $fields = array_merge(self::FIELDS, $this->getExtensionFields());
        $fields['lineItem'] = $this->orderLineItemFactory->getAllFields();

        return $fields;
    }

    protected function getRootName(): string
    {
        return self::ROOT_NAME;
    }

    protected function getExtensionNamespace(): string
    {
        return self::EXTENSION_NAMESPACE;
    }

    private function joinLineItem(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if (!($orderLineItem = $selection->filter('lineItem'))) {
            return;
        }
        $query->leftJoin(
            $selection->getRootEscaped(),
            'order_line_item',
            $orderLineItem->getRootEscaped(),
            sprintf('%s.uuid = %s.order_line_item_uuid', $orderLineItem->getRootEscaped(), $selection->getRootEscaped())
        );
        $this->orderLineItemFactory->joinDependencies($orderLineItem, $query, $context);
    }

    private function joinTranslation(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if (!($translation = $selection->filter('translation'))) {
            return;
        }
        $query->leftJoin(
            $selection->getRootEscaped(),
            'order_delivery_position_translation',
            $translation->getRootEscaped(),
            sprintf(
                '%s.order_delivery_position_uuid = %s.uuid AND %s.language_uuid = :languageUuid',
                $translation->getRootEscaped(),
                $selection->getRootEscaped(),
                $translation->getRootEscaped()
            )
        );
        $query->setParameter('languageUuid', $context->getShopUuid());
    }
}