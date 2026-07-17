<x-mail::message>
# Ваше обращение получено

{{ $submission->auto_response }}

## Копия обращения

**Идентификатор:** {{ $submission->request_id }}

**Имя:** {{ $submission->name }}

**Телефон:** {{ $submission->phone }}

**Email:** {{ $submission->email }}

<x-mail::panel>
{{ $submission->comment }}
</x-mail::panel>

Пожалуйста, сохраните идентификатор обращения для дальнейшей переписки.

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
