# syntax=docker/dockerfile:1

FROM alpine:3.23

ARG PHP_VERSION
ARG XDEBUG=false

SHELL ["/bin/sh", "-eux", "-o", "pipefail", "-c"]

RUN --mount=type=cache,target=/var/cache/apk,sharing=locked <<-EOF
    PHP_VER=$(echo "$PHP_VERSION" | tr -d '.')
    apk add --update-cache \
        php${PHP_VER} \
        php${PHP_VER}-phar \
        php${PHP_VER}-mbstring \
        php${PHP_VER}-openssl \
        php${PHP_VER}-curl \
        php${PHP_VER}-intl \
        php${PHP_VER}-xml \
        php${PHP_VER}-xmlwriter \
        php${PHP_VER}-dom \
        php${PHP_VER}-simplexml \
        php${PHP_VER}-tokenizer \
        php${PHP_VER}-ctype \
        php${PHP_VER}-iconv \
        php${PHP_VER}-session \
        php${PHP_VER}-pecl-pcov \
        git \
        unzip \
        openssh
EOF

RUN --mount=type=cache,target=/var/cache/apk,sharing=locked <<-EOF
    PHP_VER=$(echo "$PHP_VERSION" | tr -d '.')
    if [ "$XDEBUG" = "true" ]; then
        apk add "php${PHP_VER}-pecl-xdebug"
        printf '[xdebug]\nzend_extension=xdebug\nxdebug.mode=debug\nxdebug.start_with_request=trigger\nxdebug.client_host=host.docker.internal\nxdebug.client_port=9003\n' \
            > "/etc/php${PHP_VER}/conf.d/50_xdebug.ini"
    fi
EOF

RUN <<-EOF
    PHP_VER=$(echo "$PHP_VERSION" | tr -d '.')
    ln -sf "/usr/bin/php${PHP_VER}" /usr/bin/php
    git config --global --add safe.directory /app
EOF

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
