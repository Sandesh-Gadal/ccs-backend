# --- Admin panel assets (React + Vite) -------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# --- PHP dependencies --------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev

# --- Runtime -------------------------------------------------------------------
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql opcache \
    && a2enmod rewrite headers \
    && echo "ServerName localhost" > /etc/apache2/conf-enabled/servername.conf

# Serve Laravel's public/ folder and allow its .htaccess rewrites
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html
COPY --chown=www-data:www-data --from=vendor /app ./
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80
ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
