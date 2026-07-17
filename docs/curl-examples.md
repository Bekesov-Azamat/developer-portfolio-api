# Developer Portfolio API — cURL Examples

The following commands can be used to manually test the API without Postman.

## Base URL

```text
http://localhost:8020
```

The Docker containers must be running before executing the requests:

```bash
docker compose up -d
```

---

## 1. Service metadata

Returns the service name, API version, and operational status.

```bash
curl \
  --silent \
  --show-error \
  --include \
  --header "Accept: application/json" \
  http://localhost:8020/api
```

Expected HTTP status:

```text
200 OK
```

Example response:

```json
{
  "success": true,
  "data": {
    "service": "Developer Portfolio API",
    "version": "1.0.0",
    "status": "operational"
  }
}
```

The response also contains an `X-Request-ID` header used for request tracing.

---

## 2. Application health

Checks both the Laravel application and the database connection.

```bash
curl \
  --silent \
  --show-error \
  --include \
  --header "Accept: application/json" \
  http://localhost:8020/api/health
```

Expected HTTP status:

```text
200 OK
```

Example response:

```json
{
  "success": true,
  "status": "healthy",
  "version": "1.0.0",
  "checks": {
    "application": {
      "ok": true
    },
    "database": {
      "ok": true
    }
  }
}
```

If the database health check fails, the endpoint returns:

```text
503 Service Unavailable
```

The response does not expose database credentials, exception messages, or stack traces.

---

## 3. Aggregate metrics

Returns aggregate contact-processing statistics.

```bash
curl \
  --silent \
  --show-error \
  --include \
  --header "Accept: application/json" \
  http://localhost:8020/api/metrics
```

Expected HTTP status:

```text
200 OK
```

Example response:

```json
{
  "success": true,
  "data": {
    "total_submissions": 0,
    "processing": {
      "pending": 0,
      "processing": 0,
      "completed": 0,
      "partially_completed": 0,
      "failed": 0
    },
    "ai": {
      "not_attempted": 0,
      "pending": 0,
      "succeeded": 0,
      "fallback": 0,
      "failed": 0,
      "total_tokens": 0
    },
    "mail": {
      "owner": {
        "not_attempted": 0,
        "pending": 0,
        "sent": 0,
        "failed": 0,
        "skipped": 0
      },
      "user": {
        "not_attempted": 0,
        "pending": 0,
        "sent": 0,
        "failed": 0,
        "skipped": 0
      }
    }
  }
}
```

The endpoint returns aggregate counters only.

It does not expose:

- contact names;
- phone numbers;
- email addresses;
- comments;
- IP hashes;
- User-Agent values.

---

## 4. Valid contact submission

This request executes the complete contact-processing flow:

1. validates the submitted data;
2. stores the contact request in SQLite;
3. performs one AI analysis attempt or uses the safe fallback;
4. sends an email to the portfolio owner;
5. sends a response copy to the user;
6. returns the final JSON response.

When `AI_ENABLED=true`, the request may perform one real AI provider call.

When `CONTACT_MAIL_ENABLED=true`, two emails are sent. In the local Docker environment, they are captured by Mailpit:

```text
http://localhost:8025
```

Create a unique test email and submit the request:

```bash
EMAIL="curl-$(date +%s)@example.com"

curl \
  --silent \
  --show-error \
  --include \
  --request POST \
  --header "Accept: application/json" \
  --header "Content-Type: application/json" \
  --data "{
    \"name\": \"cURL Test Client\",
    \"phone\": \"+7 700 000 00 07\",
    \"email\": \"${EMAIL}\",
    \"comment\": \"Здравствуйте! Нужна разработка Laravel API для внутренней системы управления заказами. Хотел бы обсудить архитектуру проекта и возможное сотрудничество.\"
  }" \
  http://localhost:8020/api/contact
```

Expected HTTP status:

```text
201 Created
```

