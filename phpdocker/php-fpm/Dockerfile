FROM phpdockerio/php74-fpm:latest

WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install php7.4-cli php7.4-dev \
                                                         php7.4-pgsql php7.4-sqlite3 php7.4-gd \
                                                         php7.4-curl php7.4-memcached \
                                                         php7.4-imap php7.4-mysql php7.4-mbstring \
                                                         php7.4-xml php7.4-zip php7.4-bcmath php7.4-soap \
                                                         php7.4-intl php7.4-readline php7.4-pcov \
                                                         php7.4-msgpack php7.4-igbinary php7.4-ldap \
                                                         php7.4-redis \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
