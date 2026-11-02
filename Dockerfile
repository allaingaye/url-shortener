# ═══════════════════════════════════════════════════════════════
# Single-stage build — PHP-FPM + Alpine with all extensions
# ═══════════════════════════════════════════════════════════════
FROM php:8.4-fpm-alpine AS runtime

# ── System packages + build deps (removed after compile) ──
RUN apk add --no-cache \
        bash \
        git \
        curl \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        sqlite-dev \
        postgresql-dev \
        imagemagick \
        supervisor \
        tini \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        linux-headers \
        imagemagick-dev \
    \
    # Configure + install PHP extensions
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_sqlite \
        pdo_pgsql \
        pdo_mysql \
        gd \
        mbstring \
        zip \
        intl \
        opcache \
        bcmath \
        pcntl \
    \
    # Redis extension via PECL
    && pecl install redis \
    && docker-php-ext-enable redis \
    \
    # Imagick extension for QR PNG rendering
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    \
    # Cleanup build dependencies
    && apk del .build-deps

# ── Composer binary ──
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP configuration ──
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-app.conf

# ── Working directory ──
WORKDIR /var/www

# ── Copy composer files first (better layer caching) ──
COPY composer.json composer.lock ./

# ── Install dependencies — gd is now available ──
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

# ── Copy application source ──
COPY . .

# ── Entrypoint script (normalized to LF) ──
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

# ── Ensure writable dirs ──
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/api-docs \
        bootstrap/cache \
        database \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

# ── Tini as PID 1 ──
ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]