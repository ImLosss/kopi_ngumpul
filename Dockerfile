FROM laravelsail/php83-composer

WORKDIR /var/www/html

COPY . .

# Copy .env.example ke .env dan update koneksi database
RUN cp .env.example .env && \
    sed -i "s/^DB_HOST=.*/DB_HOST=89.116.34.103/" .env && \
    sed -i "s/^DB_PORT=.*/DB_PORT=5432/" .env && \
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=kopi_ngumpul/" .env && \
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=root/" .env && \
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=password/" .env

RUN composer install

RUN php artisan key:generate --force || true

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
