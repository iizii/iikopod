ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-cli

# Set timezone
ARG TIME_ZONE=Europe/Moscow
ENV TIME_ZONE=${TIME_ZONE}
RUN ln -snf /usr/share/zoneinfo/${TIME_ZONE} /etc/localtime && echo ${TIME_ZONE} > /etc/timezone

# Update and upgrade the system
RUN apt-get update \
    && apt-get upgrade -y --no-install-recommends \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \

# Install php extensions
RUN docker-php-ext-install \
    pcntl \
    sodium \
    intl \
    zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer