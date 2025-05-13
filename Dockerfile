FROM laravelsail/php83-composer

WORKDIR /var/www/html

COPY . .

# Jika .env tidak ada, copy dari .env.example
RUN if [ ! -f .env ]; then cp .env.example .env; fi

RUN composer install

# Generate app key jika belum ada
RUN php artisan key:generate --force || true

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
