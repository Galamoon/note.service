FROM php:8.5-fpm

# Install system dependencies & build tools for PECL
RUN apt-get update && apt-get install -y --no-install-recommends \
build-essential \
libpng-dev \
libjpeg-dev \
libfreetype6-dev \
libonig-dev \
libxml2-dev \
libzip-dev \
libldap2-dev \
libssl-dev \
libicu-dev \
libgmp-dev \
zlib1g-dev \
libcurl4-openssl-dev \
libwebp-dev \
pkg-config \
git \
unzip \
curl \
vim \
&& docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
&& docker-php-ext-install \
gd \
mysqli \
pdo_mysql \
intl \
soap \
ldap \
gmp \
bcmath \
zip \
# Очищаем кэш apt, чтобы уменьшить размер образа
&& apt-get clean && rm -rf /var/lib/apt/lists/*

# Install igbinary, apcu, redis, xdebug via PECL
RUN pecl install igbinary apcu redis xdebug \
    && docker-php-ext-enable igbinary apcu redis xdebug



# Configure PHP for Bitrix
RUN { \
echo "memory_limit = 512M"; \
echo "upload_max_filesize = 256M"; \
echo "post_max_size = 256M"; \
echo "max_execution_time = 600"; \
echo "max_input_time = 600"; \
echo "max_input_vars = 5000"; \
echo "date.timezone = Europe/Moscow"; \
echo "display_errors = On"; \
echo "log_errors = On"; \
echo "error_reporting = E_ALL"; \
echo "opcache.enable = 1"; \
echo "opcache.memory_consumption = 256"; \
echo "opcache.max_accelerated_files = 20000"; \
echo "opcache.revalidate_freq = 2"; \
echo "opcache.validate_timestamps = 1"; \
echo "realpath_cache_size = 4096k"; \
echo "realpath_cache_ttl = 600"; \
echo "expose_php = Off"; \
echo "short_open_tag = On"; \
echo "zlib.output_compression = On"; \
} > /usr/local/etc/php/conf.d/bitrix.ini

# Copy Xdebug configuration
COPY conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Copy PHP-FPM pool configuration
COPY conf.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Expose FPM port
EXPOSE 9000

CMD ["php-fpm"]
