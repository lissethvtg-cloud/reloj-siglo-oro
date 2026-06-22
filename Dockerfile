FROM php:8.2-apache

# 1. Instalar dependencias del sistema y extensiones de PHP (MySQL + PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql

# 2. Copiar todos los archivos del proyecto al servidor
COPY . /var/www/html/

# 3. Descomprimir tus librerías en automático si existen
RUN if [ -f /var/www/html/modules.zip ]; then unzip -o /var/www/html/modules.zip -d /var/www/html/; fi

# 4. Asegurar permisos correctos
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
