<?php

declare(strict_types=1);

namespace App\Translator;

class NllbTranslator implements Translator
{
    private const NLLB_LANGUAGES = [
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

    public function __construct(
        private readonly string $endpoint = 'http://nllb-translator:8000/translate',
        private readonly int $maxRetries = 1,
        private readonly int $timeout = 30,
    ) {}

    public function translate(string $source, string $target, string $phrase): string
    {
        $resolvedSource = $this->resolveLanguageCode($source);
        $resolvedTarget = $this->resolveLanguageCode($target);

        if ($resolvedSource === $resolvedTarget) {
            return $phrase;
        }

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $ch = curl_init($this->endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode([
                        'text' => $phrase,
                        'source' => $resolvedSource,
                        'target' => $resolvedTarget,
                    ]),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => $this->timeout,
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
                if ($attempt === $this->maxRetries) {
                    echo "NLLB TRANSLATE {$source}->{$target} ERROR (after {$this->maxRetries} attempts) " . $e->getMessage() . PHP_EOL;
                    return '';
                }
                usleep($attempt * 100_000);
            }
        }
        return '';
    }

    private function resolveLanguageCode(string $isoCode): string
    {
        return self::NLLB_LANGUAGES[$isoCode]
            ?? throw new \InvalidArgumentException("Unsupported language code: {$isoCode}");
    }
}
