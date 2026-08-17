FROM php:8.4-fpm-alpine AS php

RUN apk add -U --no-cache curl-dev
RUN docker-php-ext-install curl
RUN docker-php-ext-install exif

# APCuのインストール（ビルドツールはインストール後にきれいに削除）
RUN apk add --no-cache --virtual .build-deps autoconf g++ make \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps

RUN apk add libpng-dev
RUN docker-php-ext-install gd

# MySQL接続用ドライバー
RUN docker-php-ext-install pdo_mysql

RUN install -o www-data -g www-data -d /var/www/upload/image/

FROM php:8.4-fpm-alpine AS php

RUN docker-php-ext-install pdo_mysql

RUN install -o www-data -g www-data -d /var/www/upload/image/

RUN echo -e "post_max_size = 5M\nupload_max_filesize = 5M" >> ${PHP_INI_DIR}/php.ini

