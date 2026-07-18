<?php

namespace Tests\Unit\Services\Ai;

use App\Exceptions\AiAnalysisException;
use App\Services\Ai\AiAutoResponseValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AiAutoResponseValidatorTest extends TestCase
{
    public function test_it_accepts_safe_singular_response(): void
    {
        $validator = new AiAutoResponseValidator;

        $validator->validate(
            'Здравствуйте! Спасибо за обращение. '
            .'Я готов обсудить детали проекта.',
        );

        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unsafeResponses(): array
    {
        return [
            'missing required greeting' => [
                'Спасибо за обращение! Я готов обсудить проект.',
            ],
            'plural developer voice' => [
                'Здравствуйте! Мы изучим ваше обращение.',
            ],
            'plural possessive form' => [
                'Здравствуйте! Наш разработчик изучит обращение.',
            ],
            'time promise' => [
                'Здравствуйте! Я отвечу вам в ближайшее время.',
            ],
            'specific time promise' => [
                'Здравствуйте! Я отвечу в течение часа.',
            ],
            'artificial intelligence mention' => [
                'Здравствуйте! Ответ подготовлен искусственным интеллектом.',
            ],
            'neural network mention' => [
                'Здравствуйте! Этот ответ сформирован нейросетью.',
            ],
        ];
    }

    #[DataProvider('unsafeResponses')]
    public function test_it_rejects_unsafe_response(
        string $response,
    ): void {
        $this->expectException(
            AiAnalysisException::class,
        );

        (new AiAutoResponseValidator)->validate($response);
    }

    public function test_it_rejects_response_longer_than_limit(): void
    {
        $this->expectException(
            AiAnalysisException::class,
        );

        (new AiAutoResponseValidator)->validate(
            'Здравствуйте! '.str_repeat('а', 601),
        );
    }
}
