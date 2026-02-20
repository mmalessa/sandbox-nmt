<?php

declare(strict_types=1);

namespace App;

use MalvikLab\LibreTranslateClient\Client;
use MalvikLab\LibreTranslateClient\DTO\TranslateRequestDTO;
use OpenAI\Factory;

require __DIR__.'/../vendor/autoload.php';
error_reporting(E_ALL ^ E_DEPRECATED);


$phrase = "snopowiązałka";

$libreTranslateClient = new Client('http://libretranslate:5000');

// TODO: get target from: http://localhost:5000/languages
$translations = multitranslate(
    $libreTranslateClient,
    $phrase,
    'pl',
    ['en' ,'pl' ,'cs' ,'de' ,'es', 'fr' ,'hu' ,'nl' ,'ro' ,'sk' ,'sv' ,'ru' ,'uk' ,'it']
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

function translate(Client $client, string $phrase, string $source, string $target, int $maxRetries = 3): string
{
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
