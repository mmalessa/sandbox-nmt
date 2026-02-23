<?php

declare(strict_types=1);

namespace App;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/languages.php';
require __DIR__.'/phrase.php';

$sourceCode = 'pl';
$targetLanguages = array_keys(LANGUAGES);

$translations = multitranslate($phrase, $sourceCode, $targetLanguages);

echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";


function multitranslate(string $phrase, string $source, array $targets): array
{
    $translations = [];
    foreach ($targets as $target) {
        $translations[$target] = translate($phrase, $source, $target);
    }

    return $translations;
}

function translate(string $phrase, string $source, string $target, int $maxRetries = 1): string
{
    if ($source === $target) {
        return $phrase;
    }

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $ch = curl_init('http://m2m-translator:8000/translate');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode([
                    'text' => $phrase,
                    'source' => $source,
                    'target' => $target,
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            if ($response === false) {
                throw new \RuntimeException("cURL error: {$error}");
            }

            if ($httpCode !== 200) {
                throw new \RuntimeException("HTTP {$httpCode}: {$response}");
            }

            $data = json_decode($response, true);
            return $data['translatedText'] ?? '';
        } catch (\Throwable $e) {
            if ($attempt === $maxRetries) {
                echo "TRANSLATE {$source}->{$target} ERROR (after {$maxRetries} attempts) " . $e->getMessage() . PHP_EOL;
                return '';
            }
            usleep($attempt * 100_000);
        }
    }
    return '';
}
