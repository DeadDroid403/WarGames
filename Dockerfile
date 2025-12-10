FROM php:8.2-apache

#######################################
# Install Python 3.11 + dependencies
#######################################
RUN apt-get update && apt-get install -y \
    python3 python3-pip python3-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

#######################################
# Set working directory for PHP app
#######################################
WORKDIR /var/www/html

#######################################
# Optional: Python dependencies
# (only if you need them)
#######################################
RUN mkdir /python_helpers
COPY ./python_helpers/requirements.txt /python_helpers/requirements.txt
RUN pip3 install --break-system-packages --no-cache-dir -r /python_helpers/requirements.txt
COPY ./python_helpers/ /python_helpers

#######################################
# Fix permissions
#######################################
RUN chown -R www-data:www-data /var/www/html

#######################################
# Copy PHP application code
#######################################
COPY ./php_app/ /var/www/html

#######################################
# PHP-FPM listens on port 9000
#######################################
EXPOSE 9000

RUN python3 /python_helpers/initdb.py

RUN a2enmod rewrite
