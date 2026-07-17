# syntax=docker/dockerfile:1

FROM composer:2.10.2 AS composer

FROM php:8.4-cli-alpine

ARG APP_UID=1000
ARG APP_GID=1000

RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-libs \
        libcurl \
        libxml2 \
        libzip \
        oniguruma \
        sqlite \
        sqlite-libs \
        unzip \
    && apk add --no-cache --virtual .build-deps \
        ${PHPIZE_DEPS} \
        curl-dev \
        icu-dev \
        libxml2-dev \
        libzip-dev \
        linux-headers \
        oniguruma-dev \
        sqlite-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        intl \
        mbstring \
        pcntl \
        pdo_sqlite \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && apk del .build-deps \
    && rm -rf /tmp/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN addgroup -g "${APP_GID}" -S app \
    && adduser \
        -u "${APP_UID}" \
        -S \
        -D \
        -G app \
        -h /home/app \
        -s /bin/bash \
        app \
    && mkdir -p /workspace /home/app/.composer \
    && chown -R app:app /workspace /home/app

ENV HOME=/home/app
ENV COMPOSER_HOME=/home/app/.composer
ENV PATH="/workspace/vendor/bin:${PATH}"

WORKDIR /workspace

USER app

CMD ["sh", "-lc", "tail -f /dev/null"]
