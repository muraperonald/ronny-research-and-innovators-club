# R-RIC: container image for Render / Railway / any Docker host.
# Static pages + PHP handler served by Apache, with the PDO MySQL driver.
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite expires \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY . /var/www/html/

# Apache must be able to write the JSON fallback files.
RUN chown -R www-data:www-data /var/www/html/data

# Render provides $PORT; default to 80 elsewhere.
ENV PORT=80
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
