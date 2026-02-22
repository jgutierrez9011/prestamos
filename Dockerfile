FROM php:8.1-apache-bookworm

COPY . /var/www/html

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite

# FIX MPM: dejar SOLO prefork (mod_php)
RUN set -eux; \
    a2dismod mpm_event || true; \
    a2dismod mpm_worker || true; \
    a2dismod mpm_prefork || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf; \
    rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf; \
    rm -f /etc/apache2/mods-enabled/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.conf; \
    a2enmod mpm_prefork; \
    apachectl -M | grep -E 'mpm_(event|worker|prefork)_module'; \
    test "$(apachectl -M | grep -cE 'mpm_(event|worker|prefork)_module')" -eq 1

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["/bin/bash", "-lc", "\
echo '--- apachectl -M (MPM) ---'; apachectl -M | grep mpm || true; \
echo '--- mods-enabled (mpm) ---'; ls -la /etc/apache2/mods-enabled/ | grep mpm || true; \
echo '--- grep LoadModule mpm_ ---'; grep -R \"LoadModule mpm_\" -n /etc/apache2 || true; \
echo '--- starting apache ---'; apache2-foreground \
"]
