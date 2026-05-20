<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

use RuntimeException;

final class EditionRegistry
{
    /**
     * @return array<string, array{label: string, url: string}>
     */
    public static function all(string $packageRoot): array
    {
        $path = $packageRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'editions.json';
        if (! is_file($path)) {
            throw new RuntimeException('editions.json not found at ' . $path);
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            throw new RuntimeException('Invalid editions.json');
        }

        return $data;
    }

    /**
     * @return array{label: string, url: string}
     */
    public static function get(string $packageRoot, string $edition): array
    {
        $all = self::all($packageRoot);
        if (! isset($all[$edition]) || ! is_array($all[$edition])) {
            throw new RuntimeException(
                'Unknown Bitrix edition "' . $edition . '". Available: ' . implode(', ', array_keys($all))
            );
        }

        $entry = $all[$edition];
        if (! is_string($entry['url'] ?? null)) {
            throw new RuntimeException('Edition "' . $edition . '" has no url');
        }

        return [
            'label' => is_string($entry['label'] ?? null) ? $entry['label'] : $edition,
            'url' => $entry['url'],
        ];
    }
}
