FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        git \
        netcat-openbsd \
        unzip \
        curl \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock /app/
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . /app

EXPOSE 8000

