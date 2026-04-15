FROM php:8.2-fpm
RUN apt-get update && apt-get install -y git curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev zip unzip && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets && pecl install redis && docker-php-ext-enable redis && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/backend
RUN echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/uploads.ini && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/uploads.ini && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/uploads.ini
EXPOSE 9000
CMD ["php-fpm"]
