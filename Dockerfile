#FROM php:8.4-cli-alpine AS base
#due to malvik-lab/libre-translate-api-client

FROM php:8.5.2-cli-alpine AS base

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer/composer:2-bin /composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin

RUN apk update && apk add zip

RUN set -eux; \
    install-php-extensions \
    zip pcntl intl bcmath \
    && rm -rf /tmp/*

WORKDIR "/app"

FROM base AS dev

RUN apk update && apk add git vim bash

ARG APP_USER_ID=1000
ARG APP_USER_NAME=appuser
RUN adduser -D -u ${APP_USER_ID} ${APP_USER_NAME}

USER ${APP_USER_NAME}
