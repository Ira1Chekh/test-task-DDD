#! /bin/bash

cp .env.example .env

touch database/database.sqlite
## .env for SQLite
if ! grep -q "DB_CONNECTION=sqlite" .env; then
    echo "DB_CONNECTION=sqlite" >> .env
    echo "DB_DATABASE=/var/www/html/database/database.sqlite" >> .env
## rights (for Linux/Mac)
chmod 666 database/database.sqlite

# Sail
./vendor/bin/sail up -d

# composer dependencies
./vendor/bin/sail composer install

# generate key and migrate tables
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
