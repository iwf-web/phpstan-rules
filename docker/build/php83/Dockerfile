FROM alpine:3.23

RUN apk add --no-cache \
        php83 \
        php83-phar \
        php83-mbstring \
        php83-openssl \
        php83-curl \
        php83-intl \
        php83-xml \
        php83-xmlwriter \
        php83-dom \
        php83-simplexml \
        php83-tokenizer \
        php83-ctype \
        php83-iconv \
        php83-session \
        git \
        unzip \
        openssh

RUN sh -c 'ln -sf $(which php83) /usr/bin/php'

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
