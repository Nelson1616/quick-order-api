#!/bin/sh

echo "Hello World"

echo ${PWD}

cd /app/src/

cp .env.example .env

php artisan key:generate

cd /var/www/html

sed -i "s,LISTEN_PORT,8989,g" /etc/nginx/nginx.conf

php-fpm -D

# while ! nc -w 1 -z 127.0.0.1 9000; do sleep 0.1; done;

nginx
