<?php

declare(strict_types=1);

namespace App;

use MalvikLab\LibreTranslateClient\Client;
use MalvikLab\LibreTranslateClient\DTO\TranslateRequestDTO;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/languages.php';
require __DIR__.'/phrase.php';

error_reporting(E_ALL ^ E_DEPRECATED);


$targetLanguages = array_keys(LANGUAGES);


$libreTranslateClient = new Client('http://libretranslate:5000');

$translations = multitranslate(
    $libreTranslateClient,
    $phrase,
    'pl',
    $targetLanguages
);

echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";


function multitranslate(Client $client, string $phrase, string $source, array $targets, int $concurrency = 4): array
{
    $translations = [];
    foreach ($targets as $target) {
        $translations[$target] = translate($client, $phrase, $source, $target);
    }

    return $translations;
}

function translate(Client $client, string $phrase, string $source, string $target, int $maxRetries = 1): string
{
    if ($source === $target) {
        return $phrase;
    }

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $translation = $client->translate(new TranslateRequestDTO(
                q: $phrase,
                source: $source,
                target: $target,
                alternatives: 1,
            ));
            return $translation->translatedText;
        } catch (\Throwable $e) {
            if ($attempt === $maxRetries) {
                echo "TRANSLATE {$source}->{$target} ERROR (after {$maxRetries} attempts) " . $e->getMessage() . PHP_EOL;
                return '';
            }
            usleep($attempt * 100_000); // 100ms, 200ms, 300ms...
        }
    }
    return '';
}
