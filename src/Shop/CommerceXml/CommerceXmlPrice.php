<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop\CommerceXml;

final class CommerceXmlPrice
{
    public function __construct(
        public readonly string $productId,
        public readonly float $amount,
        public readonly string $currency = 'RUB',
        public readonly ?string $priceTypeId = null
    ) {
    }
}
