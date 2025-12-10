FROM php:8.2-fpm

#######################################
# Install Python 3.11 + dependencies
#######################################
RUN apt-get update && apt-get install -y \
    python3 python3-pip python3-venv python3-dev \
    git curl unzip zip \
    libpq-dev libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

#######################################
# Set working directory for PHP app
#######################################
WORKDIR /var/www/html

#######################################
# Copy PHP application code
#######################################
COPY ./php-app/ /var/www/html

#######################################
# Optional: Python dependencies
# (only if you need them)
#######################################
# COPY ./python/requirements.txt /tmp/requirements.txt
# RUN pip3 install --no-cache-dir -r /tmp/requirements.txt

#######################################
# Fix permissions
#######################################
RUN chown -R www-data:www-data /var/www/html

#######################################
# PHP-FPM listens on port 9000
#######################################
EXPOSE 9000

CMD ["php-fpm"]
