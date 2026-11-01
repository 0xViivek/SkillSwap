FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
# Copy only public application files and synthetic fixtures. Runtime records,
# Git metadata, tests and local development tools never enter the image.
COPY *.php .htaccess ./
COPY includes/ ./includes/
COPY admin/ ./admin/
COPY css/ ./css/
COPY js/ ./js/
COPY data/seed/ ./data/seed/
COPY data/.htaccess ./data/.htaccess
COPY deploy/apache.conf /etc/apache2/conf-available/skillswap.conf
COPY deploy/php.ini /usr/local/etc/php/conf.d/skillswap.ini
COPY deploy/start.sh /usr/local/bin/skillswap-start
RUN a2enconf skillswap \
    && sed -i 's/\r$//' /usr/local/bin/skillswap-start \
    && chmod 755 /usr/local/bin/skillswap-start

ENV SKILLSWAP_DATA_DIR=/var/lib/skillswap/records
EXPOSE 8080
CMD ["/usr/local/bin/skillswap-start"]
