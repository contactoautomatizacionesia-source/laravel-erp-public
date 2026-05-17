# =============================================================================
# Multi-stage Dockerfile — amazingsite-erp
# Stages: composer → production
# BD externa (no incluida). Chrome + Node para Browsershot.
# Assets pre-compilados en public/ (Modules/SEOOverAll no presente en repo).
# =============================================================================

# ── Stage 1: Composer dependencies ──────────────────────────────────────────
FROM composer:2 AS composer-deps

WORKDIR /app
COPY composer.json composer.lock ./
# Solo instalar deps (sin autoloader). dump-autoload se ejecuta en el stage final
# DESPUÉS de copiar el código fuente. Así el output de este stage solo cambia
# cuando composer.json/lock cambian → caché de vendor estable entre commits.
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# ── Stage 2: Production image ──────────────────────────────────────────────
FROM php:8.3-fpm-bookworm AS production
LABEL org.opencontainers.image.source=https://github.com/DaruinHerreraIgniweb/amazingsite-erp

ENV DEBIAN_FRONTEND=noninteractive \
    APP_ENV=production \
    APP_DEBUG=false

# 1. ionCube Loader (must load before any other extension)
RUN curl -o /tmp/ioncube.tar.gz https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz \
    && tar xzf /tmp/ioncube.tar.gz -C /tmp \
    && cp /tmp/ioncube/ioncube_loader_lin_8.3.so $(php -r 'echo ini_get("extension_dir");')/ \
    && echo "zend_extension=ioncube_loader_lin_8.3.so" > /usr/local/etc/php/conf.d/00-ioncube.ini \
    && rm -rf /tmp/ioncube /tmp/ioncube.tar.gz

# 2. System packages + PHP extensions (single layer para reducir tamaño)
RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
    gnupg wget unzip curl \
    # PHP extension build deps
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    # Chrome runtime deps
    libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 \
    libxcomposite1 libxdamage1 libxrandr2 libgbm1 libasound2 \
    libpangocairo-1.0-0 libxshmfence1 libx11-xcb1 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Google Chrome (repositorio oficial firmado)
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub \
        | gpg --dearmor -o /usr/share/keyrings/googlechrome-linux-keyring.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/googlechrome-linux-keyring.gpg] http://dl.google.com/linux/chrome/deb/ stable main" \
        >> /etc/apt/sources.list.d/google.list \
    && apt-get update && apt-get install -y --no-install-recommends google-chrome-stable \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 4. Node.js 20 LTS (requerido por puppeteer/browsershot)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 5. OPcache configuration for production (JIT deshabilitado — incompatible con ionCube)
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=20000\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1" > /usr/local/etc/php/conf.d/opcache-prod.ini

# 6. Custom PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

# 7. Working directory + user setup
WORKDIR /var/www

RUN mkdir -p /home/www-data/.local/share/applications \
    /home/www-data/.cache \
    /home/www-data/.config \
    storage/framework/{cache/data,sessions,views} \
    storage/logs storage/puppeteer_profile bootstrap/cache \
    && chown -R www-data:www-data /home/www-data storage bootstrap/cache

ENV HOME=/home/www-data

# 8. Install npm dependencies BEFORE copying code (cached unless package.json changes)
COPY package.json package-lock.json ./
RUN npm ci --omit=dev

# 9. Copy vendor from composer stage (only changes when composer.lock changes → cached)
COPY --from=composer-deps --chown=www-data:www-data /app/vendor vendor/

# 10. Composer binary (needed for dump-autoload in step 13)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# 11. Entrypoint (rarely changes — cached)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 12. Copy application code (changes every commit — keep LAST)
COPY --chown=www-data:www-data . .

# 13. Generate optimized autoloader (needs vendor + source code, fast ~10s)
RUN composer dump-autoload --optimize --no-dev --no-scripts

USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD php -r 'exit(0);' || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]