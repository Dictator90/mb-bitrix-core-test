<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop\CommerceXml;

final class CommerceXmlSection
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $parentId = null,
        public readonly ?string $code = null
    ) {
    }
}
