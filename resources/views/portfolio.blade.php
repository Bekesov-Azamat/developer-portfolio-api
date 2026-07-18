<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="Backend-разработчик PHP и Laravel. Разработка API, интеграций, сервисов и бизнес-логики."
    >

    <title>Backend PHP / Laravel Developer</title>

    <link
        rel="stylesheet"
        href="{{ asset('assets/portfolio.css') }}"
    >

    <script
        src="{{ asset('assets/portfolio.js') }}"
        defer
    ></script>
</head>

<body>
    <header class="site-header">
        <div class="container navigation">
            <a class="brand" href="#top">
                <span class="brand-mark">AB</span>

                <span class="brand-copy">
                    <strong>Backend Developer</strong>
                    <small>PHP · Laravel · API</small>
                </span>
            </a>

            <nav class="nav-links" aria-label="Основная навигация">
                <a href="#capabilities">Возможности</a>
                <a href="#observability">Статус API</a>
                <a href="#contact">Связаться</a>
            </nav>

            <a
                class="button button-small button-outline"
                href="/api"
                target="_blank"
                rel="noopener noreferrer"
            >
                Открыть API
            </a>
        </div>
    </header>

    <main id="top">
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <div class="eyebrow">
                        <span class="pulse-dot"></span>
                        Открыт к предложениям и проектам
                    </div>

                    <h1>
                        Создаю надёжные
                        <span>backend-системы</span>
                        на PHP и Laravel
                    </h1>

                    <p class="hero-description">
                        Проектирую REST API, бизнес-логику,
                        интеграции, обработку ошибок и сервисы,
                        которые удобно поддерживать и развивать.
                    </p>

                    <div class="hero-actions">
                        <a
                            class="button button-primary"
                            href="#contact"
                        >
                            Обсудить проект
                        </a>

                        <a
                            class="button button-secondary"
                            href="#observability"
                        >
                            Посмотреть API в работе
                        </a>
                    </div>

                    <div class="hero-stack" aria-label="Технологии">
                        <span>PHP 8.4</span>
                        <span>Laravel</span>
                        <span>REST API</span>
                        <span>SQLite</span>
                        <span>Docker</span>
                        <span>AI Integration</span>
                    </div>
                </div>

                <aside class="hero-console" aria-label="Статус системы">
                    <div class="console-header">
                        <span class="console-dot"></span>
                        <span class="console-dot"></span>
                        <span class="console-dot"></span>

                        <span class="console-title">
                            developer-portfolio-api
                        </span>
                    </div>

                    <div class="console-body">
                        <div class="console-line">
                            <span class="console-prefix">$</span>
                            <span>GET /api/health</span>
                        </div>

                        <div class="console-result">
                            <span>application</span>
                            <strong id="hero-application-status">
                                checking
                            </strong>
                        </div>

                        <div class="console-result">
                            <span>database</span>
                            <strong id="hero-database-status">
                                checking
                            </strong>
                        </div>

                        <div class="console-line console-line-spaced">
                            <span class="console-prefix">$</span>
                            <span>POST /api/contact</span>
                        </div>

                        <div class="console-code">
                            <span>validation</span>
                            <strong>enabled</strong>

                            <span>AI analysis</span>
                            <strong>enabled</strong>

                            <span>email delivery</span>
                            <strong>resilient</strong>

                            <span>request tracing</span>
                            <strong>enabled</strong>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section
            id="capabilities"
            class="section section-light"
        >
            <div class="container">
                <div class="section-heading">
                    <span class="section-label">Возможности</span>

                    <h2>Полный цикл обработки обращения</h2>

                    <p>
                        Frontend использует настоящий backend API,
                        а не демонстрационные статические данные.
                    </p>
                </div>

                <div class="capability-grid">
                    <article class="capability-card">
                        <span class="capability-number">01</span>

                        <h3>Безопасный REST API</h3>

                        <p>
                            Валидация, нормализация данных,
                            rate limiting, CORS и единый контракт ошибок.
                        </p>
                    </article>

                    <article class="capability-card">
                        <span class="capability-number">02</span>

                        <h3>AI-анализ обращения</h3>

                        <p>
                            Определение тональности, типа запроса
                            и генерация профессионального автоответа.
                        </p>
                    </article>

                    <article class="capability-card">
                        <span class="capability-number">03</span>

                        <h3>Устойчивая доставка писем</h3>

                        <p>
                            Отдельная отправка владельцу и пользователю.
                            Сбой одного письма не блокирует второе.
                        </p>
                    </article>

                    <article class="capability-card">
                        <span class="capability-number">04</span>

                        <h3>Наблюдаемость</h3>

                        <p>
                            Health-check, агрегированные metrics,
                            Request ID и безопасные файловые логи.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section
            id="observability"
            class="section observability-section"
        >
            <div class="container">
                <div class="section-heading section-heading-light">
                    <span class="section-label">Live API</span>

                    <h2>Состояние backend в реальном времени</h2>

                    <p>
                        Значения ниже загружаются напрямую
                        из <code>/api/health</code>
                        и <code>/api/metrics</code>.
                    </p>
                </div>

                <div class="status-grid">
                    <article class="status-card status-card-wide">
                        <div class="status-card-header">
                            <div>
                                <span class="status-caption">
                                    Состояние сервиса
                                </span>

                                <h3 id="health-title">
                                    Проверка подключения
                                </h3>
                            </div>

                            <span
                                id="health-badge"
                                class="status-badge status-badge-loading"
                            >
                                Проверяется
                            </span>
                        </div>

                        <div class="health-list">
                            <div>
                                <span>Laravel application</span>
                                <strong id="application-health">
                                    —
                                </strong>
                            </div>

                            <div>
                                <span>Database connection</span>
                                <strong id="database-health">
                                    —
                                </strong>
                            </div>

                            <div>
                                <span>API version</span>
                                <strong id="api-version">
                                    —
                                </strong>
                            </div>
                        </div>
                    </article>

                    <article class="status-card">
                        <span class="status-caption">
                            Всего обращений
                        </span>

                        <strong
                            id="metric-total"
                            class="metric-value"
                        >
                            —
                        </strong>

                        <small>сохранено в системе</small>
                    </article>

                    <article class="status-card">
                        <span class="status-caption">
                            AI обработка
                        </span>

                        <strong
                            id="metric-ai"
                            class="metric-value"
                        >
                            —
                        </strong>

                        <small>успешно или через fallback</small>
                    </article>

                    <article class="status-card">
                        <span class="status-caption">
                            Отправлено писем
                        </span>

                        <strong
                            id="metric-mail"
                            class="metric-value"
                        >
                            —
                        </strong>

                        <small>владельцу и пользователям</small>
                    </article>
                </div>
            </div>
        </section>

        <section
            id="contact"
            class="section contact-section"
        >
            <div class="container contact-grid">
                <div class="contact-copy">
                    <span class="section-label">Связаться</span>

                    <h2>Расскажите о задаче</h2>

                    <p>
                        Форма отправляет данные в настоящий
                        <code>POST /api/contact</code>.
                        После обработки вы увидите результат AI-анализа,
                        автоответ и Request ID.
                    </p>

                    <div class="contact-flow">
                        <div>
                            <span>1</span>
                            <p>
                                Валидация и безопасное сохранение
                                обращения
                            </p>
                        </div>

                        <div>
                            <span>2</span>
                            <p>
                                AI-анализ или автоматический
                                безопасный fallback
                            </p>
                        </div>

                        <div>
                            <span>3</span>
                            <p>
                                Отправка двух писем и возврат
                                результата
                            </p>
                        </div>
                    </div>
                </div>

                <div class="contact-panel">
                    <form id="contact-form" novalidate>
                        <div class="form-row">
                            <div class="form-field">
                                <label for="name">Имя</label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    autocomplete="name"
                                    placeholder="Иван Петров"
                                    maxlength="120"
                                    required
                                >

                                <small
                                    class="field-error"
                                    data-error-for="name"
                                ></small>
                            </div>

                            <div class="form-field">
                                <label for="phone">Телефон</label>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    autocomplete="tel"
                                    placeholder="+7 700 123 45 67"
                                    maxlength="32"
                                    required
                                >

                                <small
                                    class="field-error"
                                    data-error-for="phone"
                                ></small>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="email">Email</label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                placeholder="you@example.com"
                                maxlength="255"
                                required
                            >

                            <small
                                class="field-error"
                                data-error-for="email"
                            ></small>
                        </div>

                        <div class="form-field">
                            <label for="comment">
                                Сообщение
                            </label>

                            <textarea
                                id="comment"
                                name="comment"
                                rows="6"
                                minlength="10"
                                maxlength="4000"
                                placeholder="Опишите проект, вакансию или задачу..."
                                required
                            ></textarea>

                            <div class="field-footer">
                                <small
                                    class="field-error"
                                    data-error-for="comment"
                                ></small>

                                <small id="comment-counter">
                                    0 / 4000
                                </small>
                            </div>
                        </div>

                        <div
                            id="form-alert"
                            class="form-alert"
                            role="alert"
                            aria-live="polite"
                            hidden
                        ></div>

                        <button
                            id="submit-button"
                            class="button button-primary button-submit"
                            type="submit"
                        >
                            <span class="button-label">
                                Отправить обращение
                            </span>

                            <span
                                class="button-loader"
                                aria-hidden="true"
                            ></span>
                        </button>

                        <p class="form-note">
                            Нажатие кнопки может выполнить один
                            реальный AI-запрос и отправить два письма.
                        </p>
                    </form>

                    <section
                        id="submission-result"
                        class="submission-result"
                        aria-live="polite"
                        hidden
                    >
                        <div class="result-heading">
                            <div>
                                <span class="result-label">
                                    Обращение обработано
                                </span>

                                <h3>Спасибо за сообщение</h3>
                            </div>

                            <span
                                id="result-status"
                                class="status-badge status-badge-success"
                            >
                                completed
                            </span>
                        </div>

                        <blockquote id="result-auto-response"></blockquote>

                        <div class="result-grid">
                            <div>
                                <span>Тональность</span>
                                <strong id="result-sentiment">—</strong>
                            </div>

                            <div>
                                <span>Тип запроса</span>
                                <strong id="result-type">—</strong>
                            </div>

                            <div>
                                <span>AI status</span>
                                <strong id="result-ai-status">—</strong>
                            </div>

                            <div>
                                <span>Request ID</span>
                                <strong id="result-request-id">—</strong>
                            </div>
                        </div>

                        <button
                            id="new-submission-button"
                            class="button button-secondary button-full"
                            type="button"
                        >
                            Отправить ещё одно обращение
                        </button>
                    </section>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <div>
                <strong>Developer Portfolio API</strong>
                <p>
                    PHP · Laravel · Docker · REST API · AI
                </p>
            </div>

            <div class="footer-links">
                <a href="/api" target="_blank" rel="noopener noreferrer">
                    API Root
                </a>

                <a
                    href="/api/health"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Health
                </a>

                <a
                    href="/api/metrics"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Metrics
                </a>
            </div>
        </div>
    </footer>
</body>
</html>
