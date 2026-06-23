FROM richarvey/nginx-php-fpm:3.1.6

# Install PostgreSQL dev libraries and PHP extension
RUN apk add --no-cache postgresql-dev && \
    docker-php-ext-install pdo_pgsql

COPY . .

# Run composer install during Docker build
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install node/npm and build frontend assets
RUN apk add --no-cache nodejs npm && \
    npm install && \
    npm run build

# Set permissions for Laravel storage and cache
RUN chmod -R 777 storage bootstrap/cache

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV PHP_CATCHALL 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && /start.sh"]
