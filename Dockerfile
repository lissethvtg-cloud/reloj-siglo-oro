FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL y ZIP
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && apt-get update && apt-get install -y unzip

# Copiar todos los archivos del proyecto al servidor
COPY . /var/www/html/

# Descomprimir tus librerías en automático si existen
RUN if [ -f /var/www/html/modules.zip ]; then unzip -o /var/www/html/modules.zip -d /var/www/html/; fi

# Asegurar permisos correctos
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80