<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Distantmagic\Resonance\TranslationException\LanguageNotFoundException;
use Distantmagic\Resonance\TranslationException\PhraseNotFoundException;
use Swoole\Http\Request;

readonly class Translator
{
    /**
     * @var Map<SupportedLanguageCodeInterface, Map<string, string>>
     */
    public Map $translations;

    public function __construct(private HttpRequestLanguageDetector $languageDetector)
    {
        $this->translations = new Map();
    }

    public function trans(Request $request, string $phrase): string
    {
        $language = $this->languageDetector->detectPrimaryLanguage($request);

        if (!$this->translations->hasKey($language)) {
            throw new LanguageNotFoundException($language);
        }

        $phrases = $this->translations->get($language);

        if (!$phrases->hasKey($phrase)) {
            throw new PhraseNotFoundException($language, $phrase);
        }

        return $phrases->get($phrase);
    }
}
