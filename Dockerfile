# ✅ Use PHP 8.3 with Apache
FROM php:8.3-apache

# ✅ Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# ✅ Enable Apache mod_rewrite for Laravel routes
RUN a2enmod rewrite

# ✅ Set working directory
WORKDIR /var/www/html

# ✅ Copy project files to container
COPY . /var/www/html

# ✅ Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ✅ Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# ✅ Give storage permission (important for Laravel)
RUN chmod -R 777 storage bootstrap/cache

# ✅ Generate app key automatically (skip error if .env missing)
RUN cp .env.example .env || true \
    && php artisan key:generate || true

# ✅ Set Apache document root to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ✅ Expose HTTP port
EXPOSE 80

# ✅ Start Apache
CMD ["apache2-foreground"]
