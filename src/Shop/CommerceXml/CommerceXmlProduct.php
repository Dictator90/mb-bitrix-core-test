<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop\CommerceXml;

final class CommerceXmlProduct
{
    /**
     * @param list<string> $sectionIds
     * @param list<CommerceXmlPrice> $prices
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly array $sectionIds = [],
        public readonly array $prices = [],
        public readonly ?string $code = null
    ) {
    }
}
