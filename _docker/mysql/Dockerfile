ARG MYSQL_VERSION=8.4

FROM mysql:${MYSQL_VERSION}

# Set timezone
ARG TIME_ZONE=Europe/Moscow
ENV TIME_ZONE=${TIME_ZONE}
RUN ln -snf /usr/share/zoneinfo/${TIME_ZONE} /etc/localtime && echo ${TIME_ZONE} > /etc/timezone

CMD ["mysqld"]

EXPOSE 3306