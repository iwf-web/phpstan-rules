FROM alpine:3.23

ARG PHP_VERSION

RUN apk add --no-cache \
        php${PHP_VERSION} \
        php${PHP_VERSION}-phar \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-openssl \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-xmlwriter \
        php${PHP_VERSION}-dom \
        php${PHP_VERSION}-simplexml \
        php${PHP_VERSION}-tokenizer \
        php${PHP_VERSION}-ctype \
        php${PHP_VERSION}-iconv \
        php${PHP_VERSION}-session \
        php${PHP_VERSION}-pecl-xdebug \
        git \
        unzip \
        openssh

RUN ln -sf /usr/bin/php${PHP_VERSION} /usr/bin/php

RUN printf '[xdebug]\nzend_extension=xdebug\nxdebug.mode=debug\nxdebug.start_with_request=yes\nxdebug.client_host=host.docker.internal\nxdebug.client_port=9003\n' \
    > /etc/php${PHP_VERSION}/conf.d/50_xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
