<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop\CommerceXml;

final class CommerceXmlCodeGenerator
{
    public function generate(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');

        $cyrillic = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
        ];
        $latin = [
            'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'kh', 'ts', 'ch', 'sh', 'shch', '', 'y', '', 'e', 'yu', 'ya',
        ];
        $slug = str_replace($cyrillic, $latin, $slug);

        $slug = preg_replace('/[^a-z0-9_\-]+/', '-', $slug) ?? $slug;
        $slug = preg_replace('/-+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }
}
