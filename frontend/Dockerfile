FROM php:8.3-apache
RUN a2enmod rewrite
RUN docker-php-ext-install curl 2>/dev/null || true
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' \
    >> /etc/apache2/apache2.conf
COPY . /var/www/html
EXPOSE 80
