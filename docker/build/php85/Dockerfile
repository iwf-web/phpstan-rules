FROM alpine:3.23

RUN apk add --no-cache \
        php85 \
        php85-phar \
        php85-mbstring \
        php85-openssl \
        php85-curl \
        php85-intl \
        php85-xml \
        php85-xmlwriter \
        php85-dom \
        php85-simplexml \
        php85-tokenizer \
        php85-ctype \
        php85-iconv \
        php85-session \
        git \
        unzip \
        openssh

RUN sh -c 'ln -sf $(which php85) /usr/bin/php'

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
