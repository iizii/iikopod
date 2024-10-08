FROM redis:latest

# Set timezone
ARG TIME_ZONE=Europe/Moscow
ENV TIME_ZONE=${TIME_ZONE}
RUN ln -snf /usr/share/zoneinfo/${TIME_ZONE} /etc/localtime && echo ${TIME_ZONE} > /etc/timezone

# Update and upgrade the system
RUN apt-get update \
    && apt-get upgrade -y --no-install-recommends \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \