<?php declare(strict_types=1);

namespace Shopware\Shop\Event;

use Shopware\Api\Write\WrittenEvent;

class ShopFormTranslationWrittenEvent extends WrittenEvent
{
    const NAME = 'shop_form_translation.written';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getEntityName(): string
    {
        return 'shop_form_translation';
    }
}
