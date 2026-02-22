#FROM php:8.1-apache
FROM php:8.1-apache-buster
# Copiar el código al directorio web
COPY . /var/www/html

# Instalar extensiones necesarias para PostgreSQL y habilitar mod_rewrite
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite

# Configurar ServerName para evitar advertencias
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar permisos si es necesario
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80
