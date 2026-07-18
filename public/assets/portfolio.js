document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#contact-form');
    const submitButton = document.querySelector('#submit-button');
    const formAlert = document.querySelector('#form-alert');
    const result = document.querySelector('#submission-result');
    const comment = document.querySelector('#comment');
    const commentCounter = document.querySelector('#comment-counter');
    const newSubmissionButton = document.querySelector(
        '#new-submission-button',
    );

    const statusLabels = {
        completed: 'Выполнено',
        partially_completed: 'Частично выполнено',
        pending: 'Ожидает',
        processing: 'Обрабатывается',
        failed: 'Ошибка',
    };

    const sentimentLabels = {
        positive: 'Положительная',
        neutral: 'Нейтральная',
        negative: 'Негативная',
    };

    const requestTypeLabels = {
        project_inquiry: 'Проект',
        job_opportunity: 'Вакансия',
        consultation: 'Консультация',
        collaboration: 'Сотрудничество',
        feedback: 'Обратная связь',
        other: 'Другое',
    };

    const updateText = (selector, value) => {
        const element = document.querySelector(selector);

        if (element) {
            element.textContent = String(value);
        }
    };

    const fetchJson = async (url, options = {}) => {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                ...options.headers,
            },
            ...options,
        });

        const payload = await response.json().catch(() => ({
            success: false,
            message: 'Сервер вернул некорректный ответ.',
        }));

        return {
            response,
            payload,
        };
    };

    const setHealthUnavailable = () => {
        updateText('#hero-application-status', 'offline');
        updateText('#hero-database-status', 'unknown');
        updateText('#application-health', 'Недоступно');
        updateText('#database-health', 'Недоступно');
        updateText('#api-version', '—');
        updateText('#health-title', 'Backend недоступен');

        const badge = document.querySelector('#health-badge');

        badge.textContent = 'Недоступен';
        badge.className = 'status-badge status-badge-error';
    };

    const loadHealth = async () => {
        try {
            const { response, payload } = await fetchJson('/api/health');

            if (!response.ok || !payload.checks) {
                setHealthUnavailable();

                return;
            }

            const applicationOk = payload.checks.application?.ok === true;
            const databaseOk = payload.checks.database?.ok === true;
            const healthy = applicationOk && databaseOk;

            updateText(
                '#hero-application-status',
                applicationOk ? 'healthy' : 'failed',
            );

            updateText(
                '#hero-database-status',
                databaseOk ? 'healthy' : 'failed',
            );

            updateText(
                '#application-health',
                applicationOk ? 'Работает' : 'Ошибка',
            );

            updateText(
                '#database-health',
                databaseOk ? 'Подключена' : 'Недоступна',
            );

            updateText('#api-version', payload.version ?? '—');

            updateText(
                '#health-title',
                healthy
                    ? 'Все системы работают'
                    : 'Обнаружена проблема',
            );

            const badge = document.querySelector('#health-badge');

            badge.textContent = healthy ? 'Healthy' : 'Unhealthy';
            badge.className = healthy
                ? 'status-badge status-badge-success'
                : 'status-badge status-badge-error';
        } catch {
            setHealthUnavailable();
        }
    };

    const loadMetrics = async () => {
        try {
            const { response, payload } = await fetchJson('/api/metrics');

            if (!response.ok || !payload.data) {
                throw new Error('Metrics unavailable.');
            }

            const metrics = payload.data;
            const aiSucceeded = metrics.ai?.succeeded ?? 0;
            const aiFallback = metrics.ai?.fallback ?? 0;
            const ownerMailSent = metrics.mail?.owner?.sent ?? 0;
            const userMailSent = metrics.mail?.user?.sent ?? 0;

            updateText(
                '#metric-total',
                metrics.total_submissions ?? 0,
            );

            updateText(
                '#metric-ai',
                aiSucceeded + aiFallback,
            );

            updateText(
                '#metric-mail',
                ownerMailSent + userMailSent,
            );
        } catch {
            updateText('#metric-total', '—');
            updateText('#metric-ai', '—');
            updateText('#metric-mail', '—');
        }
    };

    const clearErrors = () => {
        document
            .querySelectorAll('[data-error-for]')
            .forEach((element) => {
                element.textContent = '';
            });

        form
            .querySelectorAll('.is-invalid')
            .forEach((element) => {
                element.classList.remove('is-invalid');
            });

        formAlert.hidden = true;
        formAlert.textContent = '';
    };

    const showValidationErrors = (errors = {}) => {
        Object.entries(errors).forEach(([field, messages]) => {
            const error = document.querySelector(
                `[data-error-for="${field}"]`,
            );

            const input = form.elements.namedItem(field);

            if (error && Array.isArray(messages)) {
                error.textContent = messages[0] ?? '';
            }

            if (input instanceof HTMLElement) {
                input.classList.add('is-invalid');
            }
        });
    };

    const showFormError = (message) => {
        formAlert.textContent = message;
        formAlert.hidden = false;
    };

    const setLoading = (loading) => {
        submitButton.disabled = loading;
        submitButton.classList.toggle('is-loading', loading);

        const label = submitButton.querySelector('.button-label');

        label.textContent = loading
            ? 'Обработка обращения...'
            : 'Отправить обращение';
    };

    const showResult = (data) => {
        updateText(
            '#result-status',
            statusLabels[data.status] ?? data.status ?? 'Готово',
        );

        updateText(
            '#result-auto-response',
            data.auto_response ?? 'Обращение успешно получено.',
        );

        updateText(
            '#result-sentiment',
            sentimentLabels[data.analysis?.sentiment]
                ?? data.analysis?.sentiment
                ?? '—',
        );

        updateText(
            '#result-type',
            requestTypeLabels[data.analysis?.request_type]
                ?? data.analysis?.request_type
                ?? '—',
        );

        updateText('#result-ai-status', data.ai_status ?? '—');
        updateText('#result-request-id', data.request_id ?? '—');

        form.hidden = true;
        result.hidden = false;
        result.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    };

    comment.addEventListener('input', () => {
        commentCounter.textContent = `${comment.value.length} / 4000`;
    });

    newSubmissionButton.addEventListener('click', () => {
        result.hidden = true;
        form.hidden = false;
        form.reset();
        commentCounter.textContent = '0 / 4000';
        clearErrors();

        form.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearErrors();
        setLoading(true);

        const payload = {
            name: form.elements.name.value,
            phone: form.elements.phone.value,
            email: form.elements.email.value,
            comment: form.elements.comment.value,
        };

        try {
            const { response, payload: responsePayload } = await fetchJson(
                '/api/contact',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                },
            );

            if (response.status === 422) {
                showValidationErrors(responsePayload.errors);
                showFormError(
                    'Проверьте заполненные поля и повторите отправку.',
                );

                return;
            }

            if (!response.ok || responsePayload.success !== true) {
                showFormError(
                    responsePayload.message
                        ?? 'Не удалось отправить обращение.',
                );

                return;
            }

            showResult(responsePayload.data);
            await loadMetrics();
        } catch {
            showFormError(
                'Не удалось подключиться к API. Повторите попытку.',
            );
        } finally {
            setLoading(false);
        }
    });

    void Promise.all([
        loadHealth(),
        loadMetrics(),
    ]);
});
