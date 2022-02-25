FROM php:8.0
WORKDIR /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer