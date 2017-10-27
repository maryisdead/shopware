<?php declare(strict_types=1);

namespace Shopware\PaymentMethod\Event;

use Shopware\Api\Write\WrittenEvent;

class PaymentMethodShopWrittenEvent extends WrittenEvent
{
    const NAME = 'payment_method_shop.written';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getEntityName(): string
    {
        return 'payment_method_shop';
    }
}
