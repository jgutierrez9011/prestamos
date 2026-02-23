FROM php:8.1-apache-bookworm

# 1) Dependencias + extensiones PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# 2) Apache: dejar SOLO un MPM (recomendado: prefork) + rewrite
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite

# 3) Código de la app
COPY . /var/www/html

# 4) Ajustes apache + permisos (opcional)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && chown -R www-data:www-data /var/www/html

# 5) Entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
