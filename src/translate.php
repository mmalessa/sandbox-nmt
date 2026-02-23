<?php

declare(strict_types=1);

namespace App;

use App\Translator\LibreTranslateTranslator;
use App\Translator\M2mTranslator;
use App\Translator\NllbTranslator;
require __DIR__.'/../vendor/autoload.php';

$phrase = 'Chłodziarka';
$phrase_lang = 'pl';

$ltTranslator = new LibreTranslateTranslator(endpoint: 'http://libretranslate:5000/translate');
$nllbTranslator = new NllbTranslator(endpoint: 'http://nllb-translator:8000/translate');
$m2mTranslator = new M2mTranslator(endpoint: 'http://m2m-translator:8000/translate');

$translations = [];
foreach (array_keys(Languages::LIST) as $target) {
    $translations[$target]['LT'] = $ltTranslator->translate($phrase_lang, $target, $phrase);
    $translations[$target]['NLLB'] = $nllbTranslator->translate($phrase_lang, $target, $phrase);
    $translations[$target]['M2M'] = $m2mTranslator->translate($phrase_lang, $target, $phrase);
}

echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
