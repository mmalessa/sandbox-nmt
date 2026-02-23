<?php

declare(strict_types=1);

namespace App\Translator;

interface Translator
{
    public function translate(string $source, string $target, string $phrase): string;
}
