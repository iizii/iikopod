ARG PHP_VERSION=8.3

FROM dunglas/frankenphp:latest-php${PHP_VERSION}

# Set timezone
ARG TIME_ZONE=Europe/Moscow
ENV TIME_ZONE=${TIME_ZONE}
RUN ln -snf /usr/share/zoneinfo/${TIME_ZONE} /etc/localtime && echo ${TIME_ZONE} > /etc/timezone

# Update and upgrade the system
RUN apt-get update \
    && apt-get upgrade -y --no-install-recommends \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install php extensions
RUN install-php-extensions \
    @composer \
    xhprof \
    sockets \
    excimer \
    opcache \
    pdo_mysql \
    pcntl \
    sodium \
    intl \
    zip \
    redis
