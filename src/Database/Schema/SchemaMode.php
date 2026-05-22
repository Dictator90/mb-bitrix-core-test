<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Schema;

enum SchemaMode: string
{
    case BASE = 'base';
    case SHOP = 'shop';
}
