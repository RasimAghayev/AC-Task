FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    curl \
    unzip \
    git \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&\
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

#COPY ./scripts/load-secrets.sh /usr/local/bin/load-secrets.sh
#RUN chmod +x /usr/local/bin/load-secrets.sh
#ENTRYPOINT ["/usr/local/bin/load-secrets.sh"]

WORKDIR /var/www/html/be
COPY ./src/be  /var/www/html/be
COPY --chown=www-data:www-data ./src/be /var/www/html/be
RUN chown -R www-data:www-data /var/www/html/be/storage /var/www/html/be/bootstrap/cache \
    && chmod -R 777 /var/www/html/be/storage /var/www/html/be/bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm"]