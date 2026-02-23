<?php

declare(strict_types=1);

namespace App;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/languages.php';
require __DIR__.'/phrase.php';

const NLLB_LANGUAGES = [
    'en' => 'eng_Latn',
    'pl' => 'pol_Latn',
    'cs' => 'ces_Latn',
    'de' => 'deu_Latn',
    'es' => 'spa_Latn',
    'fr' => 'fra_Latn',
    'hr' => 'hrv_Latn',
    'hu' => 'hun_Latn',
    'nl' => 'nld_Latn',
    'ro' => 'ron_Latn',
    'sk' => 'slk_Latn',
    'sr' => 'srp_Cyrl',
    'sv' => 'swe_Latn',
    'ru' => 'rus_Cyrl',
    'uk' => 'ukr_Cyrl',
    'it' => 'ita_Latn',
];

$sourceCode = 'pol_Latn';
$targetLanguages = NLLB_LANGUAGES;

$translations = multitranslate($phrase, $sourceCode, $targetLanguages);

echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";


function multitranslate(string $phrase, string $source, array $targets): array
{
    $translations = [];
    foreach ($targets as $key => $nllbCode) {
        $translations[$key] = translate($phrase, $source, $nllbCode);
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
            $ch = curl_init('http://nllb-translator:8000/translate');
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
