FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

# Run composer install during Docker build
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install node/npm and build frontend assets
RUN apk add --no-cache nodejs npm && \
    npm install && \
    npm run build

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["sh", "-c", "php artisan migrate --force && /start.sh"]
