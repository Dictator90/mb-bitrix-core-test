<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop\CommerceXml;

use SimpleXMLElement;

final class CommerceXmlParser
{
    private CommerceXmlCodeGenerator $codeGenerator;

    public function __construct(?CommerceXmlCodeGenerator $codeGenerator = null)
    {
        $this->codeGenerator = $codeGenerator ?? new CommerceXmlCodeGenerator();
    }

    /**
     * Parses a CommerceML XML string or file path.
     *
     * @return array{sections: list<CommerceXmlSection>, products: list<CommerceXmlProduct>}
     */
    public function parse(string $xmlContentOrPath): array
    {
        $xml = $this->loadXml($xmlContentOrPath);
        if ($xml === null) {
            return ['sections' => [], 'products' => []];
        }

        $sections = $this->parseSections($xml);
        $products = $this->parseProducts($xml);

        return [
            'sections' => $sections,
            'products' => $products,
        ];
    }

    private function loadXml(string $xmlContentOrPath): ?SimpleXMLElement
    {
        try {
            if (is_file($xmlContentOrPath)) {
                return simplexml_load_file($xmlContentOrPath);
            }

            return simplexml_load_string($xmlContentOrPath);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<CommerceXmlSection>
     */
    private function parseSections(SimpleXMLElement $xml): array
    {
        $sections = [];
        $groups = $xml->xpath('//Группа');
        if ($groups === false) {
            return [];
        }

        foreach ($groups as $group) {
            $id = (string) ($group->Ид ?? '');
            $name = (string) ($group->Наименование ?? '');
            if ($id === '' || $name === '') {
                continue;
            }

            $parent = $group->xpath('..');
            $parentId = null;
            if (is_array($parent) && isset($parent[0])) {
                if ($parent[0]->getName() === 'Группа') {
                    $parentId = (string) ($parent[0]->Ид ?? '');
                } elseif ($parent[0]->getName() === 'Группы') {
                    $grandParent = $parent[0]->xpath('..');
                    if (is_array($grandParent) && isset($grandParent[0]) && $grandParent[0]->getName() === 'Группа') {
                        $parentId = (string) ($grandParent[0]->Ид ?? '');
                    }
                }
            }

            $sections[] = new CommerceXmlSection(
                id: $id,
                name: $name,
                parentId: $parentId !== '' ? $parentId : null,
                code: $this->codeGenerator->generate($name)
            );
        }

        return $sections;
    }

    /**
     * @return list<CommerceXmlProduct>
     */
    private function parseProducts(SimpleXMLElement $xml): array
    {
        $products = [];
        $items = $xml->xpath('//Товар');
        if ($items === false) {
            return [];
        }

        foreach ($items as $item) {
            $id = (string) ($item->Ид ?? '');
            $name = (string) ($item->Наименование ?? '');
            if ($id === '' || $name === '') {
                continue;
            }

            $description = (string) ($item->Описание ?? '');

            $sectionIds = [];
            if (isset($item->Группы->Ид)) {
                foreach ($item->Группы->Ид as $groupId) {
                    $sectionIds[] = (string) $groupId;
                }
            }

            $prices = [];
            if (isset($item->Цены->Цена)) {
                foreach ($item->Цены->Цена as $priceNode) {
                    $amount = (float) ($priceNode->ЦенаЗаЕдиницу ?? 0.0);
                    $currency = (string) ($priceNode->Валюта ?? 'RUB');
                    $priceTypeId = (string) ($priceNode->ИдТипаЦены ?? '');
                    $prices[] = new CommerceXmlPrice(
                        productId: $id,
                        amount: $amount,
                        currency: $currency,
                        priceTypeId: $priceTypeId !== '' ? $priceTypeId : null
                    );
                }
            }

            $products[] = new CommerceXmlProduct(
                id: $id,
                name: $name,
                description: $description !== '' ? $description : null,
                sectionIds: $sectionIds,
                prices: $prices,
                code: $this->codeGenerator->generate($name)
            );
        }

        $offers = $xml->xpath('//Предложение');
        if ($offers !== false) {
            $productPrices = [];
            foreach ($offers as $offer) {
                $prodId = (string) ($offer->Ид ?? '');
                if ($prodId === '') {
                    continue;
                }
                if (isset($offer->Цены->Цена)) {
                    foreach ($offer->Цены->Цена as $priceNode) {
                        $amount = (float) ($priceNode->ЦенаЗаЕдиницу ?? 0.0);
                        $currency = (string) ($priceNode->Валюта ?? 'RUB');
                        $priceTypeId = (string) ($priceNode->ИдТипаЦены ?? '');
                        $productPrices[$prodId][] = new CommerceXmlPrice(
                            productId: $prodId,
                            amount: $amount,
                            currency: $currency,
                            priceTypeId: $priceTypeId !== '' ? $priceTypeId : null
                        );
                    }
                }
            }

            foreach ($products as $i => $product) {
                if (isset($productPrices[$product->id])) {
                    $products[$i] = new CommerceXmlProduct(
                        id: $product->id,
                        name: $product->name,
                        description: $product->description,
                        sectionIds: $product->sectionIds,
                        prices: array_merge($product->prices, $productPrices[$product->id]),
                        code: $product->code
                    );
                }
            }
        }

        return $products;
    }
}
