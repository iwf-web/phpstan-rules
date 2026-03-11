FROM alpine:3.23

RUN apk add --no-cache \
        php84 \
        php84-phar \
        php84-mbstring \
        php84-openssl \
        php84-curl \
        php84-intl \
        php84-xml \
        php84-xmlwriter \
        php84-dom \
        php84-simplexml \
        php84-tokenizer \
        php84-ctype \
        php84-iconv \
        php84-session \
        git \
        unzip \
        openssh

RUN sh -c 'ln -sf $(which php84) /usr/bin/php'

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
