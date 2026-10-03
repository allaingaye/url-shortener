# ═══════════════════════════════════════════════════════════════
# Stage 1 — Composer dependencies
# ═══════════════════════════════════════════════════════════════
FROM composer:2 AS vendor

WORKDIR /tmp

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install production dependencies only (no scripts, no dev)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

# ═══════════════════════════════════════════════════════════════
# Stage 2 — Runtime (PHP-FPM + Alpine)
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
        supervisor \
        tini \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        linux-headers \
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
    # Cleanup build dependencies (shrinks final image)
    && apk del .build-deps

# ── Composer binary (for runtime artisan commands) ──
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP configuration ──
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-app.conf

# ── Working directory ──
WORKDIR /var/www

# ── Copy application source (respects .dockerignore) ──
COPY . .

# ── Copy vendor from stage 1 (avoids running composer twice) ──
COPY --from=vendor /tmp/vendor ./vendor

# ── Entrypoint script (normalized to LF to survive Windows CRLF) ──
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

# ── Ensure writable dirs exist and are owned by www-data ──
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

# ── Tini as PID 1 for clean signal handling ──
ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]