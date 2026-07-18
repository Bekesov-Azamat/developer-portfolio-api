# 🧑‍💻 Developer Portfolio API

![Laravel](https://img.shields.io/badge/Laravel-13-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![SQLite](https://img.shields.io/badge/SQLite-ready-0f80cc)
![AI Provider](https://img.shields.io/badge/AI-Groq%20Free%20Tier-f55036)
![AI Architecture](https://img.shields.io/badge/AI-provider--agnostic-7952b3)
![Docker](https://img.shields.io/badge/Docker-ready-2496ed)
![Mail Testing](https://img.shields.io/badge/Mail-Mailpit%20Local%20SMTP-success)
![OpenAPI](https://img.shields.io/badge/OpenAPI-3.1-6ba539)
![Tests](https://img.shields.io/badge/Tests-60%20passed-success)
![Assertions](https://img.shields.io/badge/Assertions-405-success)
![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen)
![Pint](https://img.shields.io/badge/Pint-passing-success)
![Composer Audit](https://img.shields.io/badge/Composer%20Audit-clean-success)

**Backend-ориентированный сервис** для лендинга-портфолио разработчика.

Реализует полноценный **REST API** для обработки обращений, **AI-анализ сообщений**, автоматическую генерацию ответа, email-уведомления, безопасное логирование, health-check, агрегированные метрики и интерактивный frontend.

> Проект реализован как тестовое задание с акцентом на **production-minded архитектуру**, **безопасность**, **AI-интеграцию с graceful fallback** и **полное покрытие тестами**.

Главный рабочий поток:

```text
Frontend
→ HTTP request
→ Validation
→ Normalization
→ Database
→ AI analysis or fallback
→ Owner email
→ User email
→ JSON response
```

---

## 📋 Содержание

- [Скриншоты](#-скриншоты)
- [Что реализовано](#-что-реализовано)
- [Стек технологий](#-стек-технологий)
- [Архитектура](#-архитектура)
- [Структура проекта](#-структура-проекта)
- [API](#-api)
- [AI-интеграция](#-ai-интеграция)
- [Graceful fallback](#-graceful-fallback)
- [Email-уведомления](#-email-уведомления)
- [Health и Metrics](#-health-и-metrics)
- [Безопасность](#-безопасность)
- [Логирование](#-логирование)
- [Frontend](#-frontend)
- [Запуск через Docker](#-запуск-через-docker)
- [Переменные окружения](#-переменные-окружения)
- [Тестирование и качество](#-тестирование-и-качество)
- [Документация API](#-документация-api)
- [AI при разработке](#-использование-ai-при-разработке)
- [Соответствие заданию](#-соответствие-тестовому-заданию)
- [Deployment](#-deployment)

---

## 🖼 Скриншоты

<table>
<tr>
<td width="50%">
<img src="docs/screenshots/1.png" alt="Screenshot 1">
</td>
<td width="50%">
<img src="docs/screenshots/2.png" alt="Screenshot 2">
</td>
</tr>
<tr>
<td width="50%">
<img src="docs/screenshots/3.png" alt="Screenshot 3">
</td>
<td width="50%">
<img src="docs/screenshots/4.png" alt="Screenshot 4">
</td>
</tr>
<tr>
<td width="50%">
<img src="docs/screenshots/5.png" alt="Screenshot 5">
</td>
<td width="50%">
<img src="docs/screenshots/6.png" alt="Screenshot 6">
</td>
</tr>
</table>

---

## ✨ Что реализовано

### Backend API

- REST API на **Laravel 13**;
- endpoint отправки обращения;
- серверная валидация и нормализация входных данных;
- хранение обращений в **SQLite**;
- единый формат API-ответов и ошибок;
- корректные HTTP status codes;
- **Request ID** для трассировки запросов;
- **rate limiting** с HMAC-защитой IP;
- CORS allow-list;
- безопасное файловое логирование без PII.

### AI

- реальная интеграция с **Groq API**;
- **OpenAI-compatible** Chat Completions endpoint;
- один AI-вызов на обращение;
- **structured JSON output** через строгую JSON Schema;
- анализ тональности + числовая оценка;
- классификация типа обращения;
- автоматический ответ на русском языке;
- серверная проверка AI-ответа;
- **graceful fallback** при любой ошибке провайдера;
- сохранение token usage;
- изоляция AI в тестах — реальные вызовы не выполняются.

### Email

- письмо владельцу сайта;
- копия ответа пользователю;
- `Reply-To` с email пользователя;
- **независимая обработка** двух писем — сбой одного не блокирует другое;
- сохранение статуса каждой доставки;
- **Mailpit** для локальной проверки SMTP.

### Observability

- `GET /api/health` с реальной проверкой базы через `SELECT 1`;
- `GET /api/metrics` с агрегированными показателями;
- Request ID в header и JSON response;
- отдельный **daily log** API-запросов;
- безопасные логи — **PII не записывается**.

### Frontend

- адаптивная **Blade**-страница без Node.js;
- **Vanilla JavaScript** + чистый CSS;
- live health status и live метрики;
- рабочая форма с loading state;
- вывод ошибок валидации;
- вывод AI-анализа и автоответа;
- вывод Request ID.

---

## 🛠 Стек технологий

| Область | Технология |
|---|---|
| **Backend** | PHP 8.4, Laravel 13 |
| **Frontend** | Blade, Vanilla JavaScript, CSS |
| **Database** | SQLite |
| **AI provider** | Groq API |
| **AI plan** | GroqCloud Free Tier для демонстрации |
| **AI architecture** | Provider-agnostic через `AiAnalyzer` contract |
| **AI replacement** | Новый infrastructure adapter и DI binding |
| **AI protocol** | OpenAI-compatible Chat Completions |
| **AI model** | `openai/gpt-oss-120b` |
| **Email** | Laravel Mail |
| **Local email testing** | Mailpit — письма не уходят в интернет |
| **Production email** | Любой настроенный Laravel SMTP transport |
| **Infrastructure** | Docker, Docker Compose |
| **API documentation** | OpenAPI 3.1 |
| **API testing** | Postman Collection, cURL |
| **Automated tests** | PHPUnit 12 |
| **Mocking** | Mockery |
| **Static analysis** | Larastan / PHPStan level 7 |
| **Formatting** | Laravel Pint |
| **Dependency security** | Composer Audit |

---

## 🏗 Архитектура

```text
HTTP Request
    ↓
Controller
    ↓
FormRequest
    ↓
DTO
    ↓
Service
    ↓
Contract
    ↓
Repository / Infrastructure Adapter
    ↓
Resource
    ↓
JSON Response
```

### Слои

| Слой | Ответственность |
|---|---|
| `Controller` | HTTP-уровень: получить → вызвать → вернуть. **Бизнес-логики нет** |
| `FormRequest` | Валидация и нормализация входных данных |
| `DTO` | Типизированная передача данных между слоями |
| `Service` | Оркестрация рабочего потока |
| `Contract` | Интерфейсы для внешних зависимостей |
| `Repository` | Работа с данными — создание, обновление, агрегация |
| `Infrastructure` | Адаптеры внешних систем: AI, Mail, DB health |
| `Resource` | Стабильный публичный JSON-контракт |

### Сервисы

- `ContactSubmissionService` — главный поток обращения;
- `ContactMailService` — независимая доставка писем;
- `ContactMetricsService` — агрегированные показатели;
- `HealthCheckService` — проверка приложения и базы;
- `AiAutoResponseValidator` — серверная проверка AI-ответа.

### Контракты

Внешние зависимости скрыты за интерфейсами:

- `AiAnalyzer`;
- `ContactMailSender`;
- `DatabaseHealthChecker`;
- `ContactSubmissionRepository`;
- `ContactMetricsRepository`.

> Это позволяет **заменить AI-провайдера, почтовый транспорт или хранилище** без переписывания бизнес-логики.

---

## 📁 Структура проекта

```text
developer-portfolio-api/
├── app/
│   ├── Contracts/
│   │   ├── Ai/AiAnalyzer.php
│   │   ├── Health/DatabaseHealthChecker.php
│   │   ├── Mail/ContactMailSender.php
│   │   └── Repositories/
│   │       ├── ContactMetricsRepository.php
│   │       └── ContactSubmissionRepository.php
│   ├── Data/
│   │   ├── Ai/AiAnalysisResult.php
│   │   ├── Health/HealthCheckResult.php
│   │   ├── Mail/ContactMailResult.php
│   │   ├── Metrics/ContactMetricsSnapshot.php
│   │   └── ContactSubmissionData.php
│   ├── Enums/
│   │   ├── AiStatus.php
│   │   ├── ContactRequestType.php
│   │   ├── MailStatus.php
│   │   ├── ProcessingStatus.php
│   │   └── Sentiment.php
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── ContactSubmissionController.php
│   │   │   ├── HealthController.php
│   │   │   └── MetricsController.php
│   │   ├── Middleware/
│   │   │   ├── AssignRequestId.php
│   │   │   └── LogApiRequest.php
│   │   ├── Requests/StoreContactSubmissionRequest.php
│   │   └── Resources/ContactSubmissionResource.php
│   ├── Infrastructure/
│   │   ├── Ai/GroqAiAnalyzer.php
│   │   ├── Health/LaravelDatabaseHealthChecker.php
│   │   └── Mail/LaravelContactMailSender.php
│   ├── Models/ContactSubmission.php
│   ├── Repositories/
│   │   ├── EloquentContactMetricsRepository.php
│   │   └── EloquentContactSubmissionRepository.php
│   └── Services/
│       ├── Ai/AiAutoResponseValidator.php
│       ├── ContactMailService.php
│       ├── ContactMetricsService.php
│       ├── ContactSubmissionService.php
│       └── HealthCheckService.php
├── docs/
│   ├── screenshots/
│   ├── postman/developer-portfolio-api.postman_collection.json
│   ├── curl-examples.md
│   └── openapi.json
├── resources/views/
│   ├── emails/contact/
│   │   ├── owner.blade.php
│   │   └── user.blade.php
│   └── portfolio.blade.php
├── public/assets/
│   ├── portfolio.css
│   └── portfolio.js
├── tests/
│   ├── Feature/
│   └── Unit/
├── compose.yaml
├── phpstan.neon.dist
├── phpunit.xml
└── README.md
```

---

## 🌐 API

### Endpoints

| Method | URI | Назначение |
|---|---|---|
| `GET` | `/` | Интерактивное портфолио |
| `GET` | `/api` | Метаданные API |
| `POST` | `/api/contact` | Отправка обращения |
| `GET` | `/api/health` | Проверка приложения и базы |
| `GET` | `/api/metrics` | Агрегированные показатели |

---

### POST `/api/contact`

#### Request

```json
{
  "name": "Иван Петров",
  "phone": "+7 700 123 45 67",
  "email": "ivan@example.com",
  "comment": "Здравствуйте! Нужна разработка Laravel API для внутреннего сервиса."
}
```

#### Полный рабочий цикл

```text
Request
→ Validation → Normalization
→ Save pending submission
→ AI request or fallback → Save AI result
→ Send owner email → Send user email → Save mail statuses
→ Complete submission
→ Return response
```

#### Successful response — `201 Created`

```json
{
  "data": {
    "request_id": "872497bf-ad0c-419f-bbce-9bf31cfab15e",
    "status": "completed",
    "ai_status": "succeeded",
    "analysis": {
      "sentiment": "positive",
      "sentiment_score": 0.6,
      "request_type": "project_inquiry"
    },
    "auto_response": "Здравствуйте! Спасибо за обращение. Я готов обсудить детали проекта.",
    "submitted_at": "2026-07-17T21:45:45.000000Z"
  },
  "success": true,
  "message": "Contact submission accepted."
}
```

> В ответ **намеренно не включаются**: имя, телефон, email, комментарий, IP, User-Agent, AI API key, token usage, stack trace.

---

### Валидация

| Поле | Правила |
|---|---|
| `name` | required, string, min/max длина |
| `phone` | required, допустимые символы телефона |
| `email` | required, валидный email |
| `comment` | required, string, от 10 до 4000 символов |

Дополнительно выполняется нормализация: trim, нормализация пробелов в имени, lowercase email, очистка комментария.

#### Validation error — `422`

```json
{
  "success": false,
  "message": "Validation failed.",
  "request_id": "44984798-5058-4c50-bf1d-f61b9db4822c",
  "errors": {
    "email": ["The email field must be a valid email address."],
    "comment": ["The comment field must be at least 10 characters."]
  }
}
```

### HTTP-коды

| Код | Значение |
|---:|---|
| `200` | Запрос успешно обработан |
| `201` | Обращение создано и обработано |
| `404` | API endpoint не найден |
| `405` | HTTP method не поддерживается |
| `422` | Ошибка валидации |
| `429` | Превышен rate limit |
| `500` | Внутренняя ошибка приложения |
| `503` | База не прошла health-check |

---

## 🤖 AI-интеграция

Используется **Groq API** через OpenAI-compatible endpoint:

```text
POST /chat/completions
Model: openai/gpt-oss-120b
```

### Почему выбран Groq

Для демонстрационной версии проекта используется **GroqCloud Free Tier**.

Это позволяет запустить реальную AI-интеграцию без обязательных расходов и проверить полный рабочий цикл:

```text
Laravel
→ Groq API
→ Structured JSON
→ Domain validation
→ SQLite
→ Email
→ API response
```

Бесплатный тариф имеет ограничения по количеству запросов и токенов. Поэтому **приложение не зависит от постоянной доступности бесплатной квоты**.

Если лимит исчерпан, провайдер недоступен или возвращает ошибку:

```text
Groq unavailable or rate limited
→ AiAnalysisException
→ Safe fallback
→ Save contact submission
→ Continue email delivery
→ Return successful API response
```

> Внешний AI **улучшает** обработку обращения, но не является критической точкой отказа.

---

### Что делает AI

Один запрос — один structured JSON с четырьмя значениями:

```json
{
  "sentiment": "positive",
  "sentiment_score": 0.6,
  "request_type": "project_inquiry",
  "auto_response": "Здравствуйте! Спасибо за обращение. Я готов обсудить детали проекта."
}
```

### Допустимые значения

**Sentiment:** `positive`, `neutral`, `negative`

**Request type:** `project_inquiry`, `job_opportunity`, `consultation`, `collaboration`, `feedback`, `other`

### Structured output

Ответ запрашивается через **строгую JSON Schema**. После получения результат повторно проверяется приложением:

- существование response content;
- корректность JSON;
- допустимые enum-значения;
- числовой score в диапазоне `-1` до `1`;
- корректность token usage;
- максимальная длина ответа.

### Серверная проверка автоответа

`AiAutoResponseValidator` **запрещает** ответы, которые:

- пустые или длиннее 600 символов;
- не начинаются с `Здравствуйте!`;
- написаны от лица команды: «мы», «наш», «наша»;
- содержат «благодарим», «свяжемся», «скоро», «завтра»;
- обещают конкретные сроки;
- упоминают ИИ, AI, нейросеть или языковую модель.

```text
❌ "Здравствуйте! Мы свяжемся с вами в ближайшее время."
```

Такой ответ **не попадает в базу и письма**.

> **Максимум один AI-вызов на обращение** — без автоматических retry. Это исключает дубли, лишнюю задержку и неконтролируемый расход токенов.

---

## 🔄 Graceful fallback

AI **не является критической точкой отказа**.

Fallback срабатывает при:

- отключённом AI или отсутствующем API key;
- timeout или network error;
- HTTP error от провайдера;
- пустом или malformed JSON response;
- недопустимых значениях;
- нарушении серверных правил автоответа.

```text
AI failure
→ AiAnalysisException
→ ContactSubmissionService catches
→ Safe fallback result
→ Save submission
→ Continue mail delivery
→ Return successful API response
```

**Fallback-ответ:**

```text
Спасибо за обращение! Ваше сообщение успешно получено.
Я ознакомлюсь с ним и свяжусь с вами по указанным контактным данным.
```

При fallback в базе сохраняются:

```text
ai_status       = fallback
sentiment       = neutral
sentiment_score = 0
request_type    = other
```

> Обращение **не теряется** ни при каком сценарии отказа AI.

---

## 🔌 Замена AI-провайдера

Архитектура проекта **не привязана напрямую к Groq**.

Основной application service зависит только от контракта:

```php
App\Contracts\Ai\AiAnalyzer
```

Текущая реализация находится в отдельном infrastructure adapter:

```php
App\Infrastructure\Ai\GroqAiAnalyzer
```

Связь контракта с конкретной реализацией настраивается через DI Container:

```php
app/Providers/AppServiceProvider.php
```

Текущий поток:

```text
ContactSubmissionService
→ AiAnalyzer contract
→ GroqAiAnalyzer
→ AiAnalysisResult
```

Для подключения другого провайдера достаточно:

1. создать новый adapter, реализующий `AiAnalyzer`;
2. преобразовать ответ провайдера в `AiAnalysisResult`;
3. изменить binding в `AppServiceProvider`;
4. добавить конфигурацию в `.env` и `config/ai.php`.

При этом **не требуется изменять**: `ContactSubmissionController`, `ContactSubmissionService`, repository, database model, email service, API Resource, frontend и публичный формат API-ответа.

### Подключение AI-агента

За контрактом `AiAnalyzer` можно подключить **AI-агента** вместо обычной language model.

Агент может дополнительно:
- классифицировать обращение;
- использовать внутреннюю базу знаний;
- обращаться к разрешённым инструментам;
- передавать обращение в CRM или систему управления проектами.

При этом агент должен вернуть результат в существующий domain contract `AiAnalysisResult`. Логика инструментов, памяти и orchestration остаётся внутри нового adapter и **не проникает** в HTTP-, database- и mail-слои:

```text
Controller
→ ContactSubmissionService
→ AiAnalyzer
→ AI agent
→ AiAnalysisResult
→ Repository → Email → Response
```

### Provider-agnostic подход

```text
Business logic depends on contract
not on external provider
```

**Groq выбран как удобная бесплатная демонстрационная реализация**, а не как жёсткая зависимость всей системы.

---

## 📧 Email-уведомления

### Письмо владельцу

Содержит: имя, телефон, email, комментарий, Request ID, AI status, sentiment, request type, auto response.

`Reply-To` — email пользователя.

### Письмо пользователю

Содержит: подтверждение, копию исходного сообщения, AI-generated или fallback auto response, Request ID.

### Независимая доставка

**Сбой одного письма не блокирует второе.**

```text
Owner email failed
→ User email is still sent
→ Submission becomes partially_completed
```

| Mail status | Значение |
|---|---|
| `not_attempted` | Не запускалось |
| `pending` | В процессе |
| `sent` | Отправлено |
| `failed` | Ошибка |
| `skipped` | Пропущено |

### Локальная доставка через Mailpit

Для разработки и тестовой демонстрации используется **Mailpit** — локальный SMTP-сервис.

Mailpit принимает письма от Laravel, но **не отправляет их реальным внешним адресатам**.

Это позволяет безопасно проверить оба письма, HTML-содержимое, AI-анализ, auto response, `Reply-To`, Request ID и статусы доставки — **без реального SMTP-аккаунта и отправки в интернет**.

```text
Laravel Mail → SMTP mailpit:1025 → Mailpit inbox → Browser UI
```

Интерфейс Mailpit: `http://localhost:8025`

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=portfolio@local.test
CONTACT_OWNER_EMAIL=owner@local.test
```

### Production SMTP

Для production достаточно изменить mail-конфигурацию окружения:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=production_username
MAIL_PASSWORD=production_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=portfolio@example.com
CONTACT_OWNER_EMAIL=owner@example.com
```

**Бизнес-логика, Mailables и `ContactMailService` при этом не изменяются.** Транспорт выбирается через Laravel Mail configuration:

```text
Local environment  → Mailpit
Production         → Real SMTP provider
```

---

## 💊 Health и Metrics

### GET `/api/health`

```json
{
  "success": true,
  "status": "healthy",
  "version": "1.0.0",
  "checks": {
    "application": { "ok": true },
    "database": { "ok": true }
  }
}
```

База проверяется реальным запросом `SELECT 1`. При недоступности — `503 Service Unavailable`. **Stack trace и credentials в ответ не попадают.**

### GET `/api/metrics`

```json
{
  "success": true,
  "data": {
    "total_submissions": 3,
    "processing": {
      "completed": 2,
      "partially_completed": 1
    },
    "ai": {
      "succeeded": 2,
      "fallback": 1,
      "total_tokens": 920
    },
    "mail": {
      "owner": { "sent": 2, "failed": 1 },
      "user": { "sent": 3 }
    }
  }
}
```

Metrics **не раскрывают** имя, телефон, email, комментарий, IP hash или User-Agent.

---

## 🛡 Безопасность

| Механизм | Описание |
|---|---|
| **Серверная валидация** | FormRequest + нормализация |
| **Rate limiting** | 5 запросов/мин по умолчанию |
| **HMAC IP hashing** | `hash_hmac('sha256', ip, APP_KEY)` — открытый IP не хранится |
| **CORS allow-list** | Только доверенные origins |
| **Безопасные API-errors** | Без stack trace и внутренних деталей |
| **PII-free logs** | Имя, телефон, email, IP не пишутся в лог |
| **Secrets вне Git** | `.env`, SQLite, `vendor` игнорируются |
| **Structured AI output** | Строгая JSON Schema + серверная проверка |
| **AI validator** | Небезопасные ответы не сохраняются |
| **Graceful fallback** | AI недоступен — сервис продолжает работать |
| **UUID protection** | Защита UUID модели от mass assignment |
| **`expose_php = Off`** | Версия PHP не раскрывается |
| **Нет `X-Powered-By`** | Заголовок отсутствует |
| **Token usage скрыт** | Не возвращается пользователю |

**Rate limit при превышении — `429`:**

```json
{
  "success": false,
  "message": "Too many contact submissions. Please try again later.",
  "request_id": "0a30b0d0-c303-48cb-b817-39800dd92bb2"
}
```

---

## 📝 Логирование

Отдельный daily logging channel для API:

```text
storage/logs/api-requests-YYYY-MM-DD.log
```

```env
API_LOG_DAYS=14
```

Пример записи:

```text
api_request {
  "request_id": "8d9768d8-3bef-4c8b-8845-ad87152f18ab",
  "method": "POST",
  "route": "api.contact",
  "status": 201,
  "duration_ms": 312
}
```

В лог **не пишутся**: name, phone, email, comment, raw IP, IP hash, User-Agent, request body, AI API key.

---

## 🖥 Frontend

Frontend встроен в Laravel — **Node.js не нужен**.

| Путь | Описание |
|---|---|
| `/` | Интерактивное портфолио |

Используется: Blade + Vanilla JavaScript + CSS + Fetch API.

Загружает реальные данные из:

```text
GET /api/health   → live статус
GET /api/metrics  → live показатели
POST /api/contact → форма обращения
```

Поддерживается: character counter, loading indicator, validation errors, AI analysis result, fallback response, processing status, Request ID, повторная отправка.

---

## 🐳 Запуск через Docker

### Требования

Только **Git**, **Docker** и **Docker Compose**.

### 1. Клонировать репозиторий

```bash
git clone https://github.com/Bekesov-Azamat/developer-portfolio-api.git
cd developer-portfolio-api
```

### 2. Создать `.env`

```bash
cp .env.example .env
```

### 3. Собрать PHP-контейнер

```bash
docker compose build app
```

### 4. Установить зависимости и настроить

```bash
docker compose run --rm app composer setup
```

Команда: установит Composer dependencies, создаст `.env`, сгенерирует `APP_KEY`, создаст SQLite-файл, запустит migrations.

### 5. Запустить

```bash
docker compose up -d
```

### 6. Открыть

| Сервис | URL |
|---|---|
| Portfolio | `http://localhost:8020` |
| API root | `http://localhost:8020/api` |
| Health | `http://localhost:8020/api/health` |
| Metrics | `http://localhost:8020/api/metrics` |
| Mailpit | `http://localhost:8025` |

### Остановить

```bash
docker compose down
```

---

## ⚙️ Переменные окружения

### Application

```env
APP_NAME="Developer Portfolio API"
APP_VERSION=1.0.0
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8020
```

### AI

По умолчанию AI **отключён**:

```env
AI_ENABLED=false
```

Для включения Groq:

```env
AI_ENABLED=true
AI_PROVIDER=groq
AI_BASE_URL=https://api.groq.com/openai/v1
AI_MODEL=openai/gpt-oss-120b
AI_API_KEY=your_api_key
AI_CONNECT_TIMEOUT=2
AI_TIMEOUT=8
AI_MAX_OUTPUT_TOKENS=250
AI_TEMPERATURE=0.2
```

> При отсутствии ключа приложение автоматически использует **graceful fallback**.

### Email

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS=portfolio@local.test
CONTACT_MAIL_ENABLED=true
CONTACT_OWNER_EMAIL=owner@local.test
```

### Rate limit и логи

```env
CONTACT_RATE_LIMIT_PER_MINUTE=5
API_LOG_LEVEL=info
API_LOG_DAYS=14
```

### CORS

```env
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

---

## 🧪 Тестирование и качество

### Полный quality gate

```bash
docker compose exec app composer quality
```

Последовательно выполняются:

```text
composer validate --strict
composer audit
pint --test
phpstan analyse --memory-limit=512M
php artisan test
```

### Результат

```text
Tests:           60 passed
Assertions:      405
PHPStan level 7: No errors
Pint:            PASS
Composer audit:  No security vulnerability advisories
```

### Что покрыто тестами

| Область | Что проверяется |
|---|---|
| **Domain / Model** | UUID, default statuses, enum casts, factory metadata |
| **Contact API** | Success, нормализация, сохранение, безопасный response, validation, rate limit |
| **AI flow** | Success, disabled, provider failure, unsafe response, graceful fallback |
| **Email** | Оба письма, Reply-To, независимость доставки, owner/user failure |
| **Infrastructure** | Request ID, global 404/405/500, CORS, safe request logs |
| **Observability** | Health (healthy/503), metrics (zero/aggregated), PII-free |
| **Documentation** | OpenAPI vs routes, HTTP methods, enums, Postman structure, no secrets |
| **Frontend** | Рендеринг страницы, CSS/JS подключение, реальные API routes |

### Изоляция тестов

- отдельная SQLite database + `RefreshDatabase`;
- внешний HTTP подменяется через `Http::fake`;
- случайные реальные запросы к AI **запрещены**;
- email подменяется через `Mail::fake`;
- **реальные токены не расходуются**.

### Отдельные команды

```bash
docker compose exec app composer lint      # Pint проверка
docker compose exec app composer format    # Pint форматирование
docker compose exec app composer analyse   # PHPStan
docker compose exec app composer test      # PHPUnit
docker compose exec app composer audit     # Composer Audit
```

---

## 📚 Документация API

### OpenAPI 3.1

```text
docs/openapi.json
```

Документированы все endpoints, request body, response schemas, HTTP headers, validation errors, rate limit errors, enums. **OpenAPI проверяется тестами** на соответствие Laravel routes и PHP enums.

### Postman Collection

```text
docs/postman/developer-portfolio-api.postman_collection.json
```

Содержит 5 запросов с автоматическими Postman tests: service metadata, health, metrics, valid contact, invalid contact.

### cURL примеры

```bash
# Health
curl --silent --include \
  --header "Accept: application/json" \
  http://localhost:8020/api/health

# Metrics
curl --silent --include \
  --header "Accept: application/json" \
  http://localhost:8020/api/metrics

# Contact
curl --silent --include \
  --request POST \
  --header "Accept: application/json" \
  --header "Content-Type: application/json" \
  --data '{
    "name": "Иван Петров",
    "phone": "+7 700 123 45 67",
    "email": "ivan@example.com",
    "comment": "Здравствуйте! Нужна разработка Laravel API для внутреннего сервиса."
  }' \
  http://localhost:8020/api/contact
```

Больше примеров: `docs/curl-examples.md`

---

## 🧠 Использование AI при разработке

AI-ассистент использовался как **инструмент разработки, анализа и ревью** — не как замена ручной проверки и не как автор архитектурных решений.

### Где использовался AI

- обсуждение архитектуры и проектирование слоёв;
- черновики классов, DTO, enum;
- подготовка тестовых сценариев и поиск edge cases;
- проектирование graceful fallback;
- подготовка OpenAPI, Postman Collection, frontend-разметки;
- анализ ошибок PHPUnit и PHPStan;
- анализ security risks;
- подготовка README.

### Примеры промптов

**Архитектура:**
```text
Спроектируй production-minded Laravel API для формы обратной связи.
Контроллер должен быть тонким.
Поток: Controller → FormRequest → DTO → Service → Repository → Resource.
Внешние AI и email интеграции скрыть за контрактами.
Не переусложняй тестовое задание.
```

**AI structured output:**
```text
Реализуй один AI-вызов через Groq OpenAI-compatible API.
AI возвращает structured JSON: sentiment, sentiment_score, request_type, auto_response.
Используй строгую JSON Schema. Без автоматических retry.
```

**AI safety:**
```text
Автоответ на русском. От имени одного разработчика.
Начинать строго с «Здравствуйте!».
Не использовать «мы», «наш», «благодарим», «свяжемся».
Не обещать сроки. Не упоминать AI.
```

**Security:**
```text
Проверь что API не возвращает PII, stack trace, credentials и token usage.
IP нельзя хранить открытым текстом.
API logs: только request_id, method, route, status, duration.
```

### Что выполнялось вручную

- выбор финальной архитектуры;
- адаптация под Laravel 13;
- настройка Docker, SQLite, Groq;
- реальные E2E-запросы и проверка Mailpit;
- исправление PHPUnit failures и PHPStan errors;
- security hardening;
- ревью и чистка репозитория.

Каждая AI-заготовка проходила:

```text
Manual review → Formatting → Static analysis → Automated tests → Live verification
```

---

## ✅ Соответствие тестовому заданию

| Требование | Реализация | Статус |
|---|---|:---:|
| PHP 8.1+ | PHP 8.4 | ✅ |
| Laravel / фреймворк | Laravel 13 | ✅ |
| Composer | `composer.json`, `composer.lock` | ✅ |
| `POST /api/contact` | реализован | ✅ |
| Валидация (имя, телефон, email, комментарий) | FormRequest | ✅ |
| Письмо владельцу | отдельный Mailable | ✅ |
| Копия пользователю | отдельный Mailable | ✅ |
| Корректные HTTP statuses | 200/201/404/405/422/429/500/503 | ✅ |
| Rate limiting | Laravel RateLimiter | ✅ |
| Файловые логи | daily API channel | ✅ |
| AI-функция | анализ + классификация + автоответ | ✅ |
| Реальный AI provider | Groq API | ✅ |
| Graceful fallback | реализован | ✅ |
| `GET /api/health` | приложение + `SELECT 1` | ✅ |
| `GET /api/metrics` | агрегированная статистика | ✅ |
| `.env` и секреты вне Git | реализовано | ✅ |
| Global error handler | единый JSON response | ✅ |
| CORS | allow-list | ✅ |
| OpenAPI / Swagger | OpenAPI 3.1 | ✅ |
| Controllers → Services → Repositories | соблюдено | ✅ |
| GitHub repository | предоставлен | ✅ |
| README | полный документ | ✅ |
| Postman или cURL | оба предоставлены | ✅ |
| Инструкция запуска | Docker Compose | ✅ |
| Frontend | **дополнительная реализация** | ✅ |
| Бесплатный AI для демонстрации | GroqCloud Free Tier с graceful fallback | ✅ |
| Возможность заменить AI | `AiAnalyzer` contract и отдельный adapter | ✅ |
| Возможность подключить AI-агента | через реализацию существующего контракта | ✅ |
| Безопасная локальная проверка почты | Mailpit SMTP sandbox | ✅ |
| Отсутствие реальной отправки в тестах | Mailpit + Laravel Mail fakes | ✅ |
| Возможность production SMTP | через environment configuration | ✅ |

---

## 🚀 Deployment

Публичный production deployment не включён. Проект полностью воспроизводится локально через **Docker Compose**, что допускается условиями тестового задания.

Для production необходимо:

- `APP_ENV=production`, `APP_DEBUG=false`;
- HTTPS и reverse proxy (Nginx);
- production SMTP и CORS origins;
- secrets в защищённом environment storage;
- persistent database storage;
- при росте нагрузки — заменить SQLite на **PostgreSQL** или **MySQL**;
- централизованное логирование и резервное копирование;
- CI/CD pipeline.

---

## 🔗 Репозиторий

```text
https://github.com/Bekesov-Azamat/developer-portfolio-api
```

---

## 👤 Автор

**Azamat Bekesov** — PHP / Laravel Backend Developer

---

## 📄 License

Проект распространяется под лицензией **MIT**.
