<?php declare(strict_types=1);

namespace Shopware\PaymentMethod\Writer\Resource;

use Shopware\Api\Write\Field\FkField;
use Shopware\Api\Write\Field\ReferenceField;
use Shopware\Api\Write\Flag\Required;
use Shopware\Api\Write\WriteResource;
use Shopware\AreaCountry\Writer\Resource\AreaCountryWriteResource;
use Shopware\Context\Struct\TranslationContext;
use Shopware\PaymentMethod\Event\PaymentMethodCountryWrittenEvent;

class PaymentMethodCountryWriteResource extends WriteResource
{
    public function __construct()
    {
        parent::__construct('payment_method_country');

        $this->fields['paymentMethod'] = new ReferenceField('paymentMethodUuid', 'uuid', PaymentMethodWriteResource::class);
        $this->fields['paymentMethodUuid'] = (new FkField('payment_method_uuid', PaymentMethodWriteResource::class, 'uuid'))->setFlags(new Required());
        $this->fields['areaCountry'] = new ReferenceField('areaCountryUuid', 'uuid', AreaCountryWriteResource::class);
        $this->fields['areaCountryUuid'] = (new FkField('area_country_uuid', AreaCountryWriteResource::class, 'uuid'))->setFlags(new Required());
    }

    public function getWriteOrder(): array
    {
        return [
            PaymentMethodWriteResource::class,
            AreaCountryWriteResource::class,
            self::class,
        ];
    }

    public static function createWrittenEvent(array $updates, TranslationContext $context, array $rawData = [], array $errors = []): PaymentMethodCountryWrittenEvent
    {
        $event = new PaymentMethodCountryWrittenEvent($updates[self::class] ?? [], $context, $rawData, $errors);

        unset($updates[self::class]);

        /**
         * @var WriteResource
         * @var string[]      $identifiers
         */
        foreach ($updates as $class => $identifiers) {
            if (!array_key_exists($class, $updates) || count($updates[$class]) === 0) {
                continue;
            }

            $event->addEvent($class::createWrittenEvent($updates, $context));
        }

        return $event;
    }
}
