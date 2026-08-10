FROM serversideup/php:8.3-frankenphp

USER root

# Copy Bun for building frontend assets or JS runtime support
COPY --from=oven/bun:1.1 /usr/local/bin/bun /usr/local/bin/bun

# Install extensions (pcntl is REQUIRED for Laravel Octane worker handling)
RUN install-php-extensions pdo_mysql bcmath intl zip opcache gd redis exif pcntl

USER www-data
WORKDIR /var/www/html

# Copy application files into the container
COPY --chown=www-data:www-data . .

# Production Composer flags (--no-dev and --optimize-autoloader)
RUN composer install --optimize-autoloader --ignore-platform-req=ext-pdo_duckdb

# Document the port Octane will listen on
EXPOSE 8080

# Default command to start Octane in FrankenPHP worker mode
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8080"]
