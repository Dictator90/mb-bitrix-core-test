<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop;

final class ShopModuleSet
{
    /**
     * @return list<string>
     */
    public static function getRequiredModules(): array
    {
        return [
            'currency',
            'catalog',
            'sale',
        ];
    }
}
