#! /bin/bash

cp .env.example .env

touch database/database.sqlite
## rights (for Linux/Mac)
chmod 666 database/database.sqlite

# Sail
./vendor/bin/sail up -d

# composer dependencies
./vendor/bin/sail composer install

# generate key and migrate tables
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
