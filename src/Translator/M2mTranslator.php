<?php

declare(strict_types=1);

namespace App\Translator;

class M2mTranslator implements Translator
{
    public function __construct(
        private readonly string $endpoint = 'http://m2m-translator:8000/translate',
        private readonly int $maxRetries = 1,
        private readonly int $timeout = 30,
    ) {}

    public function translate(string $source, string $target, string $phrase): string
    {
        if ($source === $target) {
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
                        'source' => $source,
                        'target' => $target,
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
                    echo "M2M TRANSLATE {$source}->{$target} ERROR (after {$this->maxRetries} attempts) " . $e->getMessage() . PHP_EOL;
                    return '';
                }
                usleep($attempt * 100_000);
            }
        }
        return '';
    }
}
