FROM php:8.1-apache

# Copiar el código al directorio web
COPY . /var/www/html

# Instalar extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Configurar permisos si es necesario
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80