Example response:

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
    "auto_response": "Здравствуйте! Спасибо за ваш запрос. Я готов обсудить детали проекта и уточнить требования.",
    "submitted_at": "2026-07-17T21:45:45.000000Z"
  },
  "success": true,
  "message": "Contact submission accepted."
}
```

Possible processing statuses:

```text
completed
partially_completed
```

Possible AI statuses in a successful API response:

```text
succeeded
fallback
```

A fallback response means the contact request was safely stored and processed even though the AI provider was disabled, unavailable, timed out, or returned an invalid response.

The public response does not return:

- submitted name;
- submitted phone;
- submitted email;
- submitted comment;
- IP hash;
- User-Agent;
- AI provider credentials;
- internal exception details.

---

## 5. Validation error

This request intentionally sends invalid input.

It does not perform an AI request and does not send emails.

```bash
curl \
  --silent \
  --show-error \
  --include \
  --request POST \
  --header "Accept: application/json" \
  --header "Content-Type: application/json" \
  --data '{
    "name": "A",
    "phone": "invalid phone!",
    "email": "not-an-email",
    "comment": "short"
  }' \
  http://localhost:8020/api/contact
```

Expected HTTP status:

```text
422 Unprocessable Entity
```

Example response:

```json
{
  "success": false,
  "message": "Validation failed.",
  "request_id": "44984798-5058-4c50-bf1d-f61b9db4822c",
  "errors": {
    "name": [
      "The name field must be at least 2 characters."
    ],
    "phone": [
      "The phone field format is invalid."
    ],
    "email": [
      "The email field must be a valid email address."
    ],
    "comment": [
      "The comment field must be at least 10 characters."
    ]
  }
}
```

The exact validation messages may depend on the configured Laravel locale, but the JSON structure remains stable.

---

## 6. Request tracing with X-Request-ID

A valid UUID may be supplied through the `X-Request-ID` header:

```bash
curl \
  --silent \
  --show-error \
  --include \
  --header "Accept: application/json" \
  --header "X-Request-ID: 3a767365-4767-494d-9128-374987789254" \
  http://localhost:8020/api/health
```

The same UUID is:

- returned in the `X-Request-ID` response header;
- added to the safe API request log;
- available for troubleshooting and request correlation.

Invalid request IDs are automatically replaced with a generated UUID.

---

## 7. Rate limiting

Repeated contact submissions from the same client may return:

```text
429 Too Many Requests
```

Example error structure:

```json
{
  "success": false,
  "message": "Too many contact submissions. Please try again later.",
  "request_id": "0a30b0d0-c303-48cb-b817-39800dd92bb2"
}
```

The response may also contain:

```text
Retry-After
X-RateLimit-Limit
X-RateLimit-Remaining
```

Rate limiting protects the public contact endpoint from automated abuse.

---

## 8. Mailpit

Local emails can be inspected in the browser:

```text
http://localhost:8025
```

A successful contact request normally produces:

1. an owner notification containing the contact data and AI analysis;
2. a user copy containing the generated or fallback auto-response.

The owner email uses the submitted user email as `Reply-To`.

SMTP credentials are not required for the local Mailpit environment.

---

## 9. OpenAPI specification

The complete OpenAPI 3.1 specification is located at:

```text
docs/openapi.json
```

It documents:

- `GET /api`;
- `POST /api/contact`;
- `GET /api/health`;
- `GET /api/metrics`;
- request schemas;
- response schemas;
- validation errors;
- rate-limit errors;
- health-check failures;
- internal server errors.

---

## 10. Postman Collection

The ready-to-import Postman Collection is located at:

```text
docs/postman/developer-portfolio-api.postman_collection.json
```

The collection contains requests for:

- service metadata;
- application health;
- aggregate metrics;
- valid contact submission;
- invalid contact submission.

The valid contact request may perform one real AI call and send two local Mailpit emails when the corresponding features are enabled.
