<?php

namespace App\Services\Ai;

use App\Exceptions\AiAnalysisException;

final class AiAutoResponseValidator
{
    private const int MAX_LENGTH = 600;

    public function validate(string $response): void
    {
        $response = trim($response);

        if ($response === '') {
            throw new AiAnalysisException(
                'AI auto response cannot be empty.',
            );
        }

        if (mb_strlen($response) > self::MAX_LENGTH) {
            throw new AiAnalysisException(
                'AI auto response exceeds the maximum length.',
            );
        }

        if (! str_starts_with($response, 'Здравствуйте!')) {
            throw new AiAnalysisException(
                'AI auto response must use the required greeting.',
            );
        }

        if ($this->containsPluralDeveloperVoice($response)) {
            throw new AiAnalysisException(
                'AI auto response uses plural developer voice.',
            );
        }

        if ($this->containsTimePromise($response)) {
            throw new AiAnalysisException(
                'AI auto response contains a time promise.',
            );
        }

        if ($this->mentionsArtificialIntelligence($response)) {
            throw new AiAnalysisException(
                'AI auto response mentions artificial intelligence.',
            );
        }
    }

    private function containsPluralDeveloperVoice(
        string $response,
    ): bool {
        return preg_match(
            '/\b(?:'
            .'мы|наш|наша|наше|наши|нашего|нашей|нашему|нашу|'
            .'нашим|нашими|наших|благодарим|свяжемся'
            .')\b/ui',
            $response,
        ) === 1;
    }

    private function containsTimePromise(
        string $response,
    ): bool {
        return preg_match(
            '/(?:'
            .'\bскоро\b|'
            .'в\s+ближайшее\s+время|'
            .'в\s+течение\s+(?:\d+\s*)?'
            .'(?:минут(?:ы)?|час(?:а|ов)?|дн(?:я|ей)|недел(?:и|ь))|'
            .'через\s+\d+\s*'
            .'(?:минут(?:ы)?|час(?:а|ов)?|дн(?:я|ей))|'
            .'\bсегодня\b|'
            .'\bзавтра\b|'
            .'до\s+конца\s+дня'
            .')/ui',
            $response,
        ) === 1;
    }

    private function mentionsArtificialIntelligence(
        string $response,
    ): bool {
        return preg_match(
            '/(?:'
            .'(?<![\p{L}\p{N}_])ии(?![\p{L}\p{N}_])|'
            .'(?<![\p{L}\p{N}_])ai(?![\p{L}\p{N}_])|'
            .'искусственн[а-яё]*\s+интеллект[а-яё]*|'
            .'нейросет[а-яё]*|'
            .'языков[а-яё]*\s+модел[а-яё]*'
            .')/ui',
            $response,
        ) === 1;
    }
}
