FROM php:8.1-fpm

COPY php.ini /usr/local/etc/php/

# ホストのユーザーIDとグループIDを引数として受け取る
ARG USER_ID=1000
ARG GROUP_ID=1000

# ユーザーとグループを作成
RUN groupadd -g ${GROUP_ID} appgroup && \
  useradd -u ${USER_ID} -g appgroup -m appuser

RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip \
  && docker-php-ext-install pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

WORKDIR /var/www

RUN chown -R appuser:appgroup /var/www

USER appuser