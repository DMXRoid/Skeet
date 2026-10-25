# Development image for Skeet. SQLite works out of the box (bundled with the
# official PHP image); pdo_mysql / pdo_pgsql are included so the optional MySQL
# and Postgres Compose profiles work too.
FROM php:8.3-cli

RUN apt-get update \
	&& apt-get install -y --no-install-recommends git unzip libpq-dev \
	&& docker-php-ext-install pdo_mysql pdo_pgsql \
	&& rm -rf /var/lib/apt/lists/*

# Composer (for dev tooling / PHPUnit).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# pdo_sqlite + sqlite3 are compiled into the official image.
