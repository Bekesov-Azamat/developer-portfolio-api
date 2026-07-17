<x-mail::message>
# Новое обращение с портфолио

Получено новое обращение через API контактной формы.

**Идентификатор:** {{ $submission->request_id }}

**Имя:** {{ $submission->name }}

**Телефон:** {{ $submission->phone }}

**Email:** {{ $submission->email }}

**Дата:** {{ $submission->created_at?->format('d.m.Y H:i:s') }}

## Текст обращения

<x-mail::panel>
{{ $submission->comment }}
</x-mail::panel>

## AI-анализ

**Тональность:** {{ $submission->sentiment?->value ?? 'не определена' }}

**Оценка тональности:** {{ $submission->sentiment_score ?? 'не определена' }}

**Тип обращения:** {{ $submission->request_type?->value ?? 'не определён' }}

**Статус AI:** {{ $submission->ai_status->value }}

## Подготовленный автоответ

<x-mail::panel>
{{ $submission->auto_response }}
</x-mail::panel>

На это письмо можно ответить напрямую — получателем ответа будет автор обращения.

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
