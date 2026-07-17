FROM node:20-alpine AS node

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    libmemcached-dev \
    zlib-dev \
    nginx \
    oniguruma-dev \
    sqlite-dev \
    supervisor \
    zip \
    unzip \
    autoconf \
    g++ \
    make

RUN docker-php-ext-install \
    pdo_sqlite \
    gd \
    mbstring \
    xml \
    zip \
    opcache

RUN wget -O /tmp/memcached.tgz https://pecl.php.net/get/memcached-3.3.0.tgz \
    && pecl install /tmp/memcached.tgz \
    && docker-php-ext-enable memcached \
    && rm -f /tmp/memcached.tgz

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .
COPY --from=node /app/public/build ./public/build

RUN composer run-script post-autoload-dump

RUN mkdir -p storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN mkdir -p /var/log/supervisor \
    && mkdir -p /etc/nginx/ssl \
    && addgroup -g 1000 -S appgroup \
    && adduser -u 1000 -S appuser -G appgroup

RUN chown -R 1000:1000 /var/www/html \
    && chown -R 1000:1000 /var/log/supervisor \
    && chown -R 1000:1000 /etc/nginx/ssl \
    && chown -R 1000:1000 /var/lib/nginx \
    && chown -R 1000:1000 /var/log/nginx \
    && chown -R 1000:1000 /run/nginx

EXPOSE 443

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